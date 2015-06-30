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

class ModelCatalogCategory extends Model {

    /**
    * Get categories
    *
    * @param int|null $category_id int parent_category_id or null for main category
    * @param int $language_id
    * @param bool $count_product
    * @return array|bool Categories rows or false if throw exception
    */
    public function getCategories($category_id, $language_id, $count_product = false) {

        try {
            $statement = $this->db->prepare('SELECT
            `c`.`category_id`,
            `c`.`parent_category_id`,
            `c`.`alias`,
            `c`.`sort_order`,

            ' . ($count_product ? ' (SELECT COUNT(*) FROM `product` AS `p` WHERE `p`.`category_id` = `c`.`category_id`) AS `total_products`, ' : false) . '

            `cd`.`title`

             FROM `category` AS `c`
             LEFT JOIN `category_description` AS `cd` ON (`cd`.`category_id` = `c`.`category_id`)
             WHERE `c`.`parent_category_id` ' . (is_null($category_id) ? 'IS' : '=') . ' ? AND `cd`.`language_id` = ? ORDER BY `c`.`sort_order`, LCASE(`cd`.`title`)');

            $statement->execute(array($category_id, $language_id));

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
    * Get total categories
    *
    * @return int|bool Categories total rows or false if throw exception
    */
    public function getTotalCategories() {

        try {
            $statement = $this->db->prepare('SELECT
            (SELECT COUNT(*) FROM `product` AS `p` WHERE `p`.`category_id` = `c`.`category_id`) AS `total_products`
            FROM `category` AS `c`
            GROUP BY `c`.`category_id` HAVING `total_products` > 0');

            $statement->execute();

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
    * Get category
    *
    * @param int $category_id int category_id
    * @param int $language_id
    * @return resource|bool Category row or false if throw exception
    */
    public function getCategory($category_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
            `c`.`category_id`,
            `c`.`parent_category_id`,
            `c`.`alias`,
            `c`.`sort_order`,

            `cd`.`title`

             FROM `category` AS `c`
             LEFT JOIN `category_description` AS `cd` ON (`cd`.`category_id` = `c`.`category_id`)
             WHERE `c`.`category_id` = ? AND `cd`.`language_id` = ? LIMIT 1');

            $statement->execute(array($category_id, $language_id));

            return $statement->rowCount() ? $statement->fetch() : array();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }
}
