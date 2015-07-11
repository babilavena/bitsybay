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

class ControllerCatalogCategory extends Controller {

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $this->load->helper('plural');
    }

    public function index() {

        // Init variables
        $data = array();
        $breadcrumbs = array();
        $category_info = array();

        // Request validator
        if (!isset($this->request->get['category_id']) || !$category_info = $this->model_catalog_category->getCategory((int) $this->request->get['category_id'], $this->language->getId())) {
            $this->response->redirect($this->url->link('common/home'));
        }

        $categories = array($category_info->title);

        // Breadcrumbs
        $parent_category_id = $category_info->parent_category_id;
        do {
            $parent_category_info = $this->model_catalog_category->getCategory($parent_category_id, $this->language->getId());

            if ($parent_category_info) {
                $parent_category_id   = $parent_category_info->parent_category_id;

                $breadcrumbs[] = array(
                    'name' => $parent_category_info->title,
                    'href' => $this->url->link('catalog/category', 'category_id=' . $parent_category_info->category_id),
                    'active' => false);

                $categories[] = $parent_category_info->title;
            }

        } while ($parent_category_id);

        $breadcrumbs[] = array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false);
        array_multisort($breadcrumbs, SORT_ASC, SORT_NUMERIC);
        $breadcrumbs[] = array('name' => $category_info->title, 'href' => $this->url->link('catalog/category', 'category_id=' . $category_info->category_id), 'active' => true);

        // Load products
        $data['products'] = array();

        // Load child products if category is parent
        if (!$category_info->parent_category_id && $child_categories = $this->model_catalog_category->getCategories($category_info->category_id, $this->language->getId())) {

            $category_ids = array();
            foreach ($child_categories as $child_category) {
                $category_ids[] = $child_category->category_id;
            }

            $product_data = $this->model_catalog_product->getProducts(array('category_ids' => $category_ids, 'order' => 'DESC'), $this->language->getId(), $this->auth->getId(), ORDER_APPROVED_STATUS_ID);
        } else {
            $product_data = $this->model_catalog_product->getProducts(array('category_id' => $category_info->category_id, 'order' => 'DESC'), $this->language->getId(), $this->auth->getId(), ORDER_APPROVED_STATUS_ID);
        }


        foreach ($product_data as $product_info) {

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
                'status'                  => $product_info->status,
                'favorites'               => $product_info->favorites ? $product_info->favorites : false,

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


        // Create SEO title
        $this->document->setTitle(sprintf(tt('Buy %s with BitCoin | Royalty Free %s Store'), implode(' ', $categories), $categories[0]));

        // Load layout
        $data['title']  = $category_info->title;

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', $breadcrumbs);

        $data['user_is_logged'] = $this->auth->isLogged();

        // Renter the template
        $this->response->setOutput($this->load->view('catalog/list.tpl', $data));
    }
}
