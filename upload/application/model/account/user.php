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

            // Email is not exists in DB
            return false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }

    }

    /**
    * Check password reset by code
    *
    * @param string $code
    * @return int|bool user_id or false if throw exception
    */
    public function getPasswordReset($code) {

        try {

            $statement = $this->db->prepare('SELECT `user_id` FROM `user_password_reset` WHERE `code` = ? AND `date_added` > NOW() - INTERVAL 30 MINUTE LIMIT 1');
            $statement->execute(array($code));

            if ($statement->rowCount()) {
                $result = $statement->fetch();
                return $result->user_id;
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Add new password reset
    *
    * @param int $user_id
    * @param string $code
    * @param string $ip
    * @return int|bool last insert id or false if throw exception
    */
    public function addPasswordReset($user_id, $ip, $code) {

        try {

            $statement = $this->db->prepare('INSERT INTO `user_password_reset` SET `user_id` = ?, `ip` = ?, `code` = ?, `date_added` = NOW()');
            $statement->execute(array($user_id, $ip, $code));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Delete all password reset requests
    *
    * @param int $user_id
    * @return int|bool row count or false if throw exception
    */
    public function deletePasswordReset($user_id) {

        try {

            $statement = $this->db->prepare('DELETE FROM `user_password_reset` WHERE `user_id` = ?');
            $statement->execute(array($user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Update user password
    *
    * @param int $user_id
    * @param string $password Raw password string
    * @return int|bool Count affected rows or false if throw exception
    */
    public function updatePassword($user_id, $password) {

        try {
            $salt = substr(md5(uniqid(rand(), true)), 0, 9);
            $password = sha1($salt . sha1($salt . sha1($password)));

            $statement = $this->db->prepare('UPDATE `user` SET `salt` = ?, `password` = ? WHERE `user_id` = ? LIMIT 1');
            $statement->execute(array($salt, $password, $user_id));

            return $statement->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Approve email
    *
    * @param int $user_id
    * @param string $approval_code
    * @return int|bool Email approved status (affected rows) or false if throw exception
    */
    public function approveEmail($user_id, $approval_code) {

        try {
            $statement = $this->db->prepare('UPDATE `user` SET `approved` = 1, approval_code = "" WHERE `user_id` = ? AND `approval_code` = ? LIMIT 1');
            $statement->execute(array($user_id, $approval_code));

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
    * @param string $approved
    * @return int|bool Affected rows or false if throw exception
    */
    public function updateUser($user_id, $username, $email, $password, $approval_code, $approved) {

        try {
            // Update user info
            $email = mb_strtolower($email);

            if (!empty($password)) {

                $salt = substr(md5(uniqid(rand(), true)), 0, 9);
                $password = sha1($salt . sha1($salt . sha1($password)));

                $statement = $this->db->prepare('UPDATE `user` SET  `username`      = :username,
                                                                    `email`         = :email,
                                                                    `approval_code` = :approval_code,
                                                                    `approved`      = :approved,
                                                                    `salt`          = :salt,
                                                                    `password`      = :password,

                                                                    `date_modified` = NOW()

                                                                    WHERE `user_id` = :user_id
                                                                    LIMIT 1');

                $statement->execute(array(
                    ':user_id'       => $user_id,
                    ':username'      => $username,
                    ':email'         => $email,
                    ':approval_code' => $approval_code,
                    ':approved'      => $approved,
                    ':salt'          => $salt,
                    ':password'      => $password));

            } else {

                $statement = $this->db->prepare('UPDATE `user` SET `username`      = :username,
                                                                   `email`         = :email,
                                                                   `approval_code` = :approval_code,
                                                                   `approved`      = :approved,

                                                                   `date_modified` = NOW()

                                                                   WHERE `user_id` = :user_id
                                                                   LIMIT 1');

                $statement->execute(array(
                    ':user_id'       => $user_id,
                    ':username'      => $username,
                    ':approval_code' => $approval_code,
                    ':approved'      => $approved,
                    ':email'         => $email));
            }

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
    * @param string $approved
    * @return int|bool Returns user_id or false if throw exception
    */
    public function createUser($username, $email, $password, $buyer, $seller, $status, $verified, $file_quota, $approval_code, $approved) {

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
                                            `approval_code` = :approval_code,
                                            `approved`      = :approved,

                                            `date_added`    = NOW(),
                                            `date_modified` = NOW(),
                                            `date_visit`    = NOW()
                                            ');

            $statement->execute(array(  ':file_quota'    => $file_quota,
                                        ':status'        => $status,
                                        ':buyer'         => $buyer,
                                        ':seller'        => $seller,
                                        ':verified'      => $verified,
                                        ':username'      => $username,
                                        ':password'      => $password,
                                        ':email'         => $email,
                                        ':approval_code' => $approval_code,
                                        ':approved'      => $approved,
                                        ':salt'          => $salt));

            $user_id = $this->db->lastInsertId();

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
