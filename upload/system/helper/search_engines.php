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

class SearchEngines {

    static private $search_engines = array('');

    /**
    * Bot detect
    *
    * @param $user_agent
    * @return bool TRUE if bot or FALSE if else
    */
    static public function isBot($user_agent) {

        if (preg_match('/bot|crawl|slurp|spider/i', $user_agent)) {
            return true;
        } else {
            return false;
        }
    }
}
