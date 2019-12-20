<?php
/**
 * This file is part of devrun
 * Copyright (c) 2018
 *
 * @file    VersionTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\Entities;


trait VersionTrait
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version;


    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }


}