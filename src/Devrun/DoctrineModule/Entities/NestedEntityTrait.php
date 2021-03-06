<?php
/**
 * This file is part of doctrine-module
 * Copyright (c) 2017
 *
 * @file    NestedEntityTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\Entities;

trait NestedEntityTrait
{

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

}