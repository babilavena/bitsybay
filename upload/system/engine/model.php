<?php

/**
 * LICENSE
 *
 * This source file is subject to the GNU General Public License, Version 3
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @package    BitsyBay Engine
 * @copyright  Copyright (c) 2015 The BitsyBay Project (http://bitsybay.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License, Version 3
 */

abstract class Model {

    /**
     * @var resource
     */
    protected $registry;

    /**
    * Construct registry
    *
    * @param resource $registry
    */
    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
    * Magic get item from registry
    *
    * @param mixed $key
    * @return mixed
    */
    public function __get($key) {
        return $this->registry->get($key);
    }

    /**
    * Magic add item to registry
    *
    * @param mixed $key
    * @param mixed $value
    */
    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }
}
