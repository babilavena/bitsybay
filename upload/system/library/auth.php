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

final class Auth {

    private $_user_id    = 0;
    private $_file_quota = 0;
    private $_approved   = false;
    private $_username   = false;
    private $_email      = false;
    private $_status     = false;
    private $_verified   = false;
    private $_date_added = false;

    public function __construct(Registry $registry) {

        $this->db      = $registry->get('db');
        $this->request = $registry->get('request');
        $this->session = $registry->get('session');

        // If user has id
        if ($this->session->getUserId()) {

            // Find Customer in Database
            try {
                $statement = $this->db->prepare('
                SELECT

                `u`.`user_id`,
                `u`.`file_quota`,
                `u`.`status`,
                `u`.`verified`,
                `u`.`username`,
                `u`.`date_added`,
                `u`.`email`,
                (SELECT `ue`.`approved` FROM `user_email` AS `ue` WHERE `ue`.`email` = `u`.`email` LIMIT 1) AS `approved`

                FROM `user` AS `u`
                WHERE `u`.`user_id` = ?
                AND `u`.`status` = 1
                LIMIT 1');

                $statement->execute(array($this->session->getUserId()));

            } catch (PDOException $e) {

                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                trigger_error($e->getMessage());

            }

            if ($statement->rowCount()) {

                $user = $statement->fetch();

                $this->_user_id     = $user->user_id;
                $this->_file_quota  = $user->file_quota;
                $this->_approved    = $user->approved;
                $this->_username    = $user->username;
                $this->_email       = $user->email;
                $this->_status      = $user->status;
                $this->_verified    = $user->verified;
                $this->_date_added  = $user->date_added;

                // Set last visit date
                $this->_setVisitDate($user->user_id);

                // Update IP Log
                $this->_saveIP($user->user_id, $this->request->getRemoteAddress());

            } else {
                $this->logout();
            }
        }

    }

    /**
    * Register last visit
    *
    * @param int $user_id
    * @return int|bool Return affected int row, or bool true if row already exists or false if throw exception
    */
    private function _setVisitDate($user_id) {

        try {

            $statement = $this->db->prepare('UPDATE `user` SET `date_visit` = NOW() WHERE `user_id` = ? LIMIT 1');
            $statement->execute(array($user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Save user IP to database
    *
    * @param int $user_id
    * @param string $ip
    * @return int|bool Return user_ip_id if add new row, true if row already exists or false if throw exception
    */
    private function _saveIP($user_id, $ip) {

        try {

            // If IP not registered
            $statement = $this->db->prepare('SELECT * FROM `user_ip` WHERE `user_id` = ? AND `ip` = ? LIMIT 1');
            $statement->execute(array($user_id, $ip));

            if (!$statement->rowCount()) {

                // If IP changed
                $statement = $this->db->prepare('SELECT NULL FROM `user_ip` WHERE `user_id` = ? LIMIT 1');
                $statement->execute(array($user_id));

                if ($statement->rowCount()) {

                    // Add notification
                    $statement = $this->db->prepare('INSERT INTO `user_notification` SET  `user_id`     = :user_id,
                                                                                          `language_id` = :language_id,
                                                                                          `type`        = :type,
                                                                                          `title`       = :title,
                                                                                          `description` = :description,
                                                                                          `sent`        = 0,
                                                                                          `read`        = 0,
                                                                                          `date_added`  = NOW()');
                    $statement->execute(
                        array(
                            ':user_id'     => $user_id,
                            ':language_id' => DEFAULT_LANGUAGE_ID,
                            ':type'        => 'ni', // New IP
                            ':title'       => tt('Login with new IP'),
                            ':description' => sprintf(tt("Login with new IP (%s) has been registered.\n"), $ip) . tt('If you believe your account has been compromised, please contact us.')
                        )
                    );
                }

                // Save new IP
                $statement = $this->db->prepare('INSERT INTO `user_ip` SET `user_id` = ?, `ip` = ?, `date_added` = NOW()');
                $statement->execute(array($user_id, $ip));

                return $this->db->lastInsertId();
            }

            return true;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Get last IP
    *
    * @return string|bool
    */
    public function getLastIP() {

        try {
            $statement = $this->db->prepare('SELECT `ip` FROM `user_ip` WHERE `user_id` = ? ORDER BY `user_ip_id` DESC LIMIT 1');
            $statement->execute(array($this->_user_id));

            $result = $statement->fetch();

            return $result->ip;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Login user
    *
    * @param string $login Username or Email
    * @param string $password Raw password string
    * @param bool $login_is_email FALSE if login is username or TRUE if login is email (by default)
    * @return bool Returns TRUE if success or FALSE if something wrong
    */
    public function login($login, $password, $login_is_email = true) {

        try {
            // Login by email
            if ($login_is_email) {
                $statement = $this->db->prepare('SELECT
                `u`.*,
                (SELECT `ue`.`approved` FROM `user_email` AS `ue` WHERE `ue`.`email` = `u`.`email` LIMIT 1) AS `approved`
                FROM `user` AS `u`
                WHERE
                `u`.`email` = :email AND
                `u`.`password` = SHA1(CONCAT(`u`.`salt`, SHA1(CONCAT(`salt`, SHA1(:password))))) AND
                `u`.`status` = 1
                LIMIT 1');

                $statement->execute(array(
                    ':password' => $password,
                    ':email'    => $login));

            // Login by username
            } else {
                $statement = $this->db->prepare('SELECT
                `u`.*,
                (SELECT `ue`.`approved` FROM `user_email` AS `ue` WHERE `ue`.`email` = `u`.`email` LIMIT 1) AS `approved`
                FROM `user` AS `u`
                WHERE
                `u`.`username` = :username AND
                `u`.`password` = SHA1(CONCAT(salt, SHA1(CONCAT(`salt`, SHA1(:password))))) AND
                `u`.`status` = 1
                LIMIT 1');

                $statement->execute(array(
                    ':password' => $password,
                    ':username' => $login));
            }
        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }


        // Update Items
        if ($statement->rowCount()) {

            $user = $statement->fetch();

            $this->session->setUserId($user->user_id);

            // Update Global Variables
            $this->_user_id     = $user->user_id;
            $this->_approved    = $user->approved;
            $this->_file_quota  = $user->file_quota;
            $this->_username    = $user->username;
            $this->_email       = $user->email;
            $this->_status      = $user->status;
            $this->_verified    = $user->verified;
            $this->_date_added  = $user->date_added;

            // Update IP Log
            $this->_saveIP($user->user_id, $this->request->getRemoteAddress());

            return true;

        } else {

            return false;
        }
    }

    /**
    * Logout user
    *
    * @return bool Returns TRUE if success or FALSE if throw exception
    */
    public function logout() {

        // Remove Session
        $this->session->setUserId();

        // Update Variables
        $this->_user_id    = 0;
        $this->_file_quota = 0;
        $this->_approved   = false;
        $this->_username   = false;
        $this->_email      = false;
        $this->_status     = false;
        $this->_verified   = false;
        $this->_date_added = false;

        return true;

    }

    /**
    * Check if user is already logged
    *
    * @return bool TRUE if logged or FALSE if guest
    */
    public function isLogged() {
        return (bool) $this->_user_id;
    }

    /**
    * Check if user is approved
    *
    * @return bool TRUE if approved or FALSE if unapproved
    */
    public function isApproved() {
        return (bool) $this->_approved;
    }

    /**
    * Check if user is active
    *
    * @return bool TRUE if active or FALSE if unapproved
    */
    public function isActive() {
        return (bool) $this->_status;
    }

    /**
    * Check if user is verified
    *
    * @return bool TRUE if verified or FALSE if unapproved
    */
    public function isVerified() {
        return (bool) $this->_verified;
    }

    /**
    * Get user id
    *
    * @return int
    */
    public function getId() {
        return (int) $this->_user_id;
    }

    /**
    * Get file quota
    *
    * @return int file_quota
    */
    public function getFileQuota() {
        return (int) $this->_file_quota;
    }

    /**
    * Get username
    *
    * @return string
    */
    public function getUsername() {
        return (string) $this->_username;
    }

    /**
    * Get email
    *
    * @return string email
    */
    public function getEmail() {
        return (string) $this->_email;
    }

    /**
    * Get date added
    *
    * @return string mysql datetime
    */
    public function getDateAdded() {
        return $this->_date_added;
    }
}
