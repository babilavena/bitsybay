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

class ModelAccountUser extends Model {

    /**
    * Get registered user info
    *
    * @param int $user_id
    * @return object|bool User's PDOStatement::fetch object or false if throw exception
    */
    public function getUser($user_id) {
        try {
            $statement = $this->db->prepare('SELECT * FROM `user` WHERE `user_id` = ? AND `status` = 1 LIMIT 1');
            $statement->execute(array($user_id));

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
    * Get registered user info
    *
    * @param string $email
    * @return object|bool User's PDOStatement::fetch object or false if throw exception
    */
    public function getUserByEmail($email) {
        try {
            $statement = $this->db->prepare('SELECT * FROM `user` WHERE `email` = ? AND `status` = 1 LIMIT 1');
            $statement->execute(array($email));

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
    * Get user emails
    *
    * @param int $user_id
    * @return array|bool user's row set or false if throw exception
    */
    public function getEmails($user_id) {

        try {
            $statement = $this->db->prepare('SELECT `email`, `approved`, `user_id`, `date_added` FROM `user_email` WHERE `user_id` = ?');
            $statement->execute(array($user_id));

            return $statement->rowCount() ? $statement->fetchAll() : array();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check user by password
    *
    * @param int $user_id
    * @param string $password
    * @return bool Returns TRUE if user exists in database or FALSE if else
    */
    public function checkPassword($user_id, $password) {

        try {
            $statement = $this->db->prepare('SELECT NULL FROM `user` WHERE `user_id` = ? AND `password` = SHA1(CONCAT(`salt`, SHA1(CONCAT(`salt`, SHA1(?))))) LIMIT 1');
            $statement->execute(array($user_id, $password));

            if ($statement->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check username availability
    *
    * @param string $username
    * @return bool Returns TRUE if user already exists in database or FALSE if else
    */
    public function checkUsername($username) {

        try {
            $statement = $this->db->prepare('SELECT NULL FROM `user` WHERE `username` = ? LIMIT 1');
            $statement->execute(array($username));

            if ($statement->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Check email availability
    *
    * @param string $email
    * @return bool TRUE if email already exists in database or FALSE if else
    */
    public function checkEmail($email) {

        try {
            // Check user table for email exists
            $statement = $this->db->prepare('SELECT NULL FROM `user` WHERE `email` = ? LIMIT 1');
            $statement->execute(array($email));

            if ($statement->rowCount()) {
                return true;
            }

            // Check user additional email table for email exists
            $statement = $this->db->prepare('SELECT NULL FROM `user_email` WHERE `email` = ? LIMIT 1');
            $statement->execute(array($email));

            if ($statement->rowCount()) {
                return true;
            }

            // Email is not exists in DB
            return false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }

    }

    /**
    * Reset user's password
    *
    * @param string $email
    * @param string $password Raw password string
    * @return int|bool Count affected rows or false if throw exception
    */
    public function resetPassword($email, $password) {

        try {
            $salt = substr(md5(uniqid(rand(), true)), 0, 9);
            $password = sha1($salt . sha1($salt . sha1($password)));
            $email = mb_strtolower($email);

            $statement = $this->db->prepare('UPDATE `user` SET `salt` = ?, `password` = ? WHERE `email` = ? LIMIT 1');
            $statement->execute(array($salt, $password, $email));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Add new email to specific user
    *
    * @param int $user_id
    * @param string $email
    * @param string $approval_code
    * @return int|bool Email approved status or false if throw exception
    */
    public function addEmail($user_id, $email, $approval_code) {

        try {
            $statement = $this->db->prepare('SELECT approved FROM `user_email` WHERE `user_id` = ? AND `email`= ? LIMIT 1');
            $statement->execute(array($user_id, $email));

            if ($statement->rowCount()) {
                $user_email = $statement->fetch();

                return $user_email->approved;

            } else {

                $email = mb_strtolower($email);
                $statement = $this->db->prepare('INSERT INTO `user_email` SET `user_id` = ?, `email` = ?, `approved` = "0", `approval_code` = ?, `date_added` = NOW()');
                $statement->execute(array($user_id, $email, $approval_code));

                return 0;
            }
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Approve email
    *
    * @param int $user_id
    * @param string $email
    * @param string $approval_code
    * @return int|bool Email approved status (affected rows) or false if throw exception
    */
    public function approveEmail($user_id, $email, $approval_code) {

        try {
            $statement = $this->db->prepare('UPDATE `user_email` SET `approved` = 1, approval_code = "" WHERE `user_id` = ? AND `email` = ? AND `approval_code` = ? LIMIT 1');
            $statement->execute(array($user_id, mb_strtolower($email), $approval_code));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Update user info
    *
    * @param int $user_id
    * @param string $username
    * @param string $email
    * @param string $password
    * @param string $approval_code
    * @return int|bool Affected rows or false if throw exception
    */
    public function updateUser($user_id, $username, $email, $password, $approval_code) {

        try {
            $this->addEmail($user_id, $email, $approval_code);

            // Update user info
            $email    = mb_strtolower($email);

            if (!empty($password)) {
                $salt = substr(md5(uniqid(rand(), true)), 0, 9);
                $password = sha1($salt . sha1($salt . sha1($password)));

                $statement = $this->db->prepare('UPDATE `user` SET `username` = :username, `email` = :email, `salt` = :salt, `password` = :password, `date_modified` = NOW() WHERE `user_id` = :user_id LIMIT 1');
                $statement->execute(array(
                    ':user_id'  => $user_id,
                    ':username' => $username,
                    ':email'    => $email,
                    ':salt'     => $salt,
                    ':password' => $password));
            } else {

                $statement = $this->db->prepare('UPDATE `user` SET `username` = :username, `email` = :email, `date_modified` = NOW() WHERE `user_id` = :user_id LIMIT 1');
                $statement->execute(array(
                    ':user_id'  => $user_id,
                    ':username' => $username,
                    ':email'    => $email));
            }

            return $statement->rowCount();
        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Update user notification settings
    *
    * @param int $user_id    User ID
    * @param int $notify_pf  Product favorite
    * @param int $notify_pp  Product purchase
    * @param int $notify_pc  Product comment
    * @param int $notify_pn  Project news
    * @param int $notify_on  Other news
    * @param int $notify_au  Agreement update
    * @param int $notify_ni  New IP
    * @param int $notify_ns  New settings
    * @return int|bool Affected rows or false if throw exception
    */
    public function updateNotificationSettings($user_id, $notify_pf, $notify_pp, $notify_pc, $notify_pn, $notify_on, $notify_au, $notify_ni, $notify_ns) {

        try {

            $statement = $this->db->prepare('UPDATE `user` SET  `notify_pf` = :notify_pf,
                                                                `notify_pp` = :notify_pp,
                                                                `notify_pc` = :notify_pc,
                                                                `notify_pn` = :notify_pn,
                                                                `notify_on` = :notify_on,
                                                                `notify_au` = :notify_au,
                                                                `notify_ni` = :notify_ni,
                                                                `notify_ns` = :notify_ns

                                                                WHERE `user_id` = :user_id
                                                                LIMIT 1');
            $statement->execute(
                array(
                ':user_id'   => $user_id,

                ':notify_pf' => $notify_pf,
                ':notify_pp' => $notify_pp,
                ':notify_pc' => $notify_pc,
                ':notify_pn' => $notify_pn,
                ':notify_on' => $notify_on,
                ':notify_au' => $notify_au,
                ':notify_ni' => $notify_ni,
                ':notify_ns' => $notify_ns,
                )
            );

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Create new user
    *
    * @param string $username
    * @param string $email
    * @param string $password
    * @param int $buyer
    * @param int $seller
    * @param int $status
    * @param int $verified
    * @param int $file_quota Mb
    * @param string $approval_code
    * @return int|bool Returns user_id or false if throw exception
    */
    public function createUser($username, $email, $password, $buyer, $seller, $status, $verified, $file_quota, $approval_code) {

        try {
            $email     = mb_strtolower($email);
            $salt      = substr(md5(uniqid(rand(), true)), 0, 9);
            $password  = sha1($salt . sha1($salt . sha1($password)));

            $statement = $this->db->prepare('INSERT INTO `user` SET

                                            `file_quota`    = :file_quota,
                                            `status`        = :status,
                                            `buyer`         = :buyer,
                                            `seller`        = :seller,
                                            `verified`      = :verified,
                                            `username`      = :username,
                                            `password`      = :password,
                                            `salt`          = :salt,
                                            `email`         = :email,

                                            `notify_pf`     = 1,
                                            `notify_pp`     = 1,
                                            `notify_pc`     = 1,
                                            `notify_pn`     = 1,
                                            `notify_on`     = 1,
                                            `notify_au`     = 1,
                                            `notify_ni`     = 1,
                                            `notify_ns`     = 1,
                                            `notify_ca`     = 1,

                                            `date_added`    = NOW(),
                                            `date_modified` = NOW(),
                                            `date_visit`    = NOW()
                                            ');

            $statement->execute(array(  ':file_quota' => $file_quota,
                                        ':status'     => $status,
                                        ':buyer'      => $buyer,
                                        ':seller'     => $seller,
                                        ':verified'   => $verified,
                                        ':username'   => $username,
                                        ':password'   => $password,
                                        ':email'      => $email,
                                        ':salt'       => $salt));

            $user_id = $this->db->lastInsertId();

            $this->addEmail($user_id, $email, $approval_code, $approval_code);

            return (int) $user_id;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Add verification_request
    *
    * @param int $user_id
    * @param int $currency_id
    * @param string $status ENUM('pending','approved','declined')
    * @param string $address Payment address
    * @param string $code Unique verification code
    * @param string $proof Proof info
    * @return int|bool Returns login_attempt_id or false if throw exception
    */
    public function addVerificationRequest($user_id, $currency_id, $status, $address, $code, $proof) {
        try {
            $statement = $this->db->prepare('INSERT INTO `user_verification_request` SET
            `user_id` = ?,
            `currency_id` = ?,
            `status` = ?,
            `address` = ?,
            `code` = ?,
            `proof` = ?,
            `comment` = NULL,
            `date_conclusion` = NULL,
            `date_added` = NOW()
            ');
            $statement->execute(
                array(
                    $user_id,
                    $currency_id,
                    $status,
                    $address,
                    $code,
                    $proof
                )
            );

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Add login attempt log
    *
    * @param string $login
    * @return int|bool Returns login_attempt_id or false if throw exception
    */
    public function addLoginAttempt($login) {

        try {
            $statement = $this->db->prepare('INSERT INTO `login_attempt` SET `login` = ?, `ip` = ?, `date_added` = NOW()');
            $statement->execute(array($login, $this->request->getRemoteAddress()));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Clear all login attempts from database by login
    *
    * Note: By default, failed login attempts will be removed after 365 days later or if user will be successfully logged
    *
    * @param string $login
    * @param int $days Optional, 365 by default
    * @return int|bool Affected rows or false if throw exception
    */
    public function deleteLoginAttempts($login, $days = 365) {

        try {
            $statement = $this->db->prepare('DELETE FROM `login_attempt` WHERE `login` = ? OR DATE(`date_added`) <= ?');
            $statement->execute(array($login, date('Y-m-d ', strtotime('-' . $days . ' days'))));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total login attempts for specific login
    *
    * @param string $login
    * @return int|bool Total attempts or false if throw exception
    */
    public function getTotalLoginAttempts($login) {

        try {
            $statement = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `login_attempt` WHERE `login` = ?');
            $statement->execute(array($login));

            $login_attempt = $statement->fetch();

            return $login_attempt->total;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total sellers
    *
    * @return int|bool Total sellers or false if throw exception
    */
    public function getTotalSellers() {

        try {
            $statement = $this->db->prepare('SELECT COUNT(DISTINCT `user_id`) AS `total` FROM `product`');
            $statement->execute();

            $total_sellers = $statement->fetch();

            return $total_sellers->total;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Get total users
    *
    * @return int|bool Total users or false if throw exception
    */
    public function getTotalUsers() {

        try {
            $statement = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `user`');
            $statement->execute();

            $total_sellers = $statement->fetch();

            return $total_sellers->total;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
