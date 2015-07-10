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

class ControllerOrderBitcoin extends Controller {

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('catalog/product');
        $this->load->model('common/order');
        $this->load->model('account/user');

        $this->load->library('bitcoin');
    }

    public function index() {
        $this->security_log->write('Try to get empty method');
        exit;

    }


    // AJAX actions begin
    public function create() {

        // Only for logged users
        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to order product from guest request');
            exit;
        }

        // Check request
        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to order product without ajax request');
            exit;
        }

        // Check dependencies
        if (!isset($this->request->post['product_id'])) {
            $this->security_log->write('Try to order product without product_id parameter');
            exit;
        }

        // Check dependencies
        if (!isset($this->request->post['license']) || !in_array($this->request->post['license'], array('regular', 'exclusive'))) {
            $this->security_log->write('Try to order product without license parameter');
            exit;
        }

        // Try to get product
        if (!$product_info = $this->model_catalog_product->getProduct((int) $this->request->post['product_id'], $this->auth->getId(), ORDER_APPROVED_STATUS_ID)) {
            $this->security_log->write('Try to order not exists product');
            exit;
        }

        // Try to get denied product
        if (!$product_info->status) {
            $this->security_log->write('Try to order product ' . (int)$this->request->post['product_id'] . ' with status ' . $product_info->status);
            exit;
        }

        // Check if product already ordered
        if ($product_info->order_status_id == ORDER_APPROVED_STATUS_ID) {
            $this->security_log->write('Try to order ordered product');
            exit;
        }

        // Check if order self product
        if ($product_info->user_id == $this->auth->getId()) {
            $this->security_log->write('Try to order self product');
            exit;
        }

        // Check regular price
        if ($this->request->post['license'] == 'regular' && ($product_info->regular_price > 0 || $product_info->special_regular_price > 0)) {
            $amount = (float) $product_info->special_regular_price > 0 ? $product_info->special_regular_price : $product_info->regular_price;

        // Check exclusive price
        } else if ($this->request->post['license'] == 'exclusive' && ($product_info->exclusive_price > 0 || $product_info->special_exclusive_price > 0)) {
            $amount = (float) $product_info->special_exclusive_price > 0 ? $product_info->special_exclusive_price : $product_info->exclusive_price;

        // License parameter error
        } else {
            $this->security_log->write('Try to purchase product by undefined license');
            exit;
        }

        // Init variables
        $json = array('status' => false);

        // Create a new order in DB
        if (!$order_id = $this->model_common_order->createOrder($this->auth->getId(),
                                                                $product_info->product_id,
                                                                $product_info->license_id,
                                                                $this->request->post['license'],
                                                                $amount,
                                                                FEE_PER_ORDER,
                                                                ORDER_PENDING_STATUS_ID,
                                                                DEFAULT_CURRENCY_ID)) {

            $this->security_log->write('Can not create the order');
            exit;
        }

        // Create a new BitCoin Address
        try {
            $bitcoin = new BitCoin(BITCOIN_RPC_USERNAME,
                                   BITCOIN_RPC_PASSWORD,
                                   BITCOIN_RPC_HOST,
                                   BITCOIN_RPC_PORT);

            // Set response
            if (false !== $bitcoin->status && $address = $bitcoin->getaccountaddress(BITCOIN_ORDER_PREFIX . $order_id)) {

                $json = array(
                    'status'  => true,
                    'address' => $address,
                    'text'    => sprintf(tt('Send exactly %s to this address:'), $this->currency->format($amount)),
                    'href'    => 'bitcoin:' . $address . '?amount=' . $amount . '&label=' . PROJECT_NAME . ' Order #' . $order_id,
                    'src'     => $this->url->link('common/image/qr', 'code=' . $address));
            }


        } catch (Exception $e) {
            $this->security_log->write($bitcoin->error . '/' . $e->getMessage());
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
