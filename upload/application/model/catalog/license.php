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

class ModelCatalogLicense extends Model {

    /**
     * Get licenses
     *
     * @param int $language_id
     * @param int|null $user_id integer user_id for custom licenses or bool null for general licenses
     * @return array|bool License rows or FALSE if throw exception
     */
    public function getLicenses($language_id, $user_id = null)
    {
        try {
            $statement = $this->db->prepare('SELECT

                                            `l`.`user_id`,
                                            `l`.`license_id`,
                                            `ld`.`title`,
                                            `ld`.`description`

                                            FROM `license` AS `l`
                                            LEFT JOIN `license_description` AS `ld` ON (`l`.`license_id` = `ld`.`license_id`)
                                            WHERE `l`.`user_id` ' . (is_null($user_id) ? 'IS' : '=') . ' ?
                                            AND `ld`.`language_id` = ?
                                            ORDER BY LCASE(`title`)');

            $statement->execute(array($user_id, $language_id));

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
     * Get single license
     *
     * @param int $license_id
     * @param int $language_id
     * @return array|bool License rows or FALSE if throw exception
     */
    public function getLicense($license_id, $language_id)
    {
        try {
            $statement = $this->db->prepare('SELECT

                                            `l`.`user_id`,
                                            `l`.`license_id`,
                                            `ld`.`title`,
                                            `ld`.`description`

                                            FROM `license` AS `l`
                                            LEFT JOIN `license_description` AS `ld` ON (`l`.`license_id` = `ld`.`license_id`)
                                            WHERE `l`.`license_id` = ?
                                            AND `ld`.`language_id` = ?
                                            LIMIT 1');

            $statement->execute(array($license_id, $language_id));

            return $statement->rowCount() ? $statement->fetch() : array();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
     * Get single license descriptions
     *
     * @param int $license_id
     * @return array|bool License rows or FALSE if throw exception
     */
    public function getLicenseDescriptions($license_id)
    {
        try {
            $statement = $this->db->prepare('SELECT * FROM `license_description` WHERE `license_id` = ?');
            $statement->execute(array($license_id));

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
