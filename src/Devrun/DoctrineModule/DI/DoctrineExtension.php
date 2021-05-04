<?php
/**
 * This file is part of doctrine-module
 * Copyright (c) 2018
 *
 * @file    DoctrineExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\DI;

use Devrun\DoctrineModule\DoctrineForms\EntityFormMapper;
use Devrun\DoctrineModule\Http\UserStorage;
use Devrun\DoctrineModule\Listeners\BlameableListener;
use Devrun\DoctrineModule\Listeners\FlushListener;
use Devrun\DoctrineModule\Listeners\TimestampableListener;
use Doctrine\Common\Annotations\Reader;
use Gedmo\IpTraceable\IpTraceableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tree\TreeListener;
use Kdyby\Events\DI\EventsExtension;
use Nette;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class DoctrineExtension extends Nette\DI\CompilerExtension
{

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'autoFlush' => Expect::bool(false),

            'loggable' => Expect::bool(false),
            'sluggable' => Expect::bool(false),
            'softDeleteable' => Expect::bool(false),
            'treeable' => Expect::bool(false),
            'blameable' => Expect::bool(false),
            'timestampable' => Expect::bool(false),
            'translatable' => Expect::anyOf(false, Expect::structure([
                'translatable' => Expect::string()->required(),
                'default' => Expect::string()->required(),
                'translationFallback' => Expect::bool(false),
                'persistDefaultTranslation' => Expect::bool(false),
                'skipOnLoad' => Expect::bool(false),
            ]))->default(false),
            'uploadable' => Expect::bool(false),
            'sortable' => Expect::bool(false),
            'ipTraceable' => Expect::anyOf(false, Expect::structure([
                'ipValue' => Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))->required(),
            ]))->default(false),
        ]);
    }


    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('doctrine', new DoctrineExtension());
        };
    }

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        /** @var \Stdclass $config */
        $config = $this->getConfig();

        $builder->addDefinition($this->prefix('entityFormMapper'))
                ->setFactory(EntityFormMapper::class);


        /*
         * Listeners
         */
        // log
        if ($config->loggable) {
            $builder->addDefinition($this->prefix('loggable'))
                    ->setFactory(LoggableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class]);
        }

        // slug
        if ($config->sluggable) {
            $builder->addDefinition($this->prefix('sluggable'))
                    ->setFactory(SluggableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class]);
        }

        // soft delete
        if ($config->softDeleteable) {
            $builder->addDefinition($this->prefix('softDeleteable'))
                    ->setFactory(SoftDeleteableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class]);
        }

        // tree
        if ($config->treeable) {
            $builder->addDefinition($this->prefix('treeable'))
                    ->setFactory(TreeListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class])
                    ->addTag(EventsExtension::TAG_SUBSCRIBER);
        }

        // user
        if ($config->blameable) {
            $builder->addDefinition($this->prefix('blameable'))
                    // ->setType(\Gedmo\Blameable\BlameableListener::class)
                    ->setType(BlameableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class])
                    ->addTag(EventsExtension::TAG_SUBSCRIBER);
        }

        // time
        if ($config->timestampable) {
            $builder->addDefinition($this->prefix('timestampable'))
                    // ->setType(\Gedmo\Timestampable\TimestampableListener::class)
                    ->setType(TimestampableListener::class)
                    ->addTag(EventsExtension::TAG_SUBSCRIBER);
        }

        // translate
        if ($config->translatable !== false) {
            $translatableConfig = $config->translatable;
            $builder->addDefinition($this->prefix('translatable'))
                    ->setFactory(TranslatableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class])
                    ->addSetup('setDefaultLocale', [$translatableConfig->default])
                    ->addSetup('setTranslatableLocale', [$translatableConfig->translatable])
                    ->addSetup('setPersistDefaultLocaleTranslation', [$translatableConfig->translationFallback])
                    ->addSetup('setTranslationFallback', [$translatableConfig->persistDefaultTranslation])
                    ->addSetup('setSkipOnLoad', [$translatableConfig->skipOnLoad]);
        }

        // sort
        if ($config->sortable) {
            $builder->addDefinition($this->prefix('sortable'))
                    ->setFactory(SortableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class]);
        }

        // ip trace
        if ($config->ipTraceable !== false) {
            $builder->addDefinition($this->prefix('ipTraceable'))
                    ->setFactory(IpTraceableListener::class)
                    ->addSetup('setAnnotationReader', ['@' . Reader::class])
                    ->addSetup('setIpValue', [$config->ipTraceable->ipValue]);
        }

        // uow flush
        $builder->addDefinition($this->prefix('listener.flush'))
                ->setFactory(FlushListener::class, [$config->autoFlush])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

    }


    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $userStorageDefinitionName = $builder->getByType('Nette\Security\IUserStorage') ?: 'nette.userStorage';

        $builder->getDefinition($userStorageDefinitionName)
                ->setFactory(UserStorage::class);
    }


}