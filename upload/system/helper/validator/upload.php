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
    * @param string $allowed_file_extension
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function fileValid($file, $max_file_size, $allowed_file_extension) {

        // Dependencies test
        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            return false;

        } else if (empty($file['tmp_name']) || empty($file['name'])) {
            return false;
        } else {

            // Common test
            if (mb_strtolower($allowed_file_extension) != @pathinfo(self::_extensionPrepare($file['name']), PATHINFO_EXTENSION)) {
                return false;

            } else if ($max_file_size < @filesize($file['tmp_name']) / 1000000) {
                return false;
            }

            // Extension test
            if (mb_strtolower($allowed_file_extension) == 'zip') {
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
    * @param string $allowed_file_extension
    * @return bool TRUE if valid ot FALSE if else
    */
    static public function imageValid($image, $max_file_size, $min_width, $min_height, $allowed_file_extension) {

        if (!self::fileValid($image, $max_file_size, $allowed_file_extension)) {
            return false;
        }

        // Allowed image extension check
        if (mb_strtolower($allowed_file_extension) != @pathinfo(self::_extensionPrepare($image['name']), PATHINFO_EXTENSION)) {
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
        if (!$image_copy = @imagecreatefromjpeg($image['tmp_name'])) {
            return false;
        }

        imagedestroy($image_copy);

        return true;
    }

    /**
    * Prepare file extension for validation
    *
    * @param string $filename
    * @return string filename
    */
    static private function _extensionPrepare($filename) {
        return str_replace(array('jpeg'), array('jpg'), mb_strtolower(trim($filename)));
    }
}
