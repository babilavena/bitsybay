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

class ModelAccountAffiliate extends Model {

    /**
    * Get joined users for specific referrer
    *
    * @param int $referrer_user_id
    * @return int|bool joined count or false if throw exception
    */
    public function getTotalJoined($referrer_user_id) {

        try {
            $statement = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `user` WHERE `referrer_user_id` = ?');
            $statement->execute(array($referrer_user_id));

            if ($statement->rowCount()) {
                $result = $statement->fetch();
                return $result->total;
            } else {
                return 0;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total verification requests for specific referrer
    *
    * @param int $referrer_user_id
    * @return int|bool request count or false if throw exception
    */
    public function getTotalVerificationRequests($referrer_user_id) {

        try {

            $statement = $this->db->prepare('SELECT (SELECT COUNT(*) FROM `user_verification_request` AS `uvr` WHERE `uvr`.`user_id` = `u`.`user_id` AND `uvr`.`status` <> ?) AS `total`
            FROM `user` AS `u`
            WHERE `u`.`referrer_user_id` = ?
            ORDER BY `u`.`date_added` DESC');

            $statement->execute(array('pending', $referrer_user_id));

            if ($statement->rowCount()) {
                $result = $statement->fetch();
                return $result->total;
            } else {
                return 0;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total verification requests for specific referrer
    *
    * @param int $referrer_user_id
    * @param int $order_status_id
    * @return int|bool request count or false if throw exception
    */
    public function getTotalProductsPurchased($referrer_user_id, $order_status_id) {

        try {

            $statement = $this->db->prepare('SELECT (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`user_id` = `u`.`user_id` AND `o`.`order_status_id` <> ?) AS `total`
            FROM `user` AS `u`
            WHERE `u`.`referrer_user_id` = ?
            ORDER BY `u`.`date_added` DESC');

            $statement->execute(array($order_status_id, $referrer_user_id));

            if ($statement->rowCount()) {
                $result = $statement->fetch();
                return $result->total;
            } else {
                return 0;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get referral users for specific referrer
    *
    * @param int $referrer_user_id
    * @param int $order_status_id
    * @return array|bool users or false if throw exception
    */
    public function getReferrals($referrer_user_id, $order_status_id) {

        try {
            $statement = $this->db->prepare('SELECT `u`.`user_id`,
                                                    `u`.`username`,
                                                    `u`.`verified`,
                                                    `u`.`date_added`,
                                                    (SELECT COUNT(*) FROM `user_verification_request` AS `uvr` WHERE `uvr`.`user_id` = `u`.`user_id` AND `uvr`.`status` <> ?) AS `requests`,
                                                    (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`user_id` = `u`.`user_id` AND `o`.`order_status_id` <> ?) AS `purchased`
            FROM `user` AS `u`
            WHERE `u`.`referrer_user_id` = ?
            ORDER BY `u`.`date_added` DESC');

            $statement->execute(array('pending', $order_status_id, $referrer_user_id));

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
    * Update affiliate address
    *
    * @param int $user_id
    * @param int $affiliate_currency_id
    * @param string $affiliate_address
    * @return int|bool count affected rows or false if throw exception
    */
    public function updateAffiliateInfo($user_id, $affiliate_currency_id, $affiliate_address) {

        try {

            $statement = $this->db->prepare('UPDATE `user` SET `affiliate_address` = ?, `affiliate_currency_id` = ? WHERE `user_id` = ? LIMIT 1');
            $statement->execute(array($affiliate_address, $affiliate_currency_id, $user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
