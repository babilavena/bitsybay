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

final class Response {

    /**
     * @var array
     */
    private $_headers = array();

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @var string
     */
    private $_output;


    /**
    * Add header
    *
    * @param string $header
    * @return null
    */
    public function addHeader($header) {
        $this->_headers[] = $header;
    }

    /**
    * Force redirect to the target page
    *
    * @param string $url
    * @param int $status
    * @return null
    */
    public function redirect($url, $status = 302) {
        header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url), true, $status);
        exit();
    }

    /**
    * Set Compression
    *
    * @param int $level
    * @return null
    */
    public function setCompression($level) {
        $this->_level = $level;
    }

    /**
    * Set Output
    *
    * @param string $output
    * @return null
    */
    public function setOutput($output) {
        $this->_output = $output;
    }

    /**
    * Get Output
    *
    * @return string
    */
    public function getOutput() {
        return $this->_output;
    }


    /**
    * Compress data
    *
    * @param string $data
    * @param int $level
    * @return string Compressed data
    */
    private function _compress($data, $level = 0) {
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
            $encoding = 'gzip';
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
            $encoding = 'x-gzip';
        }

        if (!isset($encoding) || ($level < -1 || $level > 9)) {
            return $data;
        }

        if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
            return $data;
        }

        if (headers_sent()) {
            return $data;
        }

        if (connection_status()) {
            return $data;
        }

        $this->addHeader('Content-Encoding: ' . $encoding);

        return gzencode($data, (int)$level);
    }

    /**
    * Output data
    *
     * @return string Compressed data
    */
    public function output() {
        if ($this->_output) {
            if ($this->_level) {
                $output = $this->_compress($this->_output, $this->_level);
            } else {
                $output = $this->_output;
            }

            if (!headers_sent()) {
                foreach ($this->_headers as $header) {
                    header($header, true);
                }
            }

            echo $output;
        }
    }
}
