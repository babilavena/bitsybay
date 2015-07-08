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

class ValidatorProduct {

    /**
    * Validate title
    *
    * @param string $title
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function titleValid($title) {

        if (mb_strlen($title) < VALIDATOR_PRODUCT_TITLE_MIN_LENGTH || mb_strlen($title) > VALIDATOR_PRODUCT_TITLE_MAX_LENGTH) {
            return false;
        } else if (!preg_match('/^[\w\s\d\(\)\-\+\.\%]+$/ui', $title)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Validate description
    *
    * @param string $description
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function descriptionValid($description) {

        if (empty($description)) {
            return false;
        } else if (mb_strlen($description) < VALIDATOR_PRODUCT_DESCRIPTION_MIN_LENGTH || mb_strlen($description) > VALIDATOR_PRODUCT_DESCRIPTION_MAX_LENGTH) {
            return false;
        } else if (!preg_match('/^[\w\s\d\(\)\.\,\`\"\'\@\®\©\#\№\&\%\:\;\*\/\-\_\~\+\=\?\!\<\>\{\}\[\]\’\‘\“\”]+$/ui', $description)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Validate url
    *
    * @param string $url
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function urlValid($url) {

        if (empty($url)) {
            return false;
        } else if (mb_strlen($url) < VALIDATOR_PRODUCT_URL_MIN_LENGTH) {
            return false;
        } else if (mb_strlen($url) > VALIDATOR_PRODUCT_URL_MAX_LENGTH) {
            return false;
        } else if (!preg_match('/^(http|https)\:\/\/[a-z\d\.\/\&\?\=\-\_\+]+$/ui', $url)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Validate product tags
    *
    * @param string $tags string comma separated
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function tagsValid($tags) {

        if (!empty($tags)) {

            $tags_length = mb_strlen(preg_replace('/\,\s/', '', $tags));

            if ($tags_length < VALIDATOR_PRODUCT_TAGS_MIN_LENGTH || $tags_length > VALIDATOR_PRODUCT_TAGS_MAX_LENGTH) {
                return false;
            } else if (!preg_match('/^[\,\.\-\s\d\w]+$/ui', $tags)) {
                return false;
            }
        }

        return true;
    }

    /**
    * Validate single tag
    *
    * @param string $tag
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function tagValid($tag) {

        if (!empty($tag)) {

            $tag_length = mb_strlen(preg_replace('/\,\s/', '', $tag));

            if ($tag_length < VALIDATOR_PRODUCT_TAG_MIN_LENGTH || $tag_length > VALIDATOR_PRODUCT_TAG_MAX_LENGTH) {
                return false;
            } else if (!preg_match('/^[\,\.\-\s\d\w]+$/ui', $tag)) {
                return false;
            }
        }

        return true;
    }

    /**
    * Validate single tag
    *
    * @param string $date
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function dateValid($date) {

        if (preg_match('/^[\d]{4}\-[\d]{2}\-[\d]{2}+$/ui', $date)) {
            return true;
        } else {
            return false;
        }
    }
}
