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

class ValidatorUpload {

    /**
    * Validate file
    *
    * @param array $file
    * @param int $max_file_size MB
    * @param string $STORAGE_FILE_EXTENSION
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function fileValid($file, $max_file_size, $STORAGE_FILE_EXTENSION) {

        // Dependencies test
        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            return false;

        } else if (empty($file['tmp_name']) || empty($file['name'])) {
            return false;
        } else {

            // Common test
            if (mb_strtolower($STORAGE_FILE_EXTENSION) != @pathinfo($file['name'], PATHINFO_EXTENSION)) {
                return false;

            } else if ($max_file_size < @filesize($file['tmp_name']) / 1000000) {
                return false;
            }

            // Extension test
            if (mb_strtolower($STORAGE_FILE_EXTENSION) == 'zip') {
                $zip = new ZipArchive();
                if (true !== $zip->open($file['tmp_name'], ZipArchive::CHECKCONS)) {

                    $zip->close();
                    return false;
                }

                $zip->close();
            }

        }

        return true;
    }

    /**
    * Validate image
    *
    * JPEG only
    *
    * @param array $image
    * @param int $max_file_size MB
    * @param int $min_width PX
    * @param int $min_height PX
    * @param array $STORAGE_FILE_EXTENSION
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function imageValid($image, $max_file_size, $min_width, $min_height, array $STORAGE_FILE_EXTENSION = array('jpg', 'jpeg', 'png')) {

        // File validation
        $file_validation = false;
        $file_extension  = false;

        foreach ($STORAGE_FILE_EXTENSION as $extension) {
            if (self::fileValid($image, $max_file_size, $extension)) {
                $file_validation = true;
                $file_extension  = $extension;
                break;
            }
        }

        if (!$file_validation) {
            return false;
        }

        // Allowed image extension check
        if (!in_array(@pathinfo($image['name'], PATHINFO_EXTENSION), $STORAGE_FILE_EXTENSION)) {
            return false;
        }

        // Image size test
        if (!$image_size = @getimagesize($image['tmp_name'])) {
            return false;
        }

        // Size limits
        if (!isset($image_size[0]) || !isset($image_size[1]) ||
             empty($image_size[0]) || empty($image_size[1]) ||
             $image_size[0] < $min_width || $image_size[1] < $min_height) {

            return false;
        }

        // Image creation test
        switch ($file_extension) {
            case 'jpg':
            case 'jpeg':
                if (!$image_copy = @imagecreatefromjpeg($image['tmp_name'])) {
                    imagedestroy($image_copy);
                    return false;
                }
            break;
            case 'png':
                if (!$image_copy = @imagecreatefrompng($image['tmp_name'])) {
                    imagedestroy($image_copy);
                    return false;
                }
            break;
        }

        return true;
    }
}
