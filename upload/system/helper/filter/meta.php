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

class FilterMeta {

    /**
    * Filter uri alias
    *
    * @param $string
    * @return string
    */
    static public function description($string) {

        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = (strlen($string) > 100) ? substr($string, 0, strpos($string, ' ', 100)) : $string;
        $string = trim(preg_replace('/\s+/', ' ', $string), '.,:;-/+"');

        return $string;
    }
}
