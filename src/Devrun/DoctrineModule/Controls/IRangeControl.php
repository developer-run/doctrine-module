<?php
/**
 *
 * This file is part of doctrine-module
 *
 * Copyright (c) 2016
 *
 * @file IRangeControl.php
 * @author  Pavel Paulík <pavel.paulik1@gmail.com>
 */

namespace Devrun\DoctrineModule\Controls;

use Nette\ComponentModel\IComponent;

interface IRangeControl extends IComponent {

    const FIELD_RANGE_NAME = 'field.range.name';

    /**
     * Sets control's range value.
     * @param  mixed
     * @return void
     */
    function setToValue($value);

    /**
     * Returns control's range value.
     * @return mixed
     */
    function getToValue();

    /**
     * Returns control's from value.
     * @return mixed
     */
    function getFromValue();

    /**
     * Returns control's range name.
     * @return string
     */
    function getToName();


    /**
     * Returns control's range name.
     * @param $rangeName
     * @return $this
     */
    function setToName($rangeName);


} 