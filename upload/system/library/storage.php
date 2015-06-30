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

final class Storage {

    private $_db;

    public function __construct(Registry $registry) {
        $this->_db = $registry->get('db');
    }

    /**
    * Get product package file
    *
    * @param int $product_file_id
    * @param int $user_id
    * @param string $name
    */
    public function getProductFile($product_file_id, $user_id, $name) {

        $file = DIR_STORAGE . $user_id . DIR_SEPARATOR . $product_file_id . '.' . ALLOWED_FILE_EXTENSION;

        header('Content-Disposition: attachment; filename=' . $name . '.' . ALLOWED_FILE_EXTENSION);
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($file));

        echo readfile($file);
        exit;
    }

    /**
    * Get used disk space by user_id
    *
    * @param int $user_id
    * @param int $except_size Mb
    * @return int used space in Mb or false if throw exception
    */
    public function getUsedSpace($user_id, $except_size = 0) {

        $directory = DIR_STORAGE . $user_id . DIR_SEPARATOR;

        if (is_dir($directory)) {

            $count_size = 0;

            $dir_array = scandir($directory);
              foreach ($dir_array as $key => $filename) {

                // Images and temporary files will be ignored
                if ($filename != '..' && $filename != '.' && false === strpos($filename, '_') && false === strpos($filename, '.' . ALLOWED_IMAGE_EXTENSION)) {
                   if (is_dir($directory . DIR_SEPARATOR . $filename)){
                      $new_foldersize = foldersize($directory . DIR_SEPARATOR . $filename);
                      $count_size = $count_size + $new_foldersize;
                    } else if (is_file($directory . DIR_SEPARATOR . $filename)) {
                      $count_size = $count_size + filesize($directory . DIR_SEPARATOR . $filename);
                    }
               }
             }

            return $count_size / 1000000 - $except_size;
        } else {
            return 0;
        }
    }

    /**
    * Clean the storage
    *
    * @param int $user_id
    * @return bool
    */
    public function clean($user_id) {

        try {

            $registry = array();

            // Collect images
            $statement = $this->_db->prepare('SELECT * FROM `product_image` AS `pi` JOIN `product` AS `p` ON (`pi`.`product_id` = `p`.`product_id`) WHERE `p`.`user_id` = ?');
            $statement->execute(array($user_id));

            if ($statement->rowCount()) {

                foreach ($statement->fetchAll() as $image) {
                    $registry[] = $image->product_image_id . '.' . ALLOWED_IMAGE_EXTENSION;
                }
            }

            // Collect files
            $statement = $this->_db->prepare('SELECT * FROM `product_file` AS `pf` JOIN `product` AS `p` ON (`pf`.`product_id` = `p`.`product_id`) WHERE `p`.`user_id` = ?');
            $statement->execute(array($user_id));

            if ($statement->rowCount()) {

                foreach ($statement->fetchAll() as $file) {
                    $registry[] = $file->product_file_id . '.' . ALLOWED_FILE_EXTENSION;
                }
            }

            // Collect storage
            $storage = scandir(DIR_STORAGE . $user_id);
            foreach ($storage as $item) {
                if ($item != '.' && $item != '..' && !strpos($item, '_') && !in_array($item, $registry)) {
                    unlink(DIR_STORAGE . $user_id . DIR_SEPARATOR . $item);
                }
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
    * Get file size
    *
    * @param int $product_file_id
    * @param int $user_id
    * @param string $extension
    * @return int file size in Mb
    */
    public function getFileSize($product_file_id, $user_id, $extension) {
        return filesize(DIR_STORAGE . $user_id . DIR_SEPARATOR . $product_file_id . '.' . $extension) / 1000000;
    }
}
