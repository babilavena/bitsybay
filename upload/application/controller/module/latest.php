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

class ControllerModuleLatest extends Controller {

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $this->load->helper('plural');
    }

    public function index($settings) {

        // Init variables
        $data = array();
        $filter_data = array();

        if (isset($settings['limit'])) {
            $filter_data = array('limit' => $settings['limit'], 'order' => 'DESC');
        }

        $product_data = $this->model_catalog_product->getProducts($filter_data, $this->language->getId(), $this->auth->getId(), ORDER_APPROVED_STATUS_ID);

        $data['products'] = array();
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

        $data['user_is_logged'] = $this->auth->isLogged();

        // Renter the template
        return $this->load->view('module/common/product_list.tpl', $data);
    }
}
