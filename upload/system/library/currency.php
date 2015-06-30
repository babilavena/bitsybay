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

final class Currency {

    /**
     * @var resource
     */
    private $_db              = false;

    /**
     * @var int
     */
    private $_currency_id     = false;

    /**
     * @var string
     */
    private $_currency_code   = false;

    /**
     * @var string
     */
    private $_currency_symbol = false;

    /**
     * @var int|float
     */
    private $_currency_rate   = false;

    /**
     * @var string
     */
    private $_currency_name   = false;


    /**
     * @var array
     */
    private $_currencies      = array();

    /**
    * Construct
    *
    * @param registry $registry
    * @param int $currency_id Current currency_id id
    */
    public function __construct($registry, $currency_id) {

        $this->_db = $registry->get('db');

        try {
            $statement = $this->_db->prepare('SELECT * FROM `currency`');
            $statement->execute();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());
        }

        if ($statement->rowCount()) {

            foreach ($statement->fetchAll() as $currency) {

                $this->_currencies[$currency->currency_id] = array(
                    'currency_id' => $currency->currency_id,
                    'code'        => $currency->code,
                    'rate'        => $currency->rate,
                    'symbol'      => $currency->symbol,
                    'name'        => $currency->name
                );

                if ($currency->currency_id == $currency_id) {
                    $this->_currency_id     = $currency->currency_id;
                    $this->_currency_code   = $currency->code;
                    $this->_currency_rate   = $currency->rate;
                    $this->_currency_name   = $currency->name;
                    $this->_currency_symbol = $currency->symbol;
                }
            }
        }
    }

    /**
    * Check currency id exists
    *
    * @param int $currency_id
    * @return bool TRUE if exists or FALSE if else
    */
    public function hasId($currency_id) {

        return isset($this->_currencies[$currency_id]);

    }

    /**
    * Get currency id
    *
    * @return int currency_id
    */
    public function getId() {

        return $this->_currency_id;

    }

    /**
    * Get currency code
    *
    * @return int currency_id
    */
    public function getCode() {

        return $this->_currency_code;

    }

    /**
    * Format currency to catalog style
    *
    * @param int|float $amount
    * @param int $currency_id_from
    * @param int $currency_id_to
    * @return string formatted string
    */
    public function format($amount, $currency_id_from = 1, $currency_id_to = 1) {

        return rtrim(rtrim($amount, '0'), '.') . ' ' . $this->_currency_symbol;
    }
}
