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

class ValidatorYoutube {

    /**
    * Validate id
    *
    * @param $string
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function idValid($string) {

        if (empty($string)) {
            return false;
        } else if (mb_strlen($string) < 2 || mb_strlen($string) > 100) {
            return false;
        } else if (!preg_match('/^[a-z\d\-]+$/i', $string)) {
            return false;
        } else {
            return true;
        }
    }
}
