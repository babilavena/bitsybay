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

final class Registry {

    /**
     * @var array
     */
    private $data = array();

    /**
    * Get item from registry
    *
    * @param mixed $key
    * @return mixed
    */
    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    /**
    * Add item to registry
    *
    * @param mixed $key
    * @param mixed $value
    */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
    * Check item contains in registry
    *
    * @param mixed $key
    * @return bool TRUE or FALSE
    */
    public function has($key) {
        return isset($this->data[$key]);
    }
}
