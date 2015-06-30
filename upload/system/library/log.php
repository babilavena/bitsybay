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

final class Log {

    /**
     * @var resource
     */
    private $_handle;

    /**
     * @var string
     */
    private $_message_postfix = '';

    /**
    * Construct and open the logfile
    *
    * @param int $user_id
    * @param string $remote_address
    * @param string $filename
    */
    public function __construct($filename, $user_id = 0, $remote_address = '') {

        if ($user_id) {
            $this->_message_postfix .= sprintf(' by user_id #%s ', $user_id);
        }

        if ($remote_address) {
            $this->_message_postfix .= sprintf(' ip: [%s] ', $remote_address);
        }

        $this->_handle = fopen(DIR_BASE . 'system' . DIR_SEPARATOR . 'log' . DIR_SEPARATOR . $filename, 'a');
    }

    /**
    * Write message to the logfile
    *
    * @param string $message
    */
    public function write($message) {
        fwrite($this->_handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . $this->_message_postfix . "\n");
    }
    /**
    * Destruct process
    */
    public function __destruct() {
        fclose($this->_handle);
    }
}
