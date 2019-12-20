<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    UuidV4EntityTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\Entities;

trait UuidV4EntityTrait
{

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Devrun\DoctrineModule\Id\UuidV4Generator")
     */
    protected $id;


    /**
     * @return string
     */
    final function getId()
    {
        return $this->id;
    }

}