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

class ModelCommonRedirect extends Model {

    /**
    * Save redirect 301 rule
    *
    * @param int $code
    * @param string $uri_from
    * @param string $uri_to
    * @return array|bool last insert id or false if throw exception
    */
    public function createRedirect($code, $uri_from, $uri_to) {

        try {

            $statement = $this->db->prepare('INSERT INTO `redirect` SET `code` = ?, `uri_from` = ?, `uri_to` = ?, `requested` = 0, `date_added` = NOW()');
            $statement->execute(array($code, $uri_from, $uri_to));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
