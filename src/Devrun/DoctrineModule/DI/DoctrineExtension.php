<?php
/**
 * This file is part of devrun
 * Copyright (c) 2018
 *
 * @file    DoctrineExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\DI;

use Devrun\DoctrineModule\DoctrineForms\EntityFormMapper;
use Devrun\DoctrineModule\Listeners\BlameableListener;
use Devrun\DoctrineModule\Listeners\TimeStableListener;
use Kdyby\Events\DI\EventsExtension;
use Nette;

class DoctrineExtension extends Nette\DI\CompilerExtension
{

    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('doctrine', new DoctrineExtension());
        };
    }

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('entityFormMapper'))
                ->setFactory(EntityFormMapper::class);


        /*
         * Listeners
         */
        // user
        $builder->addDefinition($this->prefix('listener.blabeable'))
                ->setClass(BlameableListener::class)
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // time
        $builder->addDefinition($this->prefix('listener.timeStable'))
                ->setClass(TimeStableListener::class)
                ->addTag(EventsExtension::TAG_SUBSCRIBER);

    }


}