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

class ValidatorUser {

    /**
     * @var int
     */
    static private $_username_min_length = 2;

    /**
     * @var int
     */
    static private $_username_max_length = 35;

    /**
     * @var int
     */
    static private $_email_min_length = 5;

    /**
     * @var int
     */
    static private $_email_max_length = 255;

    /**
     * @var int
     */
    static private $_password_min_length = 4;

    /**
     * @var int
     */
    static private $_password_max_length = 50;


    /**
    * Validate username
    *
    * @param $string
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function usernameValid($string) {

        if (empty($string)) {
            return false;
        } else if (mb_strlen($string) < self::$_username_min_length || mb_strlen($string) > self::$_username_max_length) {
            return false;
        } else if (!preg_match('/^[a-z\-\d]+$/i', $string)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Validate e-mail
    *
    * @param $string
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function emailValid($string) {

        if (empty($string)) {
            return false;
        } else if (mb_strlen($string) < self::$_email_min_length || mb_strlen($string) > self::$_email_max_length) {
            return false;
        } else if (!filter_var($string, FILTER_VALIDATE_EMAIL)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Validate password
    *
    * @param $string
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function passwordValid($string) {

        if (empty($string)) {
            return false;
        } else if (mb_strlen($string) < self::$_password_min_length || mb_strlen($string) > self::$_password_max_length) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Get username minimum length value
    *
    * @return int
    */
    static public function getUsernameMinLength() {

        return self::$_username_min_length;
    }

    /**
    * Get username maximum length value
    *
    * @return int
    */
    static public function getUsernameMaxLength() {

        return self::$_username_max_length;
    }

    /**
    * Get email minimum length value
    *
    * @return int
    */
    static public function getEmailMinLength() {

        return self::$_email_min_length;
    }

    /**
    * Get email maximum length value
    *
    * @return int
    */
    static public function getEmailMaxLength() {

        return self::$_email_max_length;
    }

    /**
    * Get password minimum length value
    *
    * @return int
    */
    static public function getPasswordMinLength() {

        return self::$_password_min_length;
    }

    /**
    * Get password maximum length value
    *
    * @return int
    */
    static public function getPasswordMaxLength() {

        return self::$_password_max_length;
    }
}
