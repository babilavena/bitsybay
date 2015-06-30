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

final class Loader {

    /**
     * @var resource
     */
    private $registry;

    /**
    * Construct by registry
    *
    * @param resource $registry
    */
    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
    * Init controller
    *
    * @param string $route
    * @param array $args
    * @return mixed|bool
    */
    public function controller($route, $args = array()) {
        $action = new Action($route, $args);

        return $action->execute($this->registry);
    }

    /**
    * Load model
    *
    * @param string $model
    */
    public function model($model) {
        $file = DIR_BASE . 'application' . DIR_SEPARATOR . 'model' . DIR_SEPARATOR . $model . '.php';
        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

        if (file_exists($file)) {

            include_once($file);

            $this->registry->set('model_' . str_replace(DIR_SEPARATOR, '_', $model), new $class($this->registry));
        } else {
            trigger_error('Error: Could not load model ' . $file . '!');
            exit();
        }
    }

    /**
    * Load view
    *
    * @param string $template
    * @param array $data
    * @return string ob_get_contents()
    */
    public function view($template, $data = array()) {
        $file = DIR_BASE . 'application' . DIR_SEPARATOR . 'view' . DIR_SEPARATOR . $template;

        if (file_exists($file)) {
            extract($data);

            ob_start();

            require($file);

            $output = ob_get_contents();

            ob_end_clean();

            return $output;
        } else {
            trigger_error('Error: Could not load template ' . $file . '!');
            exit();
        }
    }

    /**
    * Load library
    *
    * @param string $library
    */
    public function library($library) {
        $file = DIR_BASE . 'system' . DIR_SEPARATOR . 'library/' . $library . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }

    /**
    * Load helper
    *
    * @param string $helper
    */
    public function helper($helper) {
        $file = DIR_BASE . 'system' . DIR_SEPARATOR . 'helper/' . $helper . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load helper ' . $file . '!');
            exit();
        }
    }
}
