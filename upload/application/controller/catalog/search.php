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

class ControllerCatalogSearch extends Controller {

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('account/user');
        $this->load->model('common/log');

        $this->load->helper('validator/product');
        $this->load->helper('plural');
    }

    public function index() {

        // Init variables
        $data = array();
        $breadcrumbs = array();
        $filter_data = array('order' => 'DESC');
        $title = tt('Products');
        $meta_title = '';


        $breadcrumbs[] = array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false);
        $breadcrumbs[] = array('name' => tt('Search'), 'href' => $this->url->link('catalog/search', ''), 'active' => true);

        // Filter by user
        if (isset($this->request->get['user_id']) && $user_info = $this->model_account_user->getUser((int) $this->request->get['user_id'])) {

            $title .= sprintf(' ' . tt('by %s'), $user_info->username);
            $meta_title .= sprintf(' ' . tt('by %s'), $user_info->username);
            $filter_data['user_id'] = (int) $this->request->get['user_id'];
        }

        // Filter by search term & tags
        if (isset($this->request->get['q']) && !empty($this->request->get['q']) && ValidatorProduct::titleValid($this->request->get['q'])) {

            $title .= sprintf(' ' . tt('containing %s'), ucfirst($this->request->get['q']));
            $meta_title .= sprintf(' ' . tt('Buy %s Thematic with Bitcoin | %s Thematic Store'), ucfirst($this->request->get['q']), ucfirst($this->request->get['q']));
            $filter_data['filter_query'] = $this->request->get['q'];
        }

        // Filter by favorites
        if (isset($this->request->get['favorites'])) {

            $title .= ' ' . tt('favorites');
            $meta_title .= $title;
            $filter_data['favorites'] = true;
        }

        // Filter by purchased
        if (isset($this->request->get['purchased'])) {

            $title .= ' ' . tt('purchased');
            $meta_title .= $title;
            $filter_data['purchased'] = true;
        }

        // Load products
        $data['products'] = array();

        $products_total = 0;
        foreach ($this->model_catalog_product->getProducts($filter_data, $this->language->getId(), $this->auth->getId(), ORDER_APPROVED_STATUS_ID) as $product_info) {

            $products_total++;

            // Prepare special counter
            if ($product_info->special_date_end) {

                $special_left_seconds = (strtotime($product_info->special_date_end) - time());
                $special_left_minutes = floor($special_left_seconds / 60);
                $special_left_hours   = floor($special_left_minutes / 60);
                $special_left_days    = floor($special_left_hours   / 24);

                if ($special_left_minutes < 60) {
                    $special_expires = sprintf(tt('%s %s left'), $special_left_minutes, plural($special_left_minutes, array(tt('minute'), tt('minutes'), tt('minutes'))));
                } else if ($special_left_hours < 24) {
                    $special_expires = sprintf(tt('%s %s left'), $special_left_hours, plural($special_left_hours, array(tt('hour'), tt('hours'), tt('hours'))));
                } else {
                    $special_expires = sprintf(tt('%s %s left'), $special_left_days, plural($special_left_days, array(tt('day'), tt('days'), tt('days'))));
                }

            } else {
                $special_expires = false;
            }

            switch ($product_info->order_status_id) {
                case ORDER_APPROVED_STATUS_ID:
                    $product_order_status = 'approved';
                    break;
                case ORDER_PROCESSED_STATUS_ID:
                    $product_order_status = 'processed';
                    break;
                default:
                    $product_order_status = $product_info->user_id == $this->auth->getId() ? 'approved' : false;
            }

            // Generate products
            $data['products'][] = array(

                'product_order_status'    => $product_order_status,
                'favorite'                => $product_info->favorite,
                'demo'                    => $product_info->main_product_demo_id ? true : false,

                'product_id'              => $product_info->product_id,
                'title'                   => $product_info->title,
                'favorites'               => $product_info->favorites ? $product_info->favorites : false,
                'status'                  => $product_info->status,

                'src'                     => $this->cache->image($product_info->main_product_image_id, $product_info->user_id, 144, 144),

                'href_view'               => $this->url->link('catalog/product', 'product_id=' . $product_info->product_id),
                'href_download'           => $this->url->link('catalog/product/download', 'product_id=' . $product_info->product_id),
                'href_demo'               => $this->url->link('catalog/product/demo', 'product_demo_id=' . $product_info->main_product_demo_id),


                'special_expires'         => $special_expires,
                'special_regular_price'   => $product_info->special_regular_price > 0 ? $this->currency->format($product_info->special_regular_price, $product_info->currency_id) : 0,
                'special_exclusive_price' => $product_info->special_exclusive_price > 0 ? $this->currency->format($product_info->special_exclusive_price, $product_info->currency_id) : 0,

                'regular_price'           => $this->currency->format($product_info->regular_price, $product_info->currency_id),
                'exclusive_price'         => $this->currency->format($product_info->exclusive_price, $product_info->currency_id),

                'has_regular_price'       => $product_info->regular_price > 0 ? true : false,
                'has_exclusive_price'     => $product_info->exclusive_price > 0 ? true : false,

                'has_special_regular_price'   => $product_info->special_regular_price > 0 ? true : false,
                'has_special_exclusive_price' => $product_info->special_exclusive_price > 0 ? true : false,

            );
        }

        // Log search request
        if (isset($this->request->get['q']) && !empty($this->request->get['q']) && ValidatorProduct::titleValid($this->request->get['q'])) {
            $this->model_common_log->createLogSearch($this->auth->getId(), $this->request->get['q'], $products_total);
        }

        // Load layout
        $this->document->setTitle($meta_title);
        $data['title']  = $title;

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', $breadcrumbs);
        $data['module_search']  = $this->load->controller('module/search');

        $data['user_is_logged'] = $this->auth->isLogged();

        // Renter the template
        $this->response->setOutput($this->load->view('catalog/list.tpl', $data));
    }
}
