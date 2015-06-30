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

class FilterUri {

    /**
    * Filter uri alias
    *
    * @param $string
    * @return string
    */
    static public function alias($string) {

        return preg_replace(array('/\s/', '/[^a-z0-9-]/', '/-{2,}/'), array('-', '', '-'), mb_strtolower($string));
    }
}
