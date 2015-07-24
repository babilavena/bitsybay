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
    * Filter meta description
    *
    * @param string $string
    * @param int $limit
    * @return string
    */
    static public function description($string, $limit = 10000) {

        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = (strlen($string) > $limit) ? substr($string, 0, strpos($string, ' ', $limit)) : $string;
        $string = trim(preg_replace('/\s+/', ' ', $string), '.,:;-/+"');

        return $string;
    }
}
