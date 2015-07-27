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

final class Language {

    /**
     * @var resource
     */
    private $_db               = false;

    /**
     * @var int
     */
    private $_language_id      = false;

    /**
     * @var string
     */
    private $_language_code    = false;

    /**
     * @var string
     */
    private $_language_locale  = false;

    /**
     * @var string
     */
    private $_language_name    = false;

    /**
     * @var array
     */
    private $_languages        = array();


    /**
    * Construct
    *
    * @param registry $registry
    * @param int $language_id Current language id
    */
    public function __construct(Registry $registry, $language_id) {

        $this->_db = $registry->get('db');

        try {
            $statement = $this->_db->prepare('SELECT * FROM `language`');
            $statement->execute();

        } catch (PDOException $e) {

            if ($this->_db->inTransaction()) {
                $this->_db->rollBack();
            }

            trigger_error($e->getMessage());
        }

        if ($statement->rowCount()) {

            foreach ($statement->fetchAll() as $language) {

                $this->_languages[$language->language_id] = array(
                    'language_id'      => $language->language_id,
                    'language_code'    => $language->code,
                    'language_locale'  => $language->locale,
                    'language_name'    => $language->name
                );

                if ($language->language_id == $language_id) {
                    $this->_language_id      = $language->language_id;
                    $this->_language_code    = $language->code;
                    $this->_language_locale  = $language->locale;
                    $this->_language_name    = $language->name;
                }
            }
        }
    }


    /**
    * Check language id exists
    *
    * @param int $language_id
    * @return bool TRUE if exists or FALSE if else
    */
    public function hasId($language_id) {
        return isset($this->_languages[$language_id]);
    }

    /**
    * Get language id
    *
    * @return int
    */
    public function getId() {
        return $this->_language_id;
    }

    /**
    * Get language code
    *
    * @return string
    */
    public function getCode() {
        return $this->_language_code;
    }

    /**
    * Get language locale
    *
    * @return string
    */
    public function getLocale() {
        return $this->_language_locale;
    }

    /**
    * Get language name
    *
    * @return string
    */
    public function getName() {
        return $this->_language_name;
    }
}
