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

class ModelCatalogProduct extends Model {

    /**
    * Get product
    *
    * @param int $product_id
    * @param int $session_user_id
    * @param int $approved_order_status_id
    * @return resource|bool Resource product row or FALSE if throw exception
    */
    public function getProduct($product_id, $session_user_id, $approved_order_status_id) {

        // todo: unstable priority [`o`.`order_status_id` DESC]
        try {
            $statement = $this->db->prepare('SELECT

            `p`.`product_id`,
            `p`.`category_id`,
            `p`.`user_id`,
            `p`.`license_id`,
            `p`.`currency_id`,
            `p`.`viewed`,
            `p`.`status`,
            `p`.`regular_price`,
            `p`.`exclusive_price`,
            `p`.`date_added`,
            `p`.`date_modified`,
            `pd`.`title`,
            `pd`.`description`,
            `ps`.`regular_price` AS `special_regular_price`,
            `ps`.`exclusive_price` AS `special_exclusive_price`,
            `ps`.`date_end` AS `special_date_end`,
            `ld`.`title` AS `license_title`,
            `ld`.`description` AS `license_description`,

            (SELECT `u`.`username` FROM `user` AS `u` WHERE `u`.`user_id` = `p`.`user_id` LIMIT 1) AS `username`,
            (SELECT `uv`.`verified` FROM `user` AS `uv` WHERE `uv`.`user_id` = `p`.`user_id` LIMIT 1) AS `verified`,
            (SELECT `o`.`order_status_id` FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`user_id` = :session_user_id ORDER BY `o`.`order_status_id` DESC  LIMIT 1) AS `order_status_id`,
            (SELECT COUNT(*) FROM `product_favorite` AS `pf` WHERE `pf`.`product_id` = `p`.`product_id`) AS `favorites`,
            (SELECT COUNT(*) FROM `product_favorite` AS `pf` WHERE `pf`.`product_id` = `p`.`product_id` AND `user_id` = :session_user_id LIMIT 1) AS `favorite`,
            (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`order_status_id` = :approved_order_status_id) AS `sales`,
            (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`order_status_id` = :approved_order_status_id AND `o`.`license` = "exclusive" LIMIT 1) AS `sold_as_exclusive`,
            (SELECT `pi`.`product_image_id` FROM `product_image` AS `pi` WHERE `pi`.`product_id` = `p`.`product_id` AND `pi`.`main` = 1 LIMIT 1) AS `main_product_image_id`,
            (SELECT `pdm`.`product_demo_id` FROM `product_demo` AS `pdm` WHERE `pdm`.`product_id` = `p`.`product_id` AND `pdm`.`main` = 1 LIMIT 1) AS `main_product_demo_id`

            FROM `product` AS `p`
            JOIN `product_description` AS `pd` ON (`pd`.`product_id` = `p`.`product_id`)
            LEFT JOIN `license` AS `l` ON (`l`.`license_id` = `p`.`license_id`)
            LEFT JOIN `license_description` AS `ld` ON (`ld`.`license_id` = `l`.`license_id`)
            LEFT JOIN `product_special` AS `ps` ON (`ps`.`product_id` = `p`.`product_id` AND `ps`.`date_start` < NOW() AND `ps`.`date_end` > NOW())

            WHERE `p`.`product_id` = :product_id AND (`p`.`status` = 1 OR (`p`.`user_id` = :session_user_id AND `p`.`status` = 0))
            HAVING (`order_status_id` = :approved_order_status_id OR `sold_as_exclusive` <> 1 OR (`p`.`user_id` = :session_user_id AND `sold_as_exclusive` <> 0))
            LIMIT 1');

            $statement->execute(array(':session_user_id' => $session_user_id, ':product_id' => $product_id, ':approved_order_status_id' => $approved_order_status_id));

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
    * Get products
    *
    * @param array $filter_data
    * @param int $language_id
    * @param int $session_user_id
    * @param int $approved_order_status_id
    * @return array|bool array Product rows or FALSE if throw exception
    */
    public function getProducts($filter_data, $language_id, $session_user_id, $approved_order_status_id) {

        $where = array();
        $having = array();
        $place_holders = array();

        // todo: unstable priority [`o`.`order_status_id` DESC]
        $sql = 'SELECT
                `p`.`product_id`,
                `p`.`category_id`,
                `p`.`user_id`,
                `p`.`license_id`,
                `p`.`currency_id`,
                `p`.`viewed`,
                `p`.`status`,
                `p`.`regular_price`,
                `p`.`exclusive_price`,
                `p`.`date_added`,
                `p`.`date_modified`,
                `pd`.`title`,
                `pd`.`description`,
                `ps`.`regular_price` AS `special_regular_price`,
                `ps`.`exclusive_price` AS `special_exclusive_price`,
                `ps`.`date_end` AS `special_date_end`,

                (SELECT `u`.`username` FROM `user` AS `u` WHERE `u`.`user_id` = `p`.`user_id` LIMIT 1) AS `username`,
                (SELECT `o`.`order_status_id` FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`user_id` = :session_user_id ORDER BY `o`.`order_status_id` DESC LIMIT 1) AS `order_status_id`,
                (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`order_status_id` = :approved_order_status_id) AS `sales`,
                (SELECT COUNT(*) FROM `order` AS `o` WHERE `o`.`product_id` = `p`.`product_id` AND `o`.`order_status_id` = :approved_order_status_id AND `o`.`license` = "exclusive" LIMIT 1) AS `sold_as_exclusive`,
                (SELECT COUNT(*) FROM `product_favorite` AS `pf` WHERE `pf`.`product_id` = `p`.`product_id`) AS `favorites`,
                (SELECT COUNT(*) FROM `product_favorite` AS `pf` WHERE `pf`.`product_id` = `p`.`product_id` AND `pf`.`user_id` = :session_user_id LIMIT 1) AS `favorite`,
                (SELECT `pi`.`product_image_id` FROM `product_image` AS `pi` WHERE `pi`.`product_id` = `p`.`product_id` AND `pi`.`main` = 1 LIMIT 1) AS `main_product_image_id`,
                (SELECT `pdm`.`product_demo_id` FROM `product_demo` AS `pdm` WHERE `pdm`.`product_id` = `p`.`product_id` AND `pdm`.`main` = 1 LIMIT 1) AS `main_product_demo_id`

                FROM `product` AS `p`
                JOIN `product_description` AS `pd` ON (`pd`.`product_id` = `p`.`product_id`)
                LEFT JOIN `product_special` AS `ps` ON (`ps`.`product_id` = `p`.`product_id` AND `ps`.`date_start` < NOW() AND `ps`.`date_end` > NOW()) ';

        // Filter by user ID
        if (isset($filter_data['user_id'])) {
            $where[] = '`p`.`user_id` = :user_id';
            $place_holders[':user_id'] = $filter_data['user_id'];
        }

        // Filter by category ID
        if (isset($filter_data['category_id'])) {
            $where[] = '`p`.`category_id` = :category_id';
            $place_holders[':category_id'] = $filter_data['category_id'];
        }

        // Filter by parent category ID
        if (isset($filter_data['category_ids']) && $filter_data['category_ids']) {

            $term_where = array();
            foreach ($filter_data['category_ids'] as $key => $value) {
                $term_where[] = '`p`.`category_id` = :category_id_' . $key;
                $place_holders[':category_id_' . $key] = $value;
            }

            $where[] = '(' . implode(' OR ', $term_where) . ')';
        }

        // Filter by favorites
        if (isset($filter_data['favorites'])) {
            $having[] = '`favorite` > 0';
        }

        // Filter by purchased
        if (isset($filter_data['purchased'])) {
            $having[] = '`order_status_id` = :approved_order_status_id';
        }

        // Filter by search term
        if (isset($filter_data['filter_query'])) {


            $filter_data['filter_query'] = trim($filter_data['filter_query']);

            if (strpos($filter_data['filter_query'], ' ')) {
                $terms = explode(' ', $filter_data['filter_query']);

                $term_where = array();
                foreach ($terms as $key => $term) {
                    $term_where[] = '`pd`.`title` LIKE :filter_query_' . $key;
                    $place_holders[':filter_query_' . $key] = '%' . substr($term, 0, -1) . '%';
                }

                $where[] = '(' . implode(' OR ', $term_where) . ')';
            } else {

                $sql .= 'LEFT JOIN `product_to_tag` AS `p2t` ON (`p2t`.`product_id` = `p`.`product_id`)';
                $sql .= 'LEFT JOIN `tag_description` AS `td` ON (`td`.`tag_id` = `p2t`.`tag_id` AND `td`.`language_id` = `pd`.`language_id`)';


                $where[] = '(`pd`.`title` LIKE :filter_query OR `td`.`name` LIKE :filter_query)';
                $place_holders[':filter_query'] = '%' . substr($filter_data['filter_query'], 0, -1) . '%';
            }
        }


        // Custom user results
        $place_holders[':session_user_id'] = $session_user_id;
        $place_holders[':approved_order_status_id'] = $approved_order_status_id;

        // Required
        $where[] = '`pd`.`language_id` = :language_id';
        $place_holders[':language_id'] = $language_id;
        $where[] = '(`p`.`status` = 1 OR (`p`.`user_id` = :session_user_id AND `p`.`status` = 0))';

        $having[] = '(`order_status_id` = :approved_order_status_id OR `sold_as_exclusive` <> 1 OR (`p`.`user_id` = :session_user_id AND `sold_as_exclusive` <> 0))';


        $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY `p`.`product_id`';

        $sql .= ' HAVING ' . implode(' AND ', $having);

        if (isset($filter_data['sort']) && in_array(strtolower($filter_data['sort']), array('`p`.`product_id`'))) {
            $sql .= ' ORDER BY `p`.`status`, ' . $filter_data['sort'];
        } else {
            $sql .= ' ORDER BY `p`.`status`, `p`.`product_id`';
        }

        if (isset($filter_data['order']) && 'desc' == strtolower($filter_data['order'])) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($filter_data['limit'])) {
            $sql .= ' LIMIT ' . (int) $filter_data['limit'];
        }

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute($place_holders);

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
    * Check if product identicon
    *
    * @param int $product_image_id
    * @return bool TRUE if identicon or FALSE if else or throw exception
    */
    public function isProductImageIdenticon($product_image_id) {

        try {
            $statement = $this->db->prepare('SELECT NULL FROM `product_image` WHERE `product_image_id` = ? LIMIT 1');
            $statement->execute(array($product_image_id));

            return (bool) $statement->rowCount();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Get product images info
    *
    * @param int $product_id
    * @return array|bool Product images rows or FALSE if throw exception
    */
    public function getProductImagesInfo($product_id) {

        try {
            $statement = $this->db->prepare('SELECT `product_image_id`, `main`, `watermark`, `identicon` FROM `product_image` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Get product image
    *
    * @param int $product_image_id
    * @return array|bool Product image row or FALSE if throw exception
    */
    public function getProductImageInfo($product_image_id) {

        try {
            $statement = $this->db->prepare('SELECT `product_image_id`, `main`, `identicon`, `watermark` FROM `product_image` WHERE `product_image_id` = ? LIMIT 1');
            $statement->execute(array($product_image_id));

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
    * Get product image descriptions
    *
    * @param int $product_image_id
    * @return array|bool Product image descriptions rows or FALSE if throw exception
    */
    public function getProductImageDescriptions($product_image_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `product_image_description` WHERE `product_image_id` = ?');
            $statement->execute(array($product_image_id));

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
    * Get product specials
    *
    * @param int $product_id
    * @return array|bool Product specials rows or FALSE if throw exception
    */
    public function getProductSpecials($product_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `product_special` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Get product videos
    *
    * @param int $product_id
    * @param int $language_id
    * @return array|bool Product video rows or FALSE if throw exception
    */
    public function getProductVideos($product_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
            `pv`.`product_video_id`,
            `pv`.`video_server_id`,
            `pv`.`id`,
            `vs`.`iframe_url`,
            `pvd`.`title`

            FROM `product_video` AS `pv`
            JOIN `video_server` AS `vs` ON (`vs`.`video_server_id` = `pv`.`video_server_id`)
            LEFT JOIN `product_video_description` AS `pvd` ON (`pv`.`product_video_id` = `pvd`.`product_video_id`)
            WHERE `pv`.`product_id` = ? AND `pvd`.`language_id` = ?');

            $statement->execute(array($product_id, $language_id));

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
    * Get product video descriptions
    *
    * @param int $product_video_id
    * @return array|bool Product video descriptions rows or FALSE if throw exception
    */
    public function getProductVideoDescriptions($product_video_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `product_video_description` WHERE `product_video_id` = ?');
            $statement->execute(array($product_video_id));

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
    * Get product demo
    *
    * @param int $product_demo_id
    * @param int $language_id
    * @return array|bool Product demo row or FALSE if throw exception
    */
    public function getProductDemo($product_demo_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
            `pd`.`product_demo_id`, `pd`.`product_id`, `pd`.`main`, `pd`.`url`, `pdd`.`title`
            FROM `product_demo` AS `pd`
            LEFT JOIN `product_demo_description` AS `pdd` ON (`pdd`.`product_demo_id` = `pd`.`product_demo_id`)
            WHERE `pd`.`product_demo_id` = ? AND `pdd`.`language_id` = ? LIMIT 1');

            $statement->execute(array($product_demo_id, $language_id));

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
    * Get product demos
    *
    * @param int $product_id
    * @param int $language_id
    * @return array|bool Product demo rows or FALSE if throw exception
    */
    public function getProductDemos($product_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
             `pd`.`product_demo_id`, `pd`.`product_id`, `pd`.`main`, `pd`.`url`, `pdd`.`title`
              FROM `product_demo` AS `pd`
              LEFT JOIN `product_demo_description` AS `pdd` ON (`pdd`.`product_demo_id` = `pd`.`product_demo_id`)
              WHERE `pd`.`product_id` = ? AND `pdd`.`language_id` = ? ');

            $statement->execute(array($product_id, $language_id));

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
    * Get product reviews
    *
    * @param int $product_id
    * @param int $language_id
    * @return array|bool Product demo rows or FALSE if throw exception
    */
    public function getProductReviews($product_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
            `pr`.*,
            (SELECT `username` FROM `user` AS `u` WHERE `u`.`user_id` = `pr`.`user_id` LIMIT 1) AS `username`,
            (SELECT COUNT(*) FROM `product_favorite` AS `pf` WHERE `pf`.`user_id` = `pr`.`user_id` AND `pf`.`product_id` = `pr`.`product_id` LIMIT 1) AS `favorite`
            FROM `product_review` AS `pr`
            WHERE `pr`.`product_id` = ? AND `pr`.`language_id` = ? AND `pr`.`status` = 1');

            $statement->execute(array($product_id, $language_id));

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
    * Get product images
    *
    * @param int $product_id
    * @param int $language_id
    * @return array|bool Product image rows or FALSE if throw exception
    */
    public function getProductImages($product_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
             `pi`.`product_image_id`, `pi`.`main`, `pi`.`watermark`, `pi`.`identicon`, `pid`.`title`
              FROM `product_image` AS `pi`
              LEFT JOIN `product_image_description` AS `pid` ON (`pid`.`product_image_id` = `pi`.`product_image_id`)
              WHERE `pi`.`product_id` = ? AND `pid`.`language_id` = ? ');

            $statement->execute(array($product_id, $language_id));

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
    * Get product tags
    *
    * @param int $product_id
    * @param int $language_id
    * @return array|bool Product tags rows or FALSE if throw exception
    */
    public function getProductTags($product_id, $language_id) {

        try {
            $statement = $this->db->prepare('SELECT
             `t`.`tag_id`, `p2t`.`product_id`, `td`.`name`
              FROM `product_to_tag` AS `p2t`
              JOIN `tag` AS `t` ON (`p2t`.`tag_id` = `t`.`tag_id`)
              JOIN `tag_description` AS `td` ON (`td`.`tag_id` = `t`.`tag_id`)
              WHERE `p2t`.`product_id` = ? AND `td`.`language_id` = ? ');

            $statement->execute(array($product_id, $language_id));

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
    * Get product demo descriptions
    *
    * @param int $product_demo_id
    * @return array|bool Product demo descriptions rows or FALSE if throw exception
    */
    public function getProductDemoDescriptions($product_demo_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `product_demo_description` WHERE `product_demo_id` = ?');
            $statement->execute(array($product_demo_id));

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
    * Get product descriptions
    *
    * @param int $product_id
    * @return array|bool Product descriptions row or FALSE if throw exception
    */
    public function getProductDescriptions($product_id) {

        try {
            $statement = $this->db->prepare('SELECT * FROM `product_description` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Get product withdraw address
    *
    * @param int $product_id
    * @return string|bool Product withdraw address or FALSE if throw exception
    */
    public function getWithdrawAddress($product_id) {

        try {
            $statement = $this->db->prepare('SELECT `withdraw_address` FROM `product` WHERE `product_id` = ? LIMIT 1');
            $statement->execute(array($product_id));

            if ($statement->rowCount()) {
                $result = $statement->fetch();
                return $result->withdraw_address;
            } else {
                return '';
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
    * Get product total
    *
    * @param array $filter_data
    * @return int Product rows or FALSE if throw exception
    */
    public function getTotalProducts($filter_data) {

        try {
            $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM `product`');
            $statement->execute();


            $products = $statement->fetch();

            return (int) $statement->rowCount() ? $products->total : 0;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Delete product tags
    *
    * @param int $product_id
    * @return int|bool Affected rows or FALSE if throw exception
    */
    public function deleteProductToTag($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_to_tag` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Add product to tag relation
    *
    * @param int $product_id
    * @param int $tag_id
    * @return int|bool product_to_tag_id tag_id or FALSE if throw exception
    */
    public function addProductToTag($product_id, $tag_id) {

        try {
            $statement = $this->db->prepare('INSERT IGNORE INTO `product_to_tag` SET `tag_id` = ?, `product_id` = ?');
            $statement->execute(array($tag_id, $product_id));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product
    *
    * @param int $user_id
    * @param int $category_id
    * @param int $license_id
    * @param int $currency_id
    * @param float $regular_price
    * @param float $exclusive_price
    * @param string $withdraw_address
    * @param string $alias
    * @return int|bool product_id or FALSE/rollBack if throw exception
    */
    public function createProduct($user_id, $category_id, $license_id, $currency_id, $regular_price, $exclusive_price, $withdraw_address, $alias) {

        try {
            // Product
            $statement = $this->db->prepare(
                'INSERT INTO `product` SET
                `user_id`          = :user_id,
                `category_id`      = :category_id,
                `license_id`       = :license_id,
                `currency_id`      = :currency_id,
                `regular_price`    = :regular_price,
                `exclusive_price`  = :exclusive_price,
                `alias`            = "",
                `withdraw_address` = :withdraw_address,
                `viewed`           = 0,
                `status`           = 1,
                `date_added`       = NOW(),
                `date_modified`    = NOW()');

            $statement->execute(array(
                ':user_id'          => $user_id,
                ':category_id'      => $category_id,
                ':license_id'       => $license_id,
                ':currency_id'      => $currency_id,
                ':regular_price'    => $regular_price,
                ':exclusive_price'  => $exclusive_price,
                ':withdraw_address' => $withdraw_address
            ));

            $product_id = $this->db->lastInsertId();

            $statement = $this->db->prepare('UPDATE `product` SET `alias` = CONCAT(`product_id`, "-", ?) WHERE `product_id` = ? LIMIT 1');
            $statement->execute(array($alias, $product_id));

            return $product_id;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product description
    *
    * @param int $product_id
    * @param int $language_id
    * @param string $title
    * @param string $description
    * @return int|bool product_description_id or FALSE/rollBack if throw exception
    */
    public function createProductDescription($product_id, $language_id, $title, $description) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_description` SET
                `product_id`  = :product_id,
                `language_id` = :language_id,
                `title`       = :title,
                `description` = :description');

            $statement->execute(array(
                ':product_id'  => $product_id,
                ':language_id' => $language_id,
                ':title'       => $title,
                ':description' => $description
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Delete product description
    *
    * @param int $product_id
    * @return int|bool affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductDescriptions($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_description` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Create product demo
    *
    * @param int $product_id
    * @param int $sort_order
    * @param int $main
    * @param string $url
    * @return int|bool product_demo_id or FALSE/rollBack if throw exception
    */
    public function createProductDemo($product_id, $sort_order, $url, $main) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_demo` SET
                `product_id`  = :product_id,
                `sort_order`  = :sort_order,
                `main`        = :main,
                `url`         = :url');

            $statement->execute(array(
                ':product_id' => $product_id,
                ':sort_order' => $sort_order,
                ':main'       => $main,
                ':url'        => $url
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product image
    *
    * @param int $product_id
    * @param int $sort_order
    * @param int $main
    * @param int $watermark
    * @param int $identicon
    * @return int|bool product_image_id or FALSE/rollBack if throw exception
    */
    public function createProductImage($product_id, $sort_order, $main, $watermark, $identicon = 0) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_image` SET
                `product_id`  = :product_id,
                `sort_order`  = :sort_order,
                `main`        = :main,
                `watermark`   = :watermark,
                `identicon`   = :identicon');

            $statement->execute(array(
                ':product_id' => $product_id,
                ':sort_order' => $sort_order,
                ':main'       => $main,
                ':identicon'  => $identicon,
                ':watermark'  => $watermark
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Update product image info
    *
    * @param int $product_image_id
    * @param int $sort_order
    * @param int $main
    * @param int $watermark
    * @return int|bool Count affected rows or FALSE/rollBack if throw exception
    */
    public function updateProductImageInfo($product_image_id, $sort_order, $main, $watermark) {

        try {
            $statement = $this->db->prepare('UPDATE `product_image` SET
                                            `sort_order` = :sort_order,
                                            `main`       = :main,
                                            `watermark`  = :watermark
                                             WHERE
                                            `product_image_id` = :product_image_id
                                             LIMIT 1');

            $statement->execute(array(
                ':product_image_id' => $product_image_id,
                ':sort_order'       => $sort_order,
                ':watermark'        => $watermark,
                ':main'             => $main
            ));

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
    * Create product video
    *
    * @param int $product_id
    * @param int $video_server_id
    * @param int $sort_order
    * @param string $id
    * @return int|bool product_video_id or FALSE/rollBack if throw exception
    */
    public function createProductVideo($product_id, $video_server_id, $sort_order, $id) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_video` SET
                `product_id`      = :product_id,
                `video_server_id` = :video_server_id,
                `sort_order`      = :sort_order,
                `id`              = :id');

            $statement->execute(array(
                ':product_id'      => $product_id,
                ':video_server_id' => $video_server_id,
                ':sort_order'      => $sort_order,
                ':id'              => $id
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product special
    *
    * @param int $product_id
    * @param int $sort_order
    * @param int|float $regular_price
    * @param int|float $exclusive_price
    * @param string $date_start YYYY-MM-DD
    * @param string $date_end YYYY-MM-DD
    * @return int|bool product_special_id or FALSE/rollBack if throw exception
    */
    public function createProductSpecial($product_id, $regular_price, $exclusive_price, $date_start, $date_end, $sort_order) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_special` SET
                `product_id`      = :product_id,
                `regular_price`   = :regular_price,
                `exclusive_price` = :exclusive_price,
                `date_start`      = :date_start,
                `date_end`        = :date_end,
                `sort_order`      = :sort_order');

            $statement->execute(array(
                ':product_id'      => $product_id,
                ':regular_price'   => $regular_price,
                ':exclusive_price' => $exclusive_price,
                ':date_start'      => $date_start,
                ':date_end'        => $date_end,
                ':sort_order'      => $sort_order
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product demo description
    *
    * @param int $product_demo_id
    * @param int $language_id
    * @param string $title
    * @return int|bool product_demo_id or FALSE/rollBack if throw exception
    */
    public function createProductDemoDescription($product_demo_id, $language_id, $title) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_demo_description` SET
                `product_demo_id` = :product_demo_id,
                `language_id`     = :language_id,
                `title`           = :title');

            $statement->execute(array(
                ':product_demo_id' => $product_demo_id,
                ':language_id'     => $language_id,
                ':title'           => $title
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Create product image description
    *
    * @param int $product_image_id
    * @param int $language_id
    * @param string $title
    * @return int|bool product_image_id or FALSE/rollBack if throw exception
    */
    public function createProductImageDescription($product_image_id, $language_id, $title) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_image_description` SET
                `product_image_id` = :product_image_id,
                `language_id`      = :language_id,
                `title`            = :title');

            $statement->execute(array(
                ':product_image_id' => $product_image_id,
                ':language_id'     => $language_id,
                ':title'           => $title
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Update product image description
    *
    * @param int $product_image_id
    * @param int $language_id
    * @param string $title
    * @return int|bool Count affected rows or FALSE/rollBack if throw exception
    */
    public function updateProductImageDescription($product_image_id, $language_id, $title) {

        try {
            $statement = $this->db->prepare(
                'UPDATE `product_image_description` SET
                `title` = :title
                 WHERE
                `product_image_id` = :product_image_id AND
                `language_id`      = :language_id
                 LIMIT 1');

            $statement->execute(array(
                ':product_image_id' => $product_image_id,
                ':language_id'      => $language_id,
                ':title'            => $title
            ));

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
    * Create product video description
    *
    * @param int $product_video_id
    * @param int $language_id
    * @param string $title
    * @return int|bool product_video_id or FALSE/rollBack if throw exception
    */
    public function createProductVideoDescription($product_video_id, $language_id, $title) {

        try {
            $statement = $this->db->prepare(
                'INSERT INTO `product_video_description` SET
                `product_video_id` = :product_video_id,
                `language_id`      = :language_id,
                `title`            = :title');

            $statement->execute(array(
                ':product_video_id' => $product_video_id,
                ':language_id'      => $language_id,
                ':title'            => $title
            ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Delete product demos
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductDemos($product_id) {

        try {

            $statement = $this->db->prepare('DELETE `pdd` FROM `product_demo_description` AS `pdd`
                                             JOIN `product_demo` AS `pd` ON (`pdd`.`product_demo_id` = `pd`.`product_demo_id`)
                                             WHERE `pd`.`product_id` = ?');

            $statement->execute(array($product_id));

            $affected = $statement->rowCount();

            $statement = $this->db->prepare('DELETE FROM `product_demo` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            $affected += $statement->rowCount();

            return $affected;

            $statement->execute(array($product_id));

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
    * Delete product favorites
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductFavorites($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_favorite` WHERE `product_id` = ?');

            $statement->execute(array($product_id));

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
    * Delete product reviews
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductReviews($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_review` WHERE `product_id` = ?');

            $statement->execute(array($product_id));

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
    * Delete product
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProduct($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product` WHERE `product_id` = ? LIMIT 1');

            $statement->execute(array($product_id));

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
    * Delete product image
    *
    * @param int $product_id
    * @return int|bool product_image_id or FALSE/rollBack if throw exception
    */
    public function deleteProductImages($product_id) {

        try {
            $statement = $this->db->prepare('DELETE `pid` FROM `product_image_description` AS `pid` JOIN `product_image` AS `pi` ON (`pi`.`product_image_id` = `pid`.`product_image_id`) WHERE `pi`.`product_id` = ?');
            $statement->execute(array($product_id));

            $affected = $statement->rowCount();

            $statement = $this->db->prepare('DELETE FROM `product_image` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            $affected += $statement->rowCount();

            return $affected;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Delete product specials
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductSpecials($product_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_special` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Delete product videos
    *
    * @param int $product_id
    * @return int|bool count affected rows or FALSE/rollBack if throw exception
    */
    public function deleteProductVideos($product_id) {

        try {
            $statement = $this->db->prepare('DELETE `pvd` FROM `product_video_description` AS `pvd`
                                             JOIN `product_video` AS `pv` ON (`pvd`.`product_video_id` = `pv`.`product_video_id`)
                                             WHERE `pv`.`product_id` = ?');

            $statement->execute(array($product_id));

            $affected = $statement->rowCount();

            $statement = $this->db->prepare('DELETE FROM `product_video` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            $affected += $statement->rowCount();

            return $affected;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }


    /**
    * Update product
    *
    * @param int $product_id
    * @param int $category_id
    * @param int $license_id
    * @param int $currency_id
    * @param float $regular_price
    * @param float $exclusive_price
    * @param string $withdraw_address
    * @param string $alias
    * @return int|bool affected rows or FALSE/rollBack if throw exception
    */
    public function updateProduct($product_id, $category_id, $license_id, $currency_id, $regular_price, $exclusive_price, $withdraw_address, $alias) {

        try {
            // Product
            $statement = $this->db->prepare(
                'UPDATE `product` SET

                `category_id`      = :category_id,
                `license_id`       = :license_id,
                `currency_id`      = :currency_id,
                `regular_price`    = :regular_price,
                `exclusive_price`  = :exclusive_price,
                `alias`            = CONCAT(`product_id`, "-", :alias),
                `withdraw_address` = :withdraw_address,
                `date_modified`    = NOW()

                WHERE

                `product_id`       = :product_id

                LIMIT 1');

            $statement->execute(array(
                ':product_id'       => $product_id,
                ':category_id'      => $category_id,
                ':license_id'       => $license_id,
                ':currency_id'      => $currency_id,
                ':regular_price'    => $regular_price,
                ':exclusive_price'  => $exclusive_price,
                ':withdraw_address' => $withdraw_address,
                ':alias'            => $alias
            ));

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
    * Delete product files
    *
    * @param int $product_id
    * @return int|bool affected rows or FALSE if throw exception
    */
    public function deleteProductFiles($product_id) {

        try {
            $statement = $this->db->prepare('DELETE `pfd` FROM `product_file_download` AS `pfd`
                                             JOIN `product_file` AS `pf` ON (`pf`.`product_file_id` = `pfd`.`product_file_id`)
                                             WHERE `pf`.`product_id` = ?');

            $statement->execute(array($product_id));

            $affected = $statement->rowCount();

            $statement = $this->db->prepare('DELETE FROM `product_file` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            $affected += $statement->rowCount();

            return $affected;

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Add single file
    *
    * @param int $product_id
    * @param string $hash_md5
    * @param string $hash_sha1
    * @return int|bool last insert id or FALSE if throw exception
    */
    public function createProductFile($product_id, $hash_md5, $hash_sha1) {
        try {
            $statement = $this->db->prepare('INSERT INTO `product_file` SET
            `product_id` = :product_id,
            `hash_md5`   = :hash_md5,
            `hash_sha1`  = :hash_sha1,
            `date_added` = NOW()');

            $statement->execute(array(
                ':product_id' => $product_id,
                ':hash_md5'   => $hash_md5,
                ':hash_sha1'  => $hash_sha1));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Get product file info
    *
    * @param int $product_id
    * @return object|bool product PDO::fetch() or FALSE if throw exception
    */
    public function getProductFileInfo($product_id) {
        try {
            $statement = $this->db->prepare('SELECT

            `pf`.*, `p`.`user_id`

            FROM `product_file` AS `pf`
            JOIN `product` AS `p` ON (`pf`.`product_id` = `p`.`product_id`)
            WHERE `pf`.`product_id` = ?
            LIMIT 1');
            $statement->execute(array($product_id));

            if ($statement->rowCount()) {
                return $statement->fetch();
            } else {
                return false;
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
    * Create product file download file by product id
    *
    * @param int $product_file_id
    * @param int $user_id
    * @return int|bool last product_file_download_id row or FALSE if throw exception
    */
    public function createProductFileDownload($product_file_id, $user_id) {
        try {
            $statement = $this->db->prepare('INSERT INTO `product_file_download` SET `product_file_id` = ?, `user_id` = ?, `date_added` = NOW()');
            $statement->execute(array($product_file_id, $user_id));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Remove tags relations by product_id
    *
    * @param int $product_id
    * @return int|bool removed rows count or FALSE if throw exception
    */
    public function deleteProductToTagByProductId($product_id) {

        try {

            $statement = $this->db->prepare('DELETE FROM `product_to_tag` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            return (int) $statement->rowCount();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Get tags by product id
    *
    * @param int $product_id
    * @return array|bool Array tags row or FALSE if throw exception
    */
    public function getTagsByProductId($product_id) {

        try {
            $statement = $this->db->prepare('SELECT `t`.* FROM `product_to_tag` AS `p2t` LEFT JOIN `tag` AS `t` ON (`p2t`.`tag_id` = `t`.`tag_id`) WHERE `p2t`.`product_id` = ?');
            $statement->execute(array($product_id));

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
    * Check if user has product
    *
    * @param int $user_id
    * @param int $product_id
    * @return bool TRUE if user has product or FALSE if others
    */
    public function userHasProduct($user_id, $product_id) {

        try {
            $statement = $this->db->prepare('SELECT NULL FROM `product` WHERE `user_id` = ? AND `product_id` = ? LIMIT 1');
            $statement->execute(array($user_id, $product_id));

            return (bool) $statement->rowCount();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Check if product has relations
    *
    * @param int $product_id
    * @param int $pending_status_id
    * @param int $processed_status_is
    * @param int $approved_status_id
    * @return bool TRUE if user has relations or FALSE if no
    */
    public function productHasRelations($product_id, $pending_status_id, $processed_status_is, $approved_status_id) {

        try {
            $statement = $this->db->prepare('SELECT
            NULL FROM `product_file_download` AS `pfd`
            RIGHT JOIN `product_file` AS `pf` ON (`pf`.`product_file_id` = `pfd`.`product_file_id`)
            JOIN `order` AS `o` ON (`o`.`product_id` = `o`.`product_id`)
            WHERE `o`.`product_id` = ?
            AND (`o`.`order_status_id` = ? OR `o`.`order_status_id` = ? OR (`o`.`order_status_id` = ? AND `o`.`date_added` > NOW() - INTERVAL 1 DAY)) LIMIT 1');

            $statement->execute(array($product_id, $processed_status_is, $approved_status_id, $pending_status_id));

            return (bool) $statement->rowCount();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }


    /**
    * Create product report
    *
    * @param int $product_id
    * @param string $message
    * @param int $user_id
    * @return int|bool product_report_id or FALSE/rollBack if throw exception
    */
    public function createReport($product_id, $message, $user_id) {

        try {
            $statement = $this->db->prepare('INSERT INTO `product_report` SET `product_id` = NULL, `user_id` = NULL, `message` = ?, `date_added` = NOW()');
            $statement->execute(array($message));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }


    /**
    * Add product hit
    *
    * @param int $product_id
    * @return int|bool count affected rows FALSE/rollBack if throw exception
    */
    public function addProductView($product_id) {

        try {
            $statement = $this->db->prepare('UPDATE `product` SET `viewed` = `viewed` + 1 WHERE `product_id` = ? LIMIT 1');
            $statement->execute(array($product_id));

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
    * Create product review
    *
    * @param string $review
    * @param int $product_id
    * @param int $user_id
    * @param int $language_id
    * @param int $status
    * @return int|bool product_review_id or FALSE/rollBack if throw exception
    */
    public function createProductReview($product_id, $review, $user_id, $language_id, $status) {

        try {
            $statement = $this->db->prepare('INSERT INTO `product_review` SET
            `product_id`    = :product_id,
            `user_id`       = :user_id,
            `language_id`   = :language_id,
            `review`        = :review,
            `date_added`    = NOW(),
            `date_modified` = NOW(),
            `status`        = :status');

            $statement->execute(array(
                ':product_id'  => $product_id,
                ':user_id'     => $user_id,
                ':language_id' => $language_id,
                ':review'      => $review,
                ':status'      => $status,
                ));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }


    /**
    * Create product favorite
    *
    * @param int $product_id
    * @param int $user_id
    * @return int|bool product_favorite_id or FALSE/rollBack if throw exception
    */
    public function createProductFavorite($product_id, $user_id) {

        try {
            $statement = $this->db->prepare('INSERT IGNORE INTO `product_favorite` SET `product_id` = ?, `user_id` = ?, `date_added` = NOW()');
            $statement->execute(array($product_id, $user_id));

            return $this->db->lastInsertId();

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }

    /**
    * Remove product favorite
    *
    * @param int $product_id
    * @param int $user_id
    * @return int|bool Count rows or FALSE/rollBack if throw exception
    */
    public function deleteProductFavorite($product_id, $user_id) {

        try {
            $statement = $this->db->prepare('DELETE FROM `product_favorite` WHERE `product_id` = ? AND `user_id` = ? LIMIT 1');
            $statement->execute(array($product_id, $user_id));

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
    * Reconfigure product - orders relations
    *
    * Set product_id AS NULL
    *
    * @param int $product_id
    * @return int|bool Count rows or FALSE/rollBack if throw exception
    */
    public function reconfigureProductToOrders($product_id) {

        try {
            $statement = $this->db->prepare('UPDATE `order` SET `product_id` = NULL WHERE `product_id` = ?');
            $statement->execute(array($product_id));

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
    * Get total product favorites
    *
    * @param int $product_id
    * @return int|bool Total rows or FALSE/rollBack if throw exception
    */
    public function getProductFavoritesTotal($product_id) {

        try {
            $statement = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `product_favorite` WHERE `product_id` = ?');
            $statement->execute(array($product_id));

            if ($statement->rowCount()) {
                $product_favorite = $statement->fetch();
                return $product_favorite->total;
            } else {
                return false;
            }

        } catch (PDOException $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            trigger_error($e->getMessage());

            return false;
        }
    }
}
