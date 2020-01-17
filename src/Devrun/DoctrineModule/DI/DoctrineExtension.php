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
use Devrun\DoctrineModule\Listeners\TimeStableListener;
use Kdyby\Events\DI\EventsExtension;
use Nette;

class DoctrineExtension extends Nette\DI\CompilerExtension
{

    public $defaults = array(
        'autoFlush' => false,
    );


    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('doctrine', new DoctrineExtension());
        };
    }

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);

        $builder->addDefinition($this->prefix('entityFormMapper'))
                ->setFactory(EntityFormMapper::class);


        /*
         * Listeners
         */
        // user
        $builder->addDefinition($this->prefix('listener.blabeable'))
                ->setType(BlameableListener::class)
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // time
        $builder->addDefinition($this->prefix('listener.timeStable'))
                ->setType(TimeStableListener::class)
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // uow flush
        $builder->addDefinition($this->prefix('listener.flush'))
                ->setFactory(FlushListener::class, [$config['autoFlush']])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // tree
        $builder->addDefinition($this->prefix('listener.treeListener'))
                ->setClass('Gedmo\Tree\TreeListener')
                ->addSetup('setAnnotationReader', ['@Doctrine\Common\Annotations\Reader'])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // translatable
        $builder->addDefinition($this->prefix('listener.translatableListener'))
                ->setClass('Gedmo\Translatable\TranslatableListener')
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