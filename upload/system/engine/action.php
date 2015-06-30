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

final class Action {

    private $path;
    private $file;
    private $class;
    private $method;
    private $args = array();

    /**
    * Construct
    *
    * @param string $route
    * @param array $args
    */
    public function __construct($route, $args = array()) {
        $path = '';

        // Break apart the route
        $parts = explode(DIR_SEPARATOR, str_replace('../', '', (string)$route));

        foreach ($parts as $part) {
            $path .= $part;

            if (is_dir(DIR_BASE . 'application' . DIR_SEPARATOR . 'controller' . DIR_SEPARATOR . $path)) {
                $path .= DIR_SEPARATOR;

                array_shift($parts);

                continue;
            }

            $file = DIR_BASE . 'application' . DIR_SEPARATOR . 'controller' . DIR_SEPARATOR . str_replace(array('../', '..\\', '..'), '', $path) . '.php';

            if (is_file($file)) {

                $this->path = $path;
                $this->file = $file;
                $this->class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $path);

                array_shift($parts);

                break;
            }
        }

        if ($args) {
            $this->args = $args;
        }

        $method = array_shift($parts);

        if ($method) {
            $this->method = $method;
        } else {
            $this->method = 'index';
        }
    }

    /**
    * Execute registry object
    *
    * @param resource $registry
    * @return mixed
    */
    public function execute($registry) {

        // Stop any magical methods being called
        if (substr($this->method, 0, 2) == '__') {
            return false;
        }

        if (is_file($this->file)) {
            include_once($this->file);

            $class = $this->class;

            $controller = new $class($registry);

            if (is_callable(array($controller, $this->method))) {
                return call_user_func(array($controller, $this->method), $this->args);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
