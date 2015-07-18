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

class ModelCommonAudioServer extends Model {

    /**
    * Get audio servers
    *
    * @return array|bool Audio servers rows or false if throw exception
    */
    public function getAudioServers() {

        try {
            $statement = $this->db->prepare('SELECT * FROM `audio_server` ORDER BY `name` DESC');
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
    * Get audio server
    *
    * @param int $audio_server_id
    * @return array|bool Audio server row or false if throw exception
    */
    public function getAudioServer($audio_server_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `audio_server` WHERE `audio_server_id` = ? ORDER BY `name` DESC');
            $statement->execute(array($audio_server_id));

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
