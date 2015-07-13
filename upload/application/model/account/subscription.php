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

class ModelAccountSubscription extends Model {

    /**
    * Get subscriptions
    *
    * @param int $language_id
    *
    * @return array|bool
    */
    public function getSubscriptions($language_id) {

        try {

            $statement = $this->db->prepare('SELECT * FROM `subscription` AS `s` LEFT JOIN `subscription_description` AS `sd` ON (`sd`.`subscription_id` = `s`.`subscription_id`) WHERE `sd`.`language_id` = ?');
            $statement->execute(array($language_id));

            if ($statement->rowCount()) {
                return $statement->fetchAll();
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check subscription status
    *
    * @param int $subscription_id
    *
    * @return bool
    */
    public function checkSubscription($subscription_id) {

        try {

            $statement = $this->db->prepare('SELECT NULL FROM `subscription` WHERE `subscription_id` = ? LIMIT 1');
            $statement->execute(array($subscription_id));

            return (bool) $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get user subscriptions
    *
    * @param int $user_id
    *
    * @return int|bool
    */
    public function getUserSubscriptions($user_id) {

        try {

            $statement = $this->db->prepare('SELECT * FROM `user_subscription` WHERE `user_id` = ?');
            $statement->execute(array($user_id));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check user subscription status
    *
    * @param int $user_id
    * @param int $subscription_id
    *
    * @return bool
    */
    public function checkUserSubscription($user_id, $subscription_id) {

        try {

            $statement = $this->db->prepare('SELECT NULL FROM `user_subscription` WHERE `user_id` = ? AND `subscription_id` = ? LIMIT 1');
            $statement->execute(array($user_id, $subscription_id));

            return (bool) $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Add user to subscription
    *
    * @param int $user_id
    * @param int $subscription_id
    *
    * @return int|bool
    */
    public function addUserSubscription($user_id, $subscription_id) {

        try {

            $statement = $this->db->prepare('INSERT INTO `user_subscription` SET `user_id` = ?, `subscription_id` = ? ');
            $statement->execute(array($user_id, $subscription_id));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Delete all user subscriptions
    *
    * @param int $user_id
    *
    * @return int|bool
    */
    public function deleteUserSubscriptions($user_id) {

        try {

            $statement = $this->db->prepare('DELETE FROM `user_subscription` WHERE `user_id` = ?');
            $statement->execute(array($user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
