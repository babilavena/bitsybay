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

class ModelCommonCurrency extends Model {

    /**
    * Get currencies
    *
    * @return array|bool Currencies rows or false if throw exception
    */
    public function getCurrencies() {

        try {
            $statement = $this->db->prepare('SELECT * FROM `currency` ORDER BY `rate` DESC');
            $statement->execute();

            if ($statement->rowCount()) {
                return $statement->fetchAll();
            } else {
                return array();
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get currency
    *
    * @param int $currency_id
    * @return array|bool Currency row or false if throw exception
    */
    public function getCurrency($currency_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `currency` WHERE `currency_id` = ? LIMIT 1');
            $statement->execute(array($currency_id));

            if ($statement->rowCount()) {
                return $statement->fetch();
            } else {
                return array();
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
