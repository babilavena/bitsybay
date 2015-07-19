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

class ControllerCommonHeader extends Controller {

    public function index() {

        // Add Styles
        $this->document->addStyle('/bootstrap/3.3.2/css/bootstrap.min.css');
        $this->document->addStyle('/stylesheet/common.css');
        $this->document->addStyle('/stylesheet/datepicker.css');

        // Add Scripts
        $this->document->addScript('/javascript/jquery-2.1.3.min.js');
        $this->document->addScript('/bootstrap/3.3.2/js/bootstrap.min.js');
        $this->document->addScript('/javascript/bootstrap-datepicker.js');
        $this->document->addScript('/javascript/common.js');

        $data['title']       = $this->document->getTitle();
        $data['description'] = $this->document->getDescription();
        $data['keywords']    = $this->document->getKeywords();
        $data['links']       = $this->document->getLinks();
        $data['styles']      = $this->document->getStyles();
        $data['scripts']     = $this->document->getScripts();

        $data['lang']        = $this->language->getCode();
        $data['icon']        = '';
        $data['logo']        = '';
        $data['base']        = URL_BASE;

        $data['bool_is_logged']   = $this->auth->isLogged();
        $data['bool_is_verified'] = !$this->auth->isVerified();
        $data['username']         = $this->auth->getUsername();

        // Notification section
        $this->load->model('account/notification');
        $data['total_account_notification'] = $this->model_account_notification->checkNewNotifications($this->auth->getId());

        $data['href_account_notification'] = $this->url->link('account/notification');

        // Account section
        $data['href_account_account_update'] = $this->url->link('account/account/update');
        $data['href_account_account'] = $this->url->link('account/account');
        $data['href_account_account_verification'] = $this->url->link('account/account/verification');
        $data['href_account_account_subscription'] = $this->url->link('account/account/subscription');
        $data['href_account_account_affiliate'] = $this->url->link('account/account/affiliate');

        // Buyer section
        $data['href_product_purchased'] = $this->url->link('catalog/search', 'purchased=1');
        $data['href_product_favorites'] = $this->url->link('catalog/search', 'favorites=1');

        // Seller section
        $data['href_account_product_list']  = $this->url->link('account/product');
        $data['href_account_product_sales']  = $this->url->link('account/sales');

        $redirect = base64_encode($this->url->getCurrentLink());

        $data['href_account_create']  = $this->url->link('account/account/create', 'redirect=' . $redirect);
        $data['href_account_login']   = $this->url->link('account/account/login',  'redirect=' . $redirect);
        $data['href_account_logout']  = $this->url->link('account/account/logout', 'redirect=' . $redirect);

        // Generate categories menu
        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories(null, $this->language->getId());

        $data['categories'] = array();

        foreach ($categories as $category) {

            // Get child categories
            $child = array();

            $child_categories = $this->model_catalog_category->getCategories($category->category_id, $this->language->getId(), true);

            foreach ($child_categories as $child_category) {
                if ($child_category->total_products) {
                    $child[] = array(
                        'category_id' => $child_category->category_id,
                        'title'       => $child_category->title,
                        'href'        => $this->url->link('catalog/category', 'category_id=' . $child_category->category_id),
                        'child'       => array());
                }
            }

            // Get parent categories
            if ($child) {
                $data['categories'][] = array(
                    'category_id' => $category->category_id,
                    'title'       => $category->title,
                    'href'        => $this->url->link('catalog/category', 'category_id=' . $category->category_id),
                    'child'       => $child);
            }

        }

        return $this->load->view('common/header.tpl', $data);
    }
}
