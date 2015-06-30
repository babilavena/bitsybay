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

final class Request {

    /**
     * @var array
     */
    public $get = array();

    /**
     * @var array
     */
    public $post = array();

    /**
     * @var array
     */
    public $files = array();

    /**
     * @var string
     */
    private $_remote_address   = 'undefined';

    /**
     * @var string
     */
    private $_request_method   = 'undefined';

    /**
     * @var string
     */
    private $_request_referrer = 'undefined';

    /**
     * @var string
     */
    private $_request_string   = 'undefined';

    /**
     * @var string
     */
    private $_server_protocol  = 'undefined';

    /**
     * @var string
     */
    private $_user_agent  = 'undefined';

    /**
     * @var bool
     */
    private $_https =  false;

    /**
     * @var bool
     */
    private $_is_ajax =  false;


    public function __construct() {

        if (isset($_GET)) {
            $this->get = $this->_filter($_GET);
        }

        if (isset($_POST)) {
            $this->post = $this->_filter($_POST);
        }

        if (isset($_FILES)) {
            $this->files = $this->_filter($_FILES);
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->_setRemoteAddress((string) $_SERVER['REMOTE_ADDR']);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->_setRequestMethod((string) $_SERVER['REQUEST_METHOD']);
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->_setRequestReferrer((string) $_SERVER['HTTP_REFERER']);
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->_setUserAgent((string) $_SERVER['HTTP_USER_AGENT']);
        }

        if (isset($_SERVER['QUERY_STRING'])) {
            $this->_setRequestString((string) $_SERVER['QUERY_STRING']);
        }

        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $this->_setServerProtocol((string) $_SERVER['SERVER_PROTOCOL']);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $this->_is_ajax = true;
        }

        if (isset($_SERVER['HTTPS'])) {
            $this->_setHttps((bool) $_SERVER['HTTPS']);
        }

        unset($_GET);
        unset($_POST);
        unset($_FILES);
        unset($_SERVER);
    }

    /**
    * Data filterer
    *
    * Clean incoming data for the specials chars, XSS and SQL injections
    *
    * @param array|string $data Raw string
    * @return array|string Cleaned string
    */
    private function _filter($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);

                $data[$this->_filter($key)] = $this->_filter($value);
            }
        } else {
            $data = htmlentities(trim(strip_tags(html_entity_decode($data))), ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
    * Set server remote address
    *
    * @param string $remote_address
    * @return null
    */
    private function _setRemoteAddress($remote_address = 'undefined') {
        $this->_remote_address = $this->_filter($remote_address);
    }

    /**
    * Set server request method
    *
    * @param string $request_method
    * @return null
    */
    private function _setRequestMethod($request_method = 'undefined') {
        $this->_request_method = $this->_filter($request_method);
    }

    /**
    * Set server request referrer
    *
    * @param string $request_referrer
    * @return null
    */
    private function _setRequestReferrer($request_referrer = 'undefined') {
        $this->_request_referrer = $this->_filter($request_referrer);
    }

    /**
    * Set server user agent
    *
    * @param string $user_agent
    * @return null
    */
    private function _setUserAgent($user_agent = 'undefined') {
        $this->_user_agent = $this->_filter($user_agent);
    }

    /**
    * Set server request string
    *
    * @param string $request_string
    * @return null
    */
    private function _setRequestString($request_string = 'undefined') {
        $this->_request_string = $this->_filter($request_string);
    }

    /**
    * Set server protocol
    *
    * @param string $server_protocol
    * @return null
    */
    private function _setServerProtocol($server_protocol = 'undefined') {
        $this->_server_protocol = $this->_filter($server_protocol);
    }

    /**
    * Set server HTTPS mode
    *
    * @param bool $https
    * @return null
    */
    private function _setHttps($https = false) {
        $this->_https = (bool) $https;
    }

    /**
    * Get server remote address
    *
    * @return string Remote IP
    */
    public function getRemoteAddress() {
        return $this->_remote_address;
    }

    /**
    * Get server request method
    *
    * @return string Request method
    */
    public function getRequestMethod() {
        return $this->_request_method;
    }

    /**
    * Get server request referrer
    *
    * @return string Request referrer
    */
    public function getRequestReferrer() {
        return str_replace('&amp;', '&', $this->_request_referrer);
    }

    /**
    * Get server user agent
    *
    * @return string
    */
    public function getUserAgent() {
        return $this->_user_agent;
    }

    /**
    * Get server request string
    *
    * @return string Request string
    */
    public function getRequestString() {
        return str_replace('&amp;', '&', $this->_request_string);
    }

    /**
    * Get server protocol
    *
    * @return string Server protocol
    */
    public function getServerProtocol() {
        return $this->_server_protocol;
    }

    /**
    * Get server HTTPS mode
    *
    * @return bool HTTPS mode
    */
    public function getHttps() {
        return $this->_https;
    }

    /**
    * Check ajax request
    *
    * @return bool TRUE if AJAX request or FALSE if else
    */
    public function isAjax() {
        return $this->_is_ajax;
    }
}
