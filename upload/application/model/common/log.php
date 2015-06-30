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

class ModelCommonLog extends Model {

    /**
    * Save search term
    *
    * @param int $user_id
    * @param string $term
    * @param int $results
    * @return array|bool last insert id or false if throw exception
    */
    public function createLogSearch($user_id, $term, $results) {

        try {

            if ($user_id) {
                $statement = $this->db->prepare('INSERT INTO `log_search` SET `user_id` = ?, `term` = ?, `results` = ?, `date_added` = NOW()');
                $statement->execute(array($user_id, $term, $results));
            } else {
                $statement = $this->db->prepare('INSERT INTO `log_search` SET `user_id` = NULL, `term` = ?, `results` = ?, `date_added` = NOW()');
                $statement->execute(array($term, $results));
            }


            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    /**
    * Save 404 log
    *
    * @param int $user_id
    * @param string $request
    * @param string $referrer
    * @return array|bool last insert id or false if throw exception
    */
    public function createLog404($user_id, $request, $referrer) {

        try {

            if ($user_id) {
                $statement = $this->db->prepare('INSERT INTO `log_404` SET `user_id` = ?, `request` = ?, `referrer` = ?, `date_added` = NOW()');
                $statement->execute(array($user_id, $request, $referrer));
            } else {
                $statement = $this->db->prepare('INSERT INTO `log_404` SET `user_id` = NULL, `request` = ?, `referrer` = ? , `date_added` = NOW()');
                $statement->execute(array($request, $referrer));
            }


            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
