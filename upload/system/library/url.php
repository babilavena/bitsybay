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

final class Url {

    /**
     * @var resource
     */
    private $_db;

    /**
     * @var resource
     */
    private $_request;

    /**
     * @var resource
     */
    private $_response;

    /**
     * @var string
     */
    private $_base;

    /**
     * @var array
     */
    private $_rewrite = array();

    /**
    * Construct
    *
    * @param $db
    * @param $request
    * @param $response
    * @param string $base
    */
    public function __construct($db, $request, $response, $base) {

        $this->_base     = $base;

        $this->_db       = $db;
        $this->_request  = $request;
        $this->_response = $response;

        // Create account rewrite rules
        $this->_addRewrite('account/account', 'profile');
        $this->_addRewrite('account/account/approve', 'approve');
        $this->_addRewrite('account/account/create', 'signup');
        $this->_addRewrite('account/account/update', 'settings');
        $this->_addRewrite('account/account/login', 'signin');
        $this->_addRewrite('account/account/logout', 'logout');
        $this->_addRewrite('account/account/forgot', 'forgot');
        $this->_addRewrite('account/account/reset', 'reset');
        $this->_addRewrite('account/account/verification', 'verification');
        $this->_addRewrite('account/account/subscription', 'subscriptions');

        $this->_addRewrite('account/product', 'product/list');
        $this->_addRewrite('account/product/create', 'product/create');
        $this->_addRewrite('account/product/update', 'product/update');
        $this->_addRewrite('account/product/delete', 'product/delete');

        $this->_addRewrite('account/notification', 'notifications');
        $this->_addRewrite('account/notification/read', 'notification/read');

        // Create catalog rewrite rules
        $this->_addRewrite('catalog/category', '');
        $this->_addRewrite('catalog/product', '');
        $this->_addRewrite('catalog/search', 'search');
        $this->_addRewrite('catalog/product/download', 'product/download');
        $this->_addRewrite('catalog/product/demo', 'product/demo');

        // Create common rewrite rules
        $this->_addRewrite('common/home', '');
        $this->_addRewrite('common/contact', 'contact');

        $this->_addRewrite('common/information/about', 'about');
        $this->_addRewrite('common/information/terms', 'terms');
        $this->_addRewrite('common/information/licenses', 'licenses');
        $this->_addRewrite('common/information/faq', 'faq');
        $this->_addRewrite('common/information/team', 'team');
        $this->_addRewrite('common/image/qr', 'qr');

        // Create error rewrite rules
        $this->_addRewrite('error/not_found',  '404');

        // Create categories rewrite rules
        $statement = $this->_db->query('SELECT
        `c`.`category_id`,
         CONCAT_WS("/", (SELECT `pc`.`alias` FROM `category` AS `pc` WHERE `pc`.`category_id` = `c`.`parent_category_id`), `c`.`alias`) AS `sef`
         FROM `category` AS `c`');

        if ($statement->rowCount()) {
            foreach ($statement->fetchAll() as $category) {

                // Add rewrite rule
                $this->_addRewrite('category_id=' . $category->category_id, $category->sef);
            }
        }

        // Create products rewrite rules
        $statement = $this->_db->query('SELECT
        `p`.`product_id`,
        CONCAT_WS("/",
            (SELECT `ppc`.`alias` FROM `category` AS `ppc` WHERE `ppc`.`category_id` = `c`.`parent_category_id`),
            (SELECT `pc`.`alias` FROM `category` AS `pc` WHERE `pc`.`category_id` = `p`.`category_id`),
            `p`.`alias`) AS `sef`
            FROM `product` AS `p` JOIN `category` AS `c` ON (`c`.`category_id` = `p`.`category_id`)');

        if ($statement->rowCount()) {
            foreach ($statement->fetchAll() as $product) {

                // Add rewrite rule
                $this->_addRewrite('product_id=' . $product->product_id, $product->sef);
            }
        }

        // Rewrite begin
        if (isset($this->_request->get['_route_'])) {

            $rewrite = array_flip($this->_rewrite);

            // If has rewrite rule
            if (isset($rewrite[$this->_request->get['_route_']])) {

                // Category
                if (false !== strpos($rewrite[$this->_request->get['_route_']], 'category_id')) {

                    $argument = explode('=', $rewrite[$this->_request->get['_route_']]);

                    $this->_request->get['route'] = 'catalog/category';
                    $this->_request->get[$argument[0]] = $argument[1];

                // Product
                } else if (false !== strpos($rewrite[$this->_request->get['_route_']], 'product_id')) {

                    $argument = explode('=', $rewrite[$this->_request->get['_route_']]);

                    $this->_request->get['route'] = 'catalog/product';
                    $this->_request->get[$argument[0]] = $argument[1];

                // Other
                } else {
                    $this->_request->get['route'] = $rewrite[$this->_request->get['_route_']];
                }

            // If rewrite rule not found
            } else {

                // Try to 301 redirect if request URI exists in database history
                $statement = $this->_db->prepare('SELECT `redirect_id`, `uri_to` FROM `redirect` WHERE `code` = 301 AND `uri_from` LIKE ?');
                $statement->execute(array($this->_request->get['_route_']));

                if ($statement->rowCount()) {
                    foreach ($statement->fetchAll() as $redirect) {

                        // Find available URI
                        if (isset($rewrite[$redirect->uri_to])) {

                            // Register hit
                            $statement = $this->_db->prepare('UPDATE `redirect` SET `requested` = `requested` + 1 WHERE `redirect_id` = ? LIMIT 1');
                            $statement->execute(array($redirect->redirect_id));

                            // Redirect
                            $this->_response->redirect($this->_base . $redirect->uri_to, 301);
                        }
                    }
                }

                $this->_request->get['route'] = 'error/not_found';
            }

        // If raw request
        } else if (isset($this->_request->get['route'])) {

            // Allow AJAX raw requests
            if (!$this->_request->isAjax()) {

                // Check if rewrite rule is exists
                $raw = $this->_base . 'index.php?' . urldecode(http_build_query($this->_request->get));
                $sef = $this->link($this->_request->get['route'], urldecode(http_build_query(array_diff_key($this->_request->get, array_flip(array('route'))))));

                if (rawurldecode($raw) != rawurldecode($sef)) {
                    $this->_response->redirect($sef, 303);
                }
            }
        }
    }

    /**
    * Add rewrite rule
    *
    * @param string $key e.g. common/information/about
    * @param string $value e.g. about
    */
    private function _addRewrite($key, $value) {
        $this->_rewrite[$key] = $value;
    }

    /**
    * Canonical links creation
    *
    * @param string $route Path to controller. For example: account/account/update
    * @param string $arguments Ampersand separated
    * @param bool $secure TRUE for SSL or FALSE by default
    * @return string Returns canonical link
    */
    public function link($route, $arguments = '') {

        // Secure layer
        $url = $this->_base;

        // Route
        $url .= isset($this->_rewrite[$route]) ? $this->_rewrite[$route] : 'index.php?route=' . $route;

        // Arguments
        if ($arguments) {

            $skip_rewrite = in_array($route, array('account/product/update',
                'account/product/delete',
                'catalog/product/download')) ? true : false;

            $arguments = explode('&', $arguments);

            $i = 0;
            foreach ($arguments as $argument) {

                if (isset($this->_rewrite[$argument]) && !$skip_rewrite) {
                    $url .= $this->_rewrite[$argument];
                } else {
                    $url .= ($i ? '&' : '?') . $argument;
                    $i++;
                }
            }
        }

        return $url;
    }

    /**
    * Get Current Link
    *
    * @return string Returns current page canonical link
    */
    public function getCurrentLink() {

        $route     = 'common/home';
        $arguments = array();

        if (isset($this->_request->get) && $this->_request->get) {
            foreach ($this->_request->get as $key => $value) {
                if ($key != '_route_') {
                    if ($key == 'route') {
                        $route = $value;
                    } else {
                        $arguments[] = $key . '=' . $value;
                    }
                }
            }
        }

        return $this->link($route, implode('&', $arguments));
    }

}
