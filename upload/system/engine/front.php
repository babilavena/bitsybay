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

final class Front {

    /**
     * @var resource
     */
    private $registry;

    /**
     * @var array
     */
    private $pre_action = array();

    /**
     * @var string
     */
    private $error;


    /**
    * Construct by registry
    *
    * @param registry $registry
    */
    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
    * Add preAction
    *
    * @param string $pre_action
    */
    public function addPreAction($pre_action) {
        $this->pre_action[] = $pre_action;
    }

    /**
    * Dispatch
    *
    * @param string $action
    * @param string $error
    */
    public function dispatch($action, $error) {
        $this->error = $error;

        foreach ($this->pre_action as $pre_action) {
            $result = $this->execute($pre_action);

            if ($result) {
                $action = $result;

                break;
            }
        }

        while ($action) {
            $action = $this->execute($action);
        }
    }

    /**
    * Execute
    *
    * @param mixed $action
    * @return mixed
    */
    private function execute($action) {
        $result = $action->execute($this->registry);

        if (is_object($result)) {
            $action = $result;
        } elseif ($result === false) {
            $action = $this->error;

            $this->error = '';
        } else {
            $action = false;
        }

        return $action;
    }
}
