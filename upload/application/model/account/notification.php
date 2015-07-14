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

class ModelAccountNotification extends Model {

    /**
    * Add new notification to specific user
    *
    * @param  int $user_id
    * @param  int $language_id
    * @param  string $label ENUM
    * @param  string $title
    * @param  string $description
    * @return int|bool Email approved status or false if throw exception
    */
    public function addNotification($user_id, $language_id, $label, $title, $description) {

        try {

            $statement = $this->db->prepare('INSERT INTO `user_notification` SET `user_id`     = :user_id,
                                                                                 `language_id` = :language_id,
                                                                                 `label`       = :label,
                                                                                 `title`       = :title,
                                                                                 `description` = :description,
                                                                                 `read`        = 0,
                                                                                 `date_added`  = NOW()');
            $statement->execute(
                array(
                    ':user_id'     => $user_id,
                    ':language_id' => $language_id,
                    ':label'       => $label,
                    ':title'       => $title,
                    ':description' => $description
                )
            );

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Set read status for notification
    *
    * @param int $notification_id
    * @param int $user_id
    * @param int $read 0|1
    * @return int|bool rowCount or false if throw exception
    */
    public function setReadStatus($notification_id, $user_id, $read) {

        try {

            $statement = $this->db->prepare('UPDATE `user_notification` SET `read` = ?, `date_read` = NOW() WHERE `user_notification_id` = ? AND `user_id` = ? LIMIT 1');
            $statement->execute(array($read, $notification_id, $user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get user notification
    *
    * @param int $notification_id
    * @param int $user_id
    * @param int $language_id
    * @return array|bool notification row or false if throw exception
    */
    public function getNotification($notification_id, $user_id, $language_id) {

        try {

            $statement = $this->db->prepare('SELECT * FROM `user_notification` WHERE `user_notification_id` = ? AND `user_id` = ? AND `language_id` = ? LIMIT 1');
            $statement->execute(array($notification_id, $user_id, $language_id));

            if ($statement->rowCount()) {
                return $statement->fetch();
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get user notifications
    *
    * @param int $user_id
    * @param int $language_id
    * @param array $filter_data
    * @return array|bool notification row set or false if throw exception
    */
    public function getNotifications($user_id, $language_id, array $filter_data = array()) {

        try {

            $query = 'SELECT * FROM `user_notification` WHERE `user_id` = :user_id AND `language_id` = :language_id';
            $place_holders = array(':user_id'     => $user_id,
                                   ':language_id' => $language_id);

            // Filter by read status
            if (isset($filter_data['read'])) {
                $query .= ' AND `read` = :read';
                $place_holders[':read'] = $filter_data['read'];
            }

            $query .= ' ORDER BY user_notification_id DESC';

            $statement = $this->db->prepare($query);
            $statement->execute($place_holders);

            return $statement->rowCount() ? $statement->fetchAll() : array();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total user notifications
    *
    * @param int $user_id
    * @param int $language_id
    * @param array $filter_data
    * @return int|bool notification total count or false if throw exception
    */
    public function getTotalNotifications($user_id, $language_id, array $filter_data = array()) {

        try {

            $query = 'SELECT COUNT(*) AS `total` FROM `user_notification` WHERE `user_id` = :user_id AND `language_id` = :language_id';
            $place_holders = array(':user_id'     => $user_id,
                                   ':language_id' => $language_id);

            // Filter by read status
            if (isset($filter_data['read'])) {
                $query .= ' AND `read` = :read';
                $place_holders[':read'] = $filter_data['read'];
            }

            $statement = $this->db->prepare($query);
            $statement->execute($place_holders);

            $result = $statement->fetch();

            return $result->total;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check new user notifications
    *
    * @param int $user_id
    * @return int|bool new notification status or false if throw exception
    */
    public function checkNewNotifications($user_id) {

        try {

            $statement = $this->db->prepare('SELECT NULL FROM `user_notification` WHERE `user_id` = ? AND `read` = 0 LIMIT 1');
            $statement->execute(array($user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
