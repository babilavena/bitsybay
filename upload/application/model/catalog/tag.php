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

class ModelCatalogTag extends Model {

    /**
    * Add tag
    *
    * @param string $name
    * @param int $language_id
    * @return int|bool tag_id or FALSE if throw exception
    */
    public function createTag($name, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT `t`.`tag_id`
                                             FROM `tag` AS `t`
                                             LEFT JOIN `tag_description` AS `td` ON (`t`.`tag_id` = `td`.`tag_id`)
                                             WHERE `td`.`name` = :name
                                             AND `td`.`language_id` = :language_id
                                             LIMIT 1');

            $statement->execute(array(
                ':name'        => $name,
                ':language_id' => $language_id));

            if ($statement->rowCount()) {

                $tag = $statement->fetch();

                return $tag->tag_id;

            } else {

                $statement = $this->db->prepare('INSERT INTO `tag` SET `date_added` = NOW()');
                $statement->execute();

                $tag_id = $this->db->lastInsertId();

                $statement = $this->db->prepare('INSERT INTO `tag_description` SET
                                                `tag_id`      = :tag_id,
                                                `language_id` = :language_id,
                                                `name`        = :name');

                $statement->execute(array(
                    ':tag_id'      => $tag_id,
                    ':language_id' => $language_id,
                    ':name'        => $name));

                return $tag_id;
            }

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }



    /**
    * Get tag descriptions
    *
    * @param int $tag_id
    * @return array|bool Product descriptions row or FALSE if throw exception
    */
    public function getTagDescriptions($tag_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `tag_description` WHERE `tag_id` = ?');
            $statement->execute(array($tag_id));

            return $statement->rowCount() ? $statement->fetchAll() : array();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Get tags
    *
    * @param array $filter_data
    * @param int $language_id
    * @return array|bool Tag rows or FALSE if throw exception
    */
    public function getTags($filter_data, $language_id) {

        // $where = array();
        // $place_holders = array();

        $sql = 'SELECT `t`.`tag_id`, `td`.`name`
                    FROM `tag` AS `t`
                    LEFT JOIN `tag_description` AS `td` ON (`t`.`tag_id` = `td`.`tag_id`)
                    WHERE `td`.`language_id` = :language_id';

        if (isset($filter_data['limit'])) {
            $sql .= ' LIMIT ' . (int) $filter_data['limit'];
        }

        try {
            $statement = $this->db->prepare($sql);

            $statement->execute(array(':language_id' => $language_id));

            return $statement->rowCount() ? $statement->fetchAll() : array();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }
}
