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

class ControllerCatalogProduct extends Controller {


    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('common/order');
        $this->load->model('account/notification');
        $this->load->model('account/subscription');
        $this->load->model('account/user');

        $this->load->helper('validator/product');
        $this->load->helper('plural');
        $this->load->helper('search_engines');

        $this->load->library('bitcoin');
    }

    // Common actions
    public function index() {

        // Init variables
        $data = array();
        $breadcrumbs = array();
        $product_info = array();
        $categories = array();


        // Check dependencies
        if (!isset($this->request->get['product_id']) || !$product_info = $this->model_catalog_product->getProduct((int) $this->request->get['product_id'], $this->auth->getId(), ORDER_APPROVED_STATUS_ID)) {
            $this->security_log->write('Try to get product file without product_id parameter' . isset($this->request->get['product_id']) ? (int) $this->request->get['product_id'] : false);
            $this->response->redirect($this->url->link('error/not_found'));
        }

        // Check product status
        switch ($product_info->status) {
            case 'blocked':
                $this->response->redirect($this->url->link('error/not_found'));
                break;
            case 'disabled':
                $this->response->redirect($this->url->link('error/not_found'));
                break;
            default:
        }

        // Add product hit exclude owner's and search bot hits
        if (!$this->auth->isLogged() && !SearchEngines::isBot($this->request->getUserAgent()) || ($this->auth->isLogged() && $product_info->user_id != $this->auth->getId())) {
            $this->model_catalog_product->addProductView($product_info->product_id);
        }

        // Misc
        $data['color_labels'] = array(
            'label-default','label-primary','label-success','label-warning','label-danger','label-info',
            'label-default','label-primary','label-success','label-warning','label-danger','label-info',
            'label-default','label-primary','label-success','label-warning','label-danger','label-info');


        // Breadcrumbs
        $parent_category_id = $product_info->category_id;

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
        $breadcrumbs[] = array('name' => $product_info->title, 'href' => $this->url->link('catalog/product', 'product_id=' . $product_info->product_id), 'active' => true);


        // Product data
        $data['product_id']        = $product_info->product_id;
        $data['product_title']     = $product_info->title;
        $data['product_username']  = $product_info->username;
        $data['product_sales']     = $product_info->sales;
        $data['product_status']    = $product_info->status;
        $data['product_is_self']   = $product_info->user_id == $this->auth->getId() ? true : false;
        $data['user_is_logged']    = $this->auth->isLogged() ? true : false;
        $data['verified']          = $product_info->verified;

        $image_info = $this->model_catalog_product->getProductImageInfo($product_info->main_product_image_id);

        $data['product_image_url']      = $this->cache->image($product_info->main_product_image_id, $product_info->user_id, 350, 1000, $image_info->watermark, false, true);
        $data['product_image_orig_url'] = $this->cache->image($product_info->main_product_image_id, $product_info->user_id, 570, 1000, $image_info->watermark, false, true);
        $data['product_image_id']       = $product_info->main_product_image_id;

        switch ($product_info->order_status_id) {
            case ORDER_APPROVED_STATUS_ID:
                $data['product_order_status'] = 'approved';
                break;
            case ORDER_PROCESSED_STATUS_ID:
                $data['product_order_status'] = 'processed';
                break;
            default:
                $data['product_order_status'] = $product_info->user_id == $this->auth->getId() ? 'approved' : false;
        }

        $data['product_demo']          = $product_info->main_product_demo_id ? true : false;
        $data['product_favorites']     = $product_info->favorites ? $product_info->favorites : false;
        $data['product_favorite']      = $product_info->favorite;
        $data['product_description']   = nl2br($product_info->description);

        $data['product_href_view']     = $this->url->link('catalog/product', 'product_id=' . $product_info->product_id);
        $data['product_href_download'] = $this->url->link('catalog/product/download', 'product_id=' . $product_info->product_id);
        $data['product_href_demo']     = $this->url->link('catalog/product/demo', 'product_demo_id=' . $product_info->main_product_demo_id);
        $data['product_href_user']     = $this->url->link('catalog/search', 'user_id=' . $product_info->user_id);

        $data['product_date_added']    = date(DATE_FORMAT_DEFAULT, strtotime($product_info->date_added));
        $data['product_date_modified'] = date(DATE_FORMAT_DEFAULT, strtotime($product_info->date_modified));

        $data['product_demos'] = array();
        foreach ($this->model_catalog_product->getProductDemos($product_info->product_id, $this->language->getId()) as $demo) {
            if (!$demo->main) {
                $data['product_demos'][] = array('title' => $demo->title, 'url' => $this->url->link('catalog/product/demo', 'product_demo_id=' . $demo->product_demo_id));
            }
        }

        $data['product_images'] = array();
        foreach ($this->model_catalog_product->getProductImages($product_info->product_id, $this->language->getId()) as $image) {
            if (!$image->main) {
                $data['product_images'][] = array(
                    'title'    => $image->title,
                    'preview'  => $this->cache->image($image->product_image_id, $product_info->user_id, 50, 50),
                    'original' => $this->cache->image($image->product_image_id, $product_info->user_id, 570, 1000, $image->watermark, false, true));
            }
        }

        $data['product_videos'] = array();
        foreach ($this->model_catalog_product->getProductVideos($product_info->product_id, $this->language->getId()) as $video) {
                $data['product_videos'][] = array(
                    'title' => $video->title,
                    'url'   => $video->iframe_url . $video->id);
        }

        $data['product_audios'] = array();
        foreach ($this->model_catalog_product->getProductAudios($product_info->product_id, $this->language->getId()) as $audio) {
                $data['product_audios'][] = array(
                    'title' => $audio->title,
                    'url'   => $audio->iframe_url . $audio->id);
        }

        $meta_tags            = array();
        $data['product_tags'] = array();

        foreach ($this->model_catalog_product->getProductTags($product_info->product_id, $this->language->getId()) as $tag) {
            $meta_tags[] = $tag->name;
            $data['product_tags'][] = array('name' => $tag->name, 'url' => $this->url->link('catalog/search', 'q=' . urlencode($tag->name)));
        }

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

        $data['product_special_expires']         = $special_expires;
        $data['product_special_regular_price']   = $product_info->special_regular_price > 0 ? $this->currency->format($product_info->special_regular_price, $product_info->currency_id) : 0;
        $data['product_special_exclusive_price'] = $product_info->special_exclusive_price > 0 ? $this->currency->format($product_info->special_exclusive_price, $product_info->currency_id) : 0;

        $data['product_regular_price']           = $this->currency->format($product_info->regular_price, $product_info->currency_id);
        $data['product_exclusive_price']         = $this->currency->format($product_info->exclusive_price, $product_info->currency_id);

        $data['product_has_regular_price']       = $product_info->regular_price > 0 ? true : false;
        $data['product_has_exclusive_price']     = $product_info->exclusive_price > 0 ? true : false;

        $data['product_has_special_regular_price']   = $product_info->special_regular_price > 0 ? true : false;
        $data['product_has_special_exclusive_price'] = $product_info->special_exclusive_price > 0 ? true : false;


        $data['license_form_action'] = $this->url->link('catalog/product', 'product_id=' . $product_info->product_id);

        $data['license']   = str_replace('h2', 'h4 class="license-header"', $this->load->controller('common/information/licensesRegular'));
        $data['regular']   = true;
        $data['exclusive'] = 0 == $product_info->regular_price && 0 == $product_info->special_regular_price ? true : false;

        if ('POST' == $this->request->getRequestMethod()) {
            switch ($this->request->post['license']) {
                case 'exclusive':
                    $data['regular']   = false;
                    $data['exclusive'] = true;
                    $data['license']   = $this->load->controller('common/information/licensesExclusive');
                    break;
                default:
                    $data['regular']   = true;
                    $data['exclusive'] = false;
            }
        }

        // Create meta-tags
        $this->document->setTitle(sprintf(tt('Buy %s with BitCoin'), $product_info->title) . ' | Royalty Free ' . implode(' ', $categories));

        $meta_description = html_entity_decode($product_info->description, ENT_QUOTES, 'UTF-8');
        $meta_description = (strlen($meta_description) > 100) ? substr($meta_description, 0, strpos($meta_description, ' ', 100)) : $meta_description;
        $meta_description = trim(preg_replace('/\s+/', ' ', $meta_description), '.,:;-/+"');

        $this->document->setDescription(sprintf(tt('Royalty-free %s %s by %s with BitCoin. %s. Buy with BTC easy - Download instantly!'),   $categories[0],
                                                                                                                                            $product_info->title,
                                                                                                                                            $product_info->username,
                                                                                                                                            $meta_description,
                                                                                                                                            implode(' and ', $categories)));

        $this->document->setKeywords(sprintf(tt('bitsybay, bitcoin, btc, indie, marketplace, store, buy, sell, royalty-free, %s, %s, %s'),  $product_info->username,
                                                                                                                                            strtolower(implode(', ', $categories)),
                                                                                                                                            implode(', ', $meta_tags)));

        // Load layout
        $data['title']  = $product_info->title;

        $data['alert_warning'] = $this->load->controller('common/alert/warning');
        $data['alert_success'] = $this->load->controller('common/alert/success');
        $data['alert_danger'] = $this->load->controller('common/alert/danger');


        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', $breadcrumbs);

        // Renter the template
        $this->response->setOutput($this->load->view('catalog/product.tpl', $data));
    }

    public function demo() {

        // Init variables
        $product_info = array();
        $product_demo_info = array();

        // Check dependencies
        if (!isset($this->request->get['product_demo_id']) || !$product_demo_info = $this->model_catalog_product->getProductDemo((int)$this->request->get['product_demo_id'], $this->language->getId())) {
            $this->security_log->write('Try to get product demo page without product_id parameter');
            $this->response->redirect($this->url->link('common/home'));

        } else if (!$product_info = $this->model_catalog_product->getProduct($product_demo_info->product_id, $this->auth->getId(), ORDER_APPROVED_STATUS_ID)) {
            $this->security_log->write('Try to get product info by fail demo product_id parameter');
            $this->response->redirect($this->url->link('common/home'));
        }

        // Load layout
        $this->document->addStyle('/bootstrap/3.3.2/css/bootstrap.min.css');
        $this->document->addStyle('/stylesheet/common.css');

        $this->document->setTitle($product_info->title . ' (' . $product_demo_info->title . ')');

        $data['description'] = $this->document->getDescription();
        $data['keywords']    = $this->document->getKeywords();
        $data['links']       = $this->document->getLinks();
        $data['styles']      = $this->document->getStyles();
        $data['scripts']     = $this->document->getScripts();

        $data['lang']        = $this->language->getCode();
        $data['icon']        = '';
        $data['logo']        = '';
        $data['base']        = '';

        $data['bool_is_logged'] = $this->auth->isLogged();
        $data['title']          = $product_info->title . ' (' . $product_demo_info->title . ')';

        $data['meta_title']     = $this->document->getTitle();

        $data['product_id']    = $product_info->product_id;
        $data['download']      = $product_info->order_status_id == ORDER_APPROVED_STATUS_ID || $product_info->user_id == $this->auth->getId() ? true : false;
        $data['favorite']      = $product_info->favorite;
        $data['favorites']     = $product_info->favorites ? $product_info->favorites : false;
        $data['href_view']     = $this->url->link('catalog/product', 'product_id=' . $product_info->product_id);
        $data['href_download'] = $this->url->link('catalog/product/download', 'product_id=' . $product_info->product_id);
        $data['href_original'] = $product_demo_info->url;

        // Renter the template
        $this->response->setOutput($this->load->view('catalog/demo.tpl', $data));
    }

    public function download() {

        // Only for logged users
        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to get product from guest');
            exit;
        }

        // Check dependencies
        if (!isset($this->request->get['product_id'])) {
            $this->security_log->write('Try to get product file without product_id parameter' . isset($this->request->get['product_id']) ? (int) $this->request->get['product_id'] : false);
            exit;
        }

        // Get file info
        if ($product_file_info = $this->model_catalog_product->getProductFileInfo($this->request->get['product_id'])) {

            // Check access
            if ($product_file_info->user_id == $this->auth->getId() || ORDER_APPROVED_STATUS_ID == $this->model_common_order->getOrderStatus($this->request->get['product_id'],
                                                                                                                                             $this->auth->getId())) {

                // Register download
                $this->model_catalog_product->createProductFileDownload(
                    $product_file_info->product_file_id,
                    $this->auth->getId()
                );

                // Get file
                $this->storage->getProductFile(
                    $product_file_info->product_file_id,
                    $product_file_info->user_id,
                    sprintf('%s_%s%s', mb_strtolower(PROJECT_NAME), $this->request->get['product_id'], date('dmy'))
                );
            }
        }

        $this->security_log->write('Try to get denied product file');
        exit;
    }

    // AJAX actions begin
    public function favorite() {

        // Only for logged users
        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to add product as favorite from guest request');
            exit;
        }

        // Check request
        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to add product as favorite without ajax request');
            exit;
        }

        // Check dependencies
        if (!isset($this->request->post['product_id'])) {
            $this->security_log->write('Try to add product as favorite without product_id parameter');
            exit;
        }
        // Preset variables
        $json       = array();
        $product_id = (int) $this->request->post['product_id'];

        // Get additional info
        $total   = $this->model_catalog_product->getProductFavoritesTotal($product_id);
        $product = $this->model_catalog_product->getProduct($product_id, $this->auth->getId(), ORDER_APPROVED_STATUS_ID);
        $user    = $this->model_account_user->getUser($product->user_id);

        // Favorite
        if ($this->model_catalog_product->createProductFavorite($product_id, $this->auth->getId())) {

            // Is not seller
            if ($product->user_id != $this->auth->getId()) {

                // Add notification
                $this->model_account_notification->addNotification($product->user_id,
                                                                   $this->language->getId(),
                                                                   'activity',
                                                                   tt('Your product has been marked as favorite'),
                                                                   sprintf(tt("@%s has marked %s as favorite.\nCheers!"), $this->auth->getUsername(), $product->title));

                // If subscription enabled
                if ($this->model_account_subscription->checkUserSubscription($product->user_id, FAVORITE_SUBSCRIPTION_ID)) {

                    // Send mail
                    $mail_data['project_name'] = PROJECT_NAME;

                    $mail_data['subject'] = sprintf(tt('Your product has been marked as favorite - %s'), PROJECT_NAME);
                    $mail_data['message'] = sprintf(tt("@%s has marked %s as favorite.\nCheers!"), $this->auth->getUsername(), $product->title);

                    $mail_data['href_home']         = $this->url->link('common/home');
                    $mail_data['href_contact']      = $this->url->link('common/contact');
                    $mail_data['href_subscription'] = $this->url->link('account/account/subscription');

                    $mail_data['href_facebook'] = URL_FACEBOOK;
                    $mail_data['href_twitter']  = URL_TWITTER;
                    $mail_data['href_tumblr']   = URL_TUMBLR;
                    $mail_data['href_github']   = URL_GITHUB;

                    $this->mail->setTo($user->email);
                    $this->mail->setSubject($mail_data['subject']);
                    $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                    $this->mail->send();
                }
            }

            // Set output
            $json = array('total' => $total + 1, 'status' => 200, 'code' => 1);

        } else if ($this->model_catalog_product->deleteProductFavorite($product_id, $this->auth->getId())) {

            // Set output
            $json = array('total' => $total - 1, 'status' => 200, 'code' => 0);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function report() {

        // Check request
        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to send report without ajax request');
            exit;
        }

        // Set variables
        $product_id = (int) isset($this->request->post['product_id']) ? $this->request->post['product_id'] : 0;
        $message    = (int) isset($this->request->post['message']) ? $this->request->post['message'] : false;

        if ($this->model_catalog_product->createReport($product_id, $message, $this->auth->getId())) {
            $json = array('status' => 200, 'title' => tt('Report successfully sent!'), 'message' => tt('Your message will be reviewed in the near time.'));

            $this->mail->setTo(MAIL_EMAIL_SUPPORT_ADDRESS);
            $this->mail->setSubject(tt('Report for product_id #' . $product_id));
            $this->mail->setText($this->request->post['message']);
            $this->mail->send();

        } else {
            $json = array('status' => 500, 'title' => tt('Connection error'), 'message' => tt('Please, try again later'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function reviews() {

        $data['reviews'] = array();

        // Check dependencies
        if (!isset($this->request->get['product_id'])) {
            $this->security_log->write('Try to get product reviews without product_id parameter');
            exit;
        }

        // Check request
        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to get product image without ajax request');
            exit;
        }

        $product_reviews_info = $this->model_catalog_product->getProductReviews((int) $this->request->get['product_id'], $this->language->getId());

        if ($product_reviews_info) {
            foreach ($product_reviews_info as $review) {
                $data['reviews'][] = array(
                    'user_id'       => $review->user_id,
                    'username'      => $review->username,
                    'review'        => $review->review,
                    'favorite'      => $review->favorite,
                    'href_user'     => $this->url->link('catalog/search', 'user_id=' . $review->user_id),
                    'date_added'    => date(DATE_FORMAT_DEFAULT, strtotime($review->date_added)),
                    'date_modified' => date(DATE_FORMAT_DEFAULT, strtotime($review->date_modified))
                );
            }
        }

        // Renter the template
        $this->response->setOutput($this->load->view('catalog/reviews.tpl', $data));
    }

    public function review() {

        // Check request
        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to get product image without ajax request');
            exit;
        }

        // Only for logged users
        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to add product as favorite from guest request');
            exit;
        }

        // Check dependencies
        if (!isset($this->request->post['product_id'])) {
            $this->security_log->write('Try to get product reviews without product_id parameter');
            exit;
        }

        // Review text can not by empty
        if (!isset($this->request->post['review'])) {
            $this->security_log->write('Try to get product reviews without product_id parameter');
            exit;
        }

        // Validate review text
        if (empty($this->request->post['review']) || !ValidatorProduct::descriptionValid($this->request->post['review'])) {
            $json = array('error_message' => tt('Review text is not valid!'));
        } else {

            if ($this->model_catalog_product->createProductReview((int)$this->request->post['product_id'], $this->request->post['review'], $this->auth->getId(), $this->language->getId(), 1)) {

                // Get requires
                $product = $this->model_catalog_product->getProduct((int) $this->request->post['product_id'], $this->auth->getId(), ORDER_APPROVED_STATUS_ID);
                $user    = $this->model_account_user->getUser($product->user_id);

                // Is not seller
                if ($product->user_id != $this->auth->getId()) {

                    // Add notification
                    $this->model_account_notification->addNotification($product->user_id,
                                                                       $this->language->getId(),
                                                                       'activity',
                                                                       tt('Your product has been commented'),
                                                                       sprintf(tt("@%s has posted a comment about your product %s.\n"), $this->auth->getUsername(), $product->title));


                    // If subscription enabled
                    if ($this->model_account_subscription->checkUserSubscription($product->user_id, REVIEW_SUBSCRIPTION_ID)) {

                        // Send mail
                        $mail_data['project_name'] = PROJECT_NAME;

                        $mail_data['subject'] = sprintf(tt('Your product has been commented - %s'), PROJECT_NAME);
                        $mail_data['message'] = sprintf(tt("@%s has posted a comment about your product %s.\n"), $this->auth->getUsername(), $product->title);

                        $mail_data['href_home']         = $this->url->link('common/home');
                        $mail_data['href_contact']      = $this->url->link('common/contact');
                        $mail_data['href_subscription'] = $this->url->link('account/account/subscription');

                        $mail_data['href_facebook'] = URL_FACEBOOK;
                        $mail_data['href_twitter']  = URL_TWITTER;
                        $mail_data['href_tumblr']   = URL_TUMBLR;
                        $mail_data['href_github']   = URL_GITHUB;

                        $this->mail->setTo($user->email);
                        $this->mail->setSubject($mail_data['subject']);
                        $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                        $this->mail->send();
                    }
                }

                $json = array('success_message' => tt('Thank you for your review!'));
            } else {
                $json = array('error_message' => tt('Internal server error! Please try again later.'));
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
