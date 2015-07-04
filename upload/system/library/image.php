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

final class Image {

    private $_image;
    private $_width;
    private $_height;

    /**
    * Create
    *
    * @param string $image binary string or path to the image
    * @param bool $blob
    */
    public function __construct($image, $blob = false) {

        $this->_image = new Imagick();
        if ($blob) {
            $this->_image->readImageBlob($image);
        } else {
            $this->_image->readImage($image);
        }

        $this->_width  = $this->_image->getImageWidth();
        $this->_height = $this->_image->getImageHeight();
    }

    /**
    * Save image to the file
    *
    * @param string $filename
    * @return bool success
    */
    public function save($filename) {

        // Create directories by path if not exists
        $directories = explode(DIR_SEPARATOR, $filename);
        $path = '';
        foreach ($directories as $directory) {
            $path .= DIR_SEPARATOR . $directory;
            if (!is_dir($path) && false === strpos($directory, '.')) {
                mkdir($path, 0755);
            }
        }

        // Set image format
        $this->_image->setImageFormat(STORAGE_IMAGE_EXTENSION);

        // Write image to the disk
        return $this->_image->writeImage($filename);
    }


    /**
    * Get created image content
    *
    * @return string Image blob content
    */
    public function getContent() {
        return $this->_image->getImageBlob();
    }

    /**
    * Get created image height
    *
    * @return int Image width
    */
    public function getWidth() {
        return $this->_width;
    }

    /**
    * Get created image height
    *
    * @return int Image height
    */
    public function getHeight() {
        return $this->_height;
    }


    /**
    * Image resizing
    *
    * @param int $width X scale
    * @param int $height Y scale
    * @param int $blur 1 or 2 (1 by default)
    * @param bool $filter_type
    * @param bool $best_fit
    */
    public function resize($width, $height, $blur = 1, $filter_type = false, $best_fit = false) {

        // If the best fit mode is disabled
        if (!$best_fit) {

            // Crop to the square
            if ($this->_width > $this->_height) {
                $this->_image->cropImage($this->_height, $this->_height, ($this->_width - $this->_height) / 2, 0);
            } else if ($this->_width < $this->_height) {
                $this->_image->cropImage($this->_width, $this->_width, 0, ($this->_height - $this->_width) / 2);
            }
        }

        $this->_image->resizeImage($width, $height, $filter_type, $blur, $best_fit);

        $this->_width  = $width;
        $this->_height = $height;
    }


    /**
    * Create image watermark
    *
    * @param string $image watermark file
    */
    public function watermark($image) {

        $watermark = new Imagick($image);

        // Overlay
        for ($w = 0; $w < $this->_width; $w += $watermark->getImageWidth()) {
            for ($h = 0; $h < $this->_height; $h += $watermark->getImageHeight()) {
                $this->_image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $w, $h);
            }
        }
    }
}


