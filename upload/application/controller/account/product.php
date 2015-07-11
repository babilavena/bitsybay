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

class ControllerAccountProduct extends Controller {

    private $_error = array();

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('common/language');
        $this->load->model('common/currency');
        $this->load->model('common/video_server');
        $this->load->model('common/redirect');

        $this->load->model('account/user');

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/tag');

        $this->load->helper('validator/product');
        $this->load->helper('validator/upload');
        $this->load->helper('validator/youtube');
        $this->load->helper('validator/vimeo');
        $this->load->helper('validator/bitcoin');

        $this->load->helper('filter/uri');
        $this->load->library('identicon');

    }

    // Route actions begin
    public function index() {

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->link('account/product'))));
        }

        $data = array();

        $this->document->setTitle(tt('All products'));

        $data['href_account_product_create'] = $this->url->link('account/product/create');

        $data['alert_success']  = $this->load->controller('common/alert/success');
        $data['alert_danger']   = $this->load->controller('common/alert/danger');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['products'] = array();

        $filter_data = array('user_id' => $this->auth->getId());

        $total = $this->model_catalog_product->getTotalProducts($filter_data);

        if ($total) {
            $products = $this->model_catalog_product->getProducts($filter_data, $this->language->getId(), $this->auth->getId(), ORDER_APPROVED_STATUS_ID);

            foreach ($products as $product) {
                $data['products'][] = array(
                    'product_id'              => $product->product_id,
                    'title'                   => $product->title,
                    'image'                   => $this->cache->image($product->main_product_image_id, $product->user_id, 36, 36),
                    'date_added'              => date(tt('Y.m.d'), strtotime($product->date_added)),
                    'special_regular_price'   => $product->special_regular_price ? $this->currency->format($product->special_regular_price, $product->currency_id) : 0,
                    'special_exclusive_price' => $product->special_exclusive_price ? $this->currency->format($product->special_exclusive_price, $product->currency_id) : 0,
                    'regular_price'           => $this->currency->format($product->regular_price, $product->currency_id),
                    'exclusive_price'         => $this->currency->format($product->exclusive_price, $product->currency_id),
                    'regular_status'          => $product->special_regular_price > 0 || $product->regular_price > 0 ? true : false,
                    'exclusive_status'        => $product->special_exclusive_price > 0 || $product->exclusive_price > 0 ? true : false,
                    'sales'                   => $product->sales,
                    'favorites'               => $product->favorites,
                    'viewed'                  => $product->viewed,
                    'status'                  => $product->status,
                    'href_edit'               => $this->url->link('account/product/update', 'product_id=' . $product->product_id),
                    'href_delete'             => $this->url->link('account/product/delete', 'product_id=' . $product->product_id),
                    'href_download'           => $this->url->link('catalog/product/download', 'product_id=' . $product->product_id),
                    'href_view'               => $this->url->link('catalog/product', 'product_id=' . $product->product_id)
                );
            }
        }
        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Product list'), 'href' => $this->url->link('account/product'), 'active' => true)
            ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/product/product_list.tpl', $data));
    }

    public function create() {

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login',
                                                       'redirect=' . base64_encode($this->url->link('account/product/create'))));
        }

        if ('POST' == $this->request->getRequestMethod() && $this->_validateProductForm()) {

            // Start transaction
            $this->db->beginTransaction();

            // Add product
            $product_id = $this->model_catalog_product->createProduct(  $this->auth->getId(),
                                                                        $this->request->post['category_id'],
                                                                        $this->request->post['currency_id'],
                                                                        $this->request->post['regular_price'],
                                                                        $this->request->post['exclusive_price'],
                                                                        $this->request->post['withdraw_address'],
                                                                        FilterUri::alias($this->request->post['product_description'][DEFAULT_LANGUAGE_ID]['title']));

            // Add product description
            foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                $this->model_catalog_product->createProductDescription( $product_id,
                                                                        $language_id,
                                                                        $product_description['title'],
                                                                        $product_description['description']);
            }

            // Add Tags
            foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                if (!empty($product_description['tags'])) {

                    $tags = explode(',', $product_description['tags']);
                    foreach ($tags as $tag) {

                        // Add a new global tag if not exists
                        $tag_id = $this->model_catalog_tag->createTag(mb_strtolower(trim($tag)), $language_id);

                        // Add product to tag relation
                        $this->model_catalog_product->addProductToTag($product_id, $tag_id);
                    }
                }
            }

            // Add file
            $directory = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;

            if ($file_content = file_get_contents($directory . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION)) {

                $product_file_id = $this->model_catalog_product->createProductFile( $product_id,
                                                                                    md5($file_content),
                                                                                    sha1($file_content));
                rename(
                    $directory . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION,
                    $directory . $product_file_id . '.' . STORAGE_FILE_EXTENSION
                );
            }

            // Add demos
            if (isset($this->request->post['demo'])) {
                foreach ($this->request->post['demo'] as $row => $demo) {
                    $product_demo_id = $this->model_catalog_product->createProductDemo( $product_id,
                                                                                        $demo['sort_order'],
                                                                                        $demo['url'],
                                                                                        $this->request->post['main_demo'] == $row ? 1 : 0);

                    foreach ($demo['title'] as $language_id => $title) {
                        $this->model_catalog_product->createProductDemoDescription( $product_demo_id,
                                                                                    $language_id,
                                                                                    $title);
                    }
                }
            }

            // Add images
            if (isset($this->request->post['image'])) {
                foreach ($this->request->post['image'] as $row => $image) {

                        $product_image_id = $this->model_catalog_product->createProductImage($product_id,
                                                                                             $image['sort_order'],
                                                                                             $this->request->post['main_image'] == $row ? 1 : 0,
                                                                                             isset($image['watermark']) ? 1 : 0);

                        // Generate image titles
                        foreach ($image['title'] as $language_id => $title) {
                            $this->model_catalog_product->createProductImageDescription($product_image_id,
                                                                                        $language_id,
                                                                                        $title);
                        }

                        // Rename temporary file
                        $directory = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;
                        rename(
                            $directory . $image['product_image_id'] . '.' . STORAGE_IMAGE_EXTENSION,
                            $directory . $product_image_id . '.' . STORAGE_IMAGE_EXTENSION
                        );

                }

            // Generate unique image if others images is not exists
            } else {

                $product_image_id = $this->model_catalog_product->createProductImage($product_id, 1, 1, 0, 1);

                // Generate image titles from product title
                foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                    $this->model_catalog_product->createProductImageDescription($product_image_id,
                                                                                $language_id,
                                                                                $product_description['title']);
                }

                $identicon = new Identicon();
                $image     = new Image($identicon->generateImageResource(sha1($product_id),
                                                                         PRODUCT_IMAGE_ORIGINAL_WIDTH,
                                                                         PRODUCT_IMAGE_ORIGINAL_HEIGHT), true);

                $image->save(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $product_image_id . '.' . STORAGE_IMAGE_EXTENSION);
            }

            // Add videos
            if (isset($this->request->post['video'])) {
                foreach ($this->request->post['video'] as $video) {

                    $product_video_id = $this->model_catalog_product->createProductVideo($product_id,
                                                                                         $video['source'],
                                                                                         $video['sort_order'],
                                                                                         $video['id']);

                    foreach ($video['title'] as $language_id => $title) {
                        $this->model_catalog_product->createProductVideoDescription($product_video_id,
                                                                                    $language_id,
                                                                                    $title);
                    }
                }
            }

            // Add specials
            if (isset($this->request->post['special'])) {
                foreach ($this->request->post['special'] as $special) {
                    $this->model_catalog_product->createProductSpecial( $product_id,
                                                                        $special['regular_price'],
                                                                        $special['exclusive_price'],
                                                                        $special['date_start'],
                                                                        $special['date_end'],
                                                                        $special['sort_order']);
                }
            }

            $this->db->commit();

            // Reset cache
            $this->cache->clean($this->auth->getId());

            $this->session->setUserMessage(array('success' => tt('Product successfully published!')));
            $this->response->redirect($this->url->link('account/product'));
        }

        $data = $this->_populateForm($this->url->link('account/product/create'));

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Product list'), 'href' => $this->url->link('account/product'), 'active' => false),
                    array('name' => tt('Add product'), 'href' => $this->url->link('account/product/create'), 'active' => true),
            ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/product/product_form.tpl', $data));
    }

    public function update() {

        $product_id = 0;

        // Redirect to product create if product_id is not exists
        if (isset($this->request->get['product_id'])) {
            $product_id = (int) $this->request->get['product_id'];
        } else {
            // Log hack attempt
            $this->security_log->write('Try to get product without product_id param');
            $this->response->redirect($this->url->link('account/product/create'));
        }

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->link('account/product/update', 'product_id=' . $product_id))));
        }

        // Check if user has product
        if (!$this->model_catalog_product->userHasProduct($this->auth->getId(), $product_id)) {

            // Log hack attempt
            $this->security_log->write('Try to get not own\'s product_id #' . $product_id);

            // Redirect to safe page
            $this->response->redirect($this->url->link('account/product'));
        }

        if ('POST' == $this->request->getRequestMethod() && $this->_validateProductForm()) {

            // Start transaction
            $this->db->beginTransaction();

            // Add product
            $this->model_catalog_product->updateProduct($product_id,
                                                        $this->request->post['category_id'],
                                                        $this->request->post['currency_id'],
                                                        $this->request->post['regular_price'],
                                                        $this->request->post['exclusive_price'],
                                                        $this->request->post['withdraw_address'],
                                                        FilterUri::alias($this->request->post['product_description'][DEFAULT_LANGUAGE_ID]['title']));

            // Add 301 rule if product has new URI

            $url = new Url($this->db, $this->request, $this->response, URL_BASE);

            $old_url = $this->url->link('catalog/product', 'product_id=' . $product_id);
            $new_url = $url->link('catalog/product', 'product_id=' . $product_id);

            if ($old_url != $new_url) {
                $this->model_common_redirect->createRedirect(301, str_replace(URL_BASE, $old_url), str_replace(URL_BASE, $new_url));
            }

            // Add product description
            $this->model_catalog_product->deleteProductDescriptions($product_id);

            foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                $this->model_catalog_product->createProductDescription( $product_id,
                                                                        $language_id,
                                                                        $product_description['title'],
                                                                        $product_description['description']);
            }

            // Add Tags
            $this->model_catalog_product->deleteProductToTagByProductId($product_id);

            foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                if (!empty($product_description['tags'])) {

                    $tags = explode(',', $product_description['tags']);
                    foreach ($tags as $tag) {

                        // Add a new global tag if not exists
                        $tag_id = $this->model_catalog_tag->createTag(mb_strtolower(trim($tag)), $language_id);

                        // Add product to tag relation
                        $this->model_catalog_product->addProductToTag($product_id, $tag_id);
                    }
                }
            }

            // Add file
            $directory = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;
            if ($file_content = file_get_contents($directory . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION)) {

                $this->model_catalog_product->deleteProductFiles($product_id);
                $product_file_id = $this->model_catalog_product->createProductFile( $product_id,
                                                                                    md5($file_content),
                                                                                    sha1($file_content));
                rename(
                    $directory . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION,
                    $directory . $product_file_id . '.' . STORAGE_FILE_EXTENSION
                );
            }

            // Add demos
            $this->model_catalog_product->deleteProductDemos($product_id);

            if (isset($this->request->post['demo'])) {
                foreach ($this->request->post['demo'] as $row => $demo) {
                    $product_demo_id = $this->model_catalog_product->createProductDemo($product_id, $demo['sort_order'], $demo['url'], $this->request->post['main_demo'] == $row ? 1 : 0);

                    foreach ($demo['title'] as $language_id => $title) {
                        $this->model_catalog_product->createProductDemoDescription($product_demo_id, $language_id, $title);
                    }
                }
            }

            // Update images
            $this->model_catalog_product->deleteProductImages($product_id);

            if (isset($this->request->post['image'])) {
                foreach ($this->request->post['image'] as $row => $image) {
                    $product_image_id = $this->model_catalog_product->createProductImage($product_id,
                                                                                         $image['sort_order'],
                                                                                         $this->request->post['main_image'] == $row ? 1 : 0,
                                                                                         isset($image['watermark']) ? 1 : 0);

                    // Generate image titles
                    foreach ($image['title'] as $language_id => $title) {
                        $this->model_catalog_product->createProductImageDescription($product_image_id,
                                                                                    $language_id,
                                                                                    $title);
                    }

                    // Rename temporary file
                    $directory = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;
                    rename(
                        $directory . $image['product_image_id'] . '.' . STORAGE_IMAGE_EXTENSION,
                        $directory . $product_image_id . '.' . STORAGE_IMAGE_EXTENSION
                    );
                }

            // Generate unique image if others images is not exists
            } else {

                $product_image_id = $this->model_catalog_product->createProductImage($product_id, 1, 1, 0, 1);

                // Generate image titles from product title
                foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                    $this->model_catalog_product->createProductImageDescription($product_image_id,
                                                                                $language_id,
                                                                                $product_description['title']);
                }

                $identicon = new Identicon();
                $image     = new Image($identicon->generateImageResource(sha1($product_id),
                                                                         PRODUCT_IMAGE_ORIGINAL_WIDTH,
                                                                         PRODUCT_IMAGE_ORIGINAL_HEIGHT), true);

                $image->save(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $product_image_id . '.' . STORAGE_IMAGE_EXTENSION);
            }


            // Add videos
            $this->model_catalog_product->deleteProductVideos($product_id);

            if (isset($this->request->post['video'])) {
                foreach ($this->request->post['video'] as $video) {

                    $product_video_id = $this->model_catalog_product->createProductVideo($product_id, $video['source'], $video['sort_order'], $video['id']);

                    foreach ($video['title'] as $language_id => $title) {
                        $this->model_catalog_product->createProductVideoDescription($product_video_id, $language_id, $title);
                    }
                }
            }

            // Add specials
            $this->model_catalog_product->deleteProductSpecials($product_id);

            if (isset($this->request->post['special'])) {
                foreach ($this->request->post['special'] as $special) {
                    $this->model_catalog_product->createProductSpecial( $product_id,
                                                                        $special['regular_price'],
                                                                        $special['exclusive_price'],
                                                                        $special['date_start'],
                                                                        $special['date_end'],
                                                                        $special['sort_order']);
                }
            }

            $this->db->commit();

            // Cleaning
            $this->cache->clean($this->auth->getId());
            $this->storage->clean($this->auth->getId());

            $this->session->setUserMessage(array('success' => tt('Product successfully updated!')));
            $this->response->redirect($this->url->link('account/product'));
        }

        $data = $this->_populateForm($this->url->link('account/product/update', 'product_id=' . $product_id));

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Product list'), 'href' => $this->url->link('account/product'), 'active' => false),
                    array('name' => tt('Update product'), 'href' => $this->url->link('account/product/update', 'product_id=' . $product_id), 'active' => true),
            ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/product/product_form.tpl', $data));
    }

    public function delete() {

        // Redirect to product create if product_id is not exists
        if (!isset($this->request->get['product_id'])) {

            // Log hack attempt
            $this->security_log->write('Try to delete product without product_id param');
            $this->response->redirect($this->url->link('account/product'));
        }

        $product_id = (int) $this->request->get['product_id'];

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login',
                                                       'redirect=' . base64_encode($this->url->link('account/product/delete', 'product_id=' . $product_id)),
                                                       'SSL'));
        }

        // Check if user has product
        if (!$this->model_catalog_product->userHasProduct($this->auth->getId(), $product_id)) {

            // Log hack attempt
            $this->security_log->write('Try to delete not own\'s product #' . $product_id);
            $this->response->redirect($this->url->link('account/product'));
        }

        // Check if all customers already download package files
        if ($this->model_catalog_product->productHasRelations($product_id, ORDER_PENDING_STATUS_ID, ORDER_PROCESSED_STATUS_ID, ORDER_APPROVED_STATUS_ID)) {

            $this->session->setUserMessage(array('danger' => tt('Looks like someone has ordered this product, try again later!')));
            $this->response->redirect($this->url->link('account/product'));
        }

        // Begin action
        $this->document->setTitle(tt('Deleting...'));

        // Start transaction
        $this->db->beginTransaction();

        // Delete product description
        $this->model_catalog_product->deleteProductDescriptions($product_id);

        // Delete Tags
        $this->model_catalog_product->deleteProductToTagByProductId($product_id);

        // Delete product files
        $product_file_info = $this->model_catalog_product->getProductFileInfo($product_id);
        unlink(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $product_file_info->product_file_id . '.' . STORAGE_FILE_EXTENSION);
        $this->model_catalog_product->deleteProductFiles($product_id);

        // Delete demos
        $this->model_catalog_product->deleteProductDemos($product_id);

        // Delete images
        $product_images = $this->model_catalog_product->getProductImages($product_id, DEFAULT_LANGUAGE_ID);
        foreach ($product_images as $product_image) {
            unlink(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $product_image->product_image_id . '.' . STORAGE_IMAGE_EXTENSION);
        }

        $this->model_catalog_product->deleteProductImages($product_id);

        // Delete videos
        $this->model_catalog_product->deleteProductVideos($product_id);

        // Delete specials
        $this->model_catalog_product->deleteProductSpecials($product_id);

        // Delete favorites
        $this->model_catalog_product->deleteProductFavorites($product_id);

        // Delete reviews
        $this->model_catalog_product->deleteProductReviews($product_id);

        // Reconfigure orders relations
        $this->model_catalog_product->reconfigureProductToOrders($product_id);

        // Delete product
        $this->model_catalog_product->deleteProduct($product_id);


        $this->db->commit();

        // Reset cache
        $this->cache->clean($this->auth->getId());

        $this->session->setUserMessage(array('success' => tt('Product successfully deleted!')));
        $this->response->redirect($this->url->link('account/product'));

    }

    // AJAX actions begin
    public function quota() {

        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to access to quota method from guest request');
            exit;
        }

        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to access to quota method without ajax request');
            exit;
        }

        $data = array();

        if (isset($this->request->get['product_file_id'])) {

            $directory = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;
            $filename  = $this->request->get['product_file_id'] . '.' . STORAGE_FILE_EXTENSION;
            $file_size = (file_exists($directory . DIR_SEPARATOR . $filename)) ? filesize($directory . $filename) / 1000000 : false;

            if ($file_size > 0) {
                if (isset($this->request->get['product_id'])) {
                    $data = array('custom_file_space' => $file_size, 'except_size' => $file_size);
                } else {
                    $data = array('custom_file_space' => $file_size);
                }
            } else {
                $this->security_log->write('Try to access to not own\'s temporary file');
            }
        }

        $this->response->setOutput($this->load->controller('module/quota_bar', $data));
    }

    public function uploadPackage() {

        if (!$this->auth->isLogged()) {
            $this->security_log->write('Trying to access to uploadPackage method from guest request');
            exit;
        }

        if (!$this->request->isAjax()) {
            $this->security_log->write('Trying to access to uploadPackage method without ajax request');
            exit;
        }

        $json = array('error_message' => tt('Undefined upload error'));

        if ('POST' == $this->request->getRequestMethod() && $this->_validatePackage()) {

            $file_content = file_get_contents($this->request->files['package']['tmp_name']);

            // Generate unique path names
            $filename  = '_' . sha1(rand().microtime().$this->auth->getId());

            // Create user's folder if not exists
            if (!is_dir(DIR_STORAGE . $this->auth->getId())) {
                mkdir(DIR_STORAGE . $this->auth->getId(), 0755);
            }

            // Return result
            if (move_uploaded_file($this->request->files['package']['tmp_name'], DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $filename . '.' . STORAGE_FILE_EXTENSION)) {
                $json = array('success_message'   => tt('Package file was successfully uploaded!'),
                              'product_file_id'   => $filename,
                              'hash_md5'          => 'MD5:  ' . md5($file_content),
                              'hash_sha1'         => 'SHA1: ' . sha1($file_content));
            }

        } else if (isset($this->_error['file']['common'])) {
            $json = array('error_message' => $this->_error['file']['common']);
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function uploadImage() {

        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to upload image from guest request');
            exit;
        }

        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to upload image without ajax request');
            exit;
        }

        $json = array('error_message' => tt('Undefined upload error'));

        if ('POST' == $this->request->getRequestMethod() && $this->_validateImage()) {

            // Create user's folder if not exists
            if (!is_dir(DIR_STORAGE . $this->auth->getId())) {
                mkdir(DIR_STORAGE . $this->auth->getId(), 0755);
            }

            $image = new Image($this->request->files['image']['tmp_name'][$this->request->get['row']]);

            // Resize to default original format
            if (PRODUCT_IMAGE_ORIGINAL_WIDTH < $image->getWidth() || PRODUCT_IMAGE_ORIGINAL_HEIGHT < $image->getHeight()) {
                $image->resize(PRODUCT_IMAGE_ORIGINAL_WIDTH, PRODUCT_IMAGE_ORIGINAL_HEIGHT, 1, false, true);
            }

            $image_path     = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR;
            $image_filename = '_' . sha1(rand().microtime().$this->auth->getId());

            // Save image to the temporary file
            if ($image->save($image_path . $image_filename . '.' . STORAGE_IMAGE_EXTENSION)) {
                $json = array('success_message'   => tt('Image successfully uploaded!'),
                              'url'               => $this->cache->image($image_filename, $this->auth->getId(), 36, 36),
                              'product_image_id'  => $image_filename);
            }

        } else if (isset($this->_error['image']['common'])) {
            $json = array('error_message' => $this->_error['image']['common']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Local helpers begin
    private function _populateForm($action) {

        $data = array();

        // Common
        $data['date_today']           = date('Y-m-d');
        $data['date_tomorrow']        = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d'))));

        $data['product_id']           = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : false;
        $data['error']                = $this->_error;
        $data['action']               = $action;
        $data['href_account_product'] = $this->url->link('account/product');

        // Get saved info
        if (isset($this->request->get['product_id'])) {
            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id'], $this->auth->getId(), ORDER_APPROVED_STATUS_ID);
            $this->document->setTitle(sprintf(tt('Edit %s'), $product_info->title));
            $data['title'] = sprintf(tt('Edit %s'), $product_info->title);
        } else {
            $product_info = array();
            $this->document->setTitle(tt('Add product'));
            $data['title'] = tt('Add product');
        }

        // File
        if ( isset($this->request->post['product_file_id']) &&
            !empty($this->request->post['product_file_id']) &&
            file_exists(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION) &&
            $file_content = file_get_contents(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION)) {

            $data['package_hash_md5']    = 'MD5:  ' . md5($file_content);
            $data['package_hash_sha1']   = 'SHA1: ' . sha1($file_content);
            $data['product_file_id']     = $this->request->post['product_file_id'];
            $data['module_quota_bar']    = $this->load->controller('module/quota_bar',
                                                                    array('custom_file_space' => $this->storage->getFileSize($this->request->post['product_file_id'],
                                                                                                                             $this->auth->getId(),
                                                                                                                             STORAGE_FILE_EXTENSION)));

        } else if ($product_info && $product_file_info = $this->model_catalog_product->getProductFileInfo($product_info->product_id)) {

            $data['package_hash_md5']    = 'MD5:  ' . $product_file_info->hash_md5;
            $data['package_hash_sha1']   = 'SHA1: ' . $product_file_info->hash_sha1;
            $data['product_file_id'] = $product_file_info->product_file_id;

            $file_size = $this->storage->getFileSize($product_file_info->product_file_id,
                                                     $this->auth->getId(),
                                                     STORAGE_FILE_EXTENSION);

            $data['module_quota_bar'] = $this->load->controller('module/quota_bar',
                                                                array('except_size' => $file_size,
                                                                'custom_file_space' => $file_size));

        } else {

            $data['package_hash_md5']    = false;
            $data['package_hash_sha1']   = false;
            $data['product_file_id'] = false;
            $data['module_quota_bar']    = $this->load->controller('module/quota_bar');
        }

        // Languages
        $languages = $this->model_common_language->getLanguages();

        $data['languages']  = array();
        foreach ($languages as $language) {
            $data['languages'][] = array('language_id' => $language->language_id);
        }

        // Product descriptions
        $data['product_description'] = array();

        if (isset($this->request->post['product_description'])) {
            foreach ($this->request->post['product_description'] as $language_id => $product_description) {
                $data['product_description'][$language_id] = array('title'       => isset($product_description['title']) ? $product_description['title'] : false,
                                                                   'description' => isset($product_description['description']) ? $product_description['description'] : false,
                                                                   'tags'        => isset($product_description['tags']) ? $product_description['tags'] : false);

            }
        } elseif ($product_info) {

            $product_tags = $this->model_catalog_product->getTagsByProductId($this->request->get['product_id']);

            $product_tag_descriptions = array();
            foreach ($product_tags as $product_tag) {
                foreach ($this->model_catalog_tag->getTagDescriptions($product_tag->tag_id) as $tag_description) {
                    $product_tag_descriptions[$tag_description->language_id][] = $tag_description->name;
                }
            }




            foreach ($this->model_catalog_product->getProductDescriptions($this->request->get['product_id']) as $product_description) {
                $data['product_description'][$product_description->language_id] = array('title'       => $product_description->title,
                                                                                        'description' => $product_description->description,
                                                                                        'tags'        => isset($product_tag_descriptions[$product_description->language_id]) ? implode(', ', $product_tag_descriptions[$product_description->language_id]) : false);
            }
        } else {
            foreach ($languages as $language) {
                $data['product_description'][$language->language_id] = array('title'       => false,
                                                                             'description' => false,
                                                                             'tags'        => false);
            }
        }

        // Demos
        $demo_rows = array(0);
        $data['demos'] = array();

        if (isset($this->request->post['demo'])) {
            foreach ($this->request->post['demo'] as $row => $demo) {

                $demo_rows[] = $row;

                $demo_titles = array();

                foreach ($demo['title'] as $language_id => $title) {
                    $demo_titles[$language_id] = $title;
                }

                $data['demos'][$row] = array(
                    'main'  => isset($this->request->post['main_demo']) && $this->request->post['main_demo'] == $row ? true : false,
                    'url'   => isset($demo['url']) ? $demo['url'] : false,
                    'title' => $demo_titles);
            }

        } else if ($product_info) {
            foreach ($this->model_catalog_product->getProductDemos($product_info->product_id, $this->language->getId()) as $product_demo) {

                $demo_rows[] = $product_demo->product_demo_id;

                $demo_titles = array();

                foreach ($this->model_catalog_product->getProductDemoDescriptions($product_demo->product_demo_id) as $demo_description) {
                    $demo_titles[$demo_description->language_id] = $demo_description->title;
                }

                $data['demos'][$product_demo->product_demo_id] = array(
                    'main'  => $product_demo->main,
                    'url'   => $product_demo->url,
                    'title' => $demo_titles);
            }
        }

        $data['demo_max_row']    = max($demo_rows);
        $data['demo_total_rows'] = count($demo_rows) - 1;

        // Images
        $image_rows = array(0);
        $data['images'] = array();

        if (isset($this->request->post['image'])) {
            foreach ($this->request->post['image'] as $row => $image) {

                $image_rows[]      = $row;
                $image_titles      = array();
                $product_image_url = false;

                foreach ($image['title'] as $language_id => $title) {
                    $image_titles[$language_id] = $title;
                }

                // If image already stored in exist product
                if ( isset($image['product_image_id']) &&
                    !empty($image['product_image_id']) &&
                    file_exists(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $image['product_image_id'] . '.' . STORAGE_IMAGE_EXTENSION)) {

                    $product_image_url = $this->cache->image($image['product_image_id'], $this->auth->getId(), 36, 36);
                }

                $data['images'][$row] = array(
                    'product_image_id'     => $image['product_image_id'],
                    'url'                  => $product_image_url,
                    'identicon'            => isset($image['identicon']) ? 1 : 0,
                    'watermark'            => isset($image['watermark']) ? 1 : 0,
                    'main'                 => isset($this->request->post['main_image']) && $this->request->post['main_image'] == $row ? true : false,
                    'title'                => $image_titles);
            }

        } else if ($product_info) {

            foreach ($this->model_catalog_product->getProductImagesInfo($product_info->product_id) as $row => $image) {

                $row++;
                $image_rows[] = $row;
                $image_titles = array();

                foreach ($this->model_catalog_product->getProductImageDescriptions($image->product_image_id) as $image_description) {
                    $image_titles[$image_description->language_id] = $image_description->title;
                }

                $data['images'][$row] = array(
                    'product_image_id'     => $image->product_image_id,
                    'watermark'            => $image->watermark,
                    'main'                 => $image->main,
                    'identicon'            => $image->identicon,
                    'url'                  => $this->cache->image($image->product_image_id, $this->auth->getId(), 36, 36),
                    'title'                => $image_titles);
            }
        }

        $data['image_max_row']    = max($image_rows);
        $data['image_total_rows'] = count($image_rows) - 1;


        // Videos
        $video_rows = array(0);
        $data['videos'] = array();

        if (isset($this->request->post['video'])) {
            foreach ($this->request->post['video'] as $row => $video) {

                $video_rows[] = $row;

                $video_titles = array();

                foreach ($video['title'] as $language_id => $title) {
                    $video_titles[$language_id] = $title;
                }

                $data['videos'][$row] = array(
                    'source' => isset($video['source']) ? $video['source'] : false,
                    'id'     => isset($video['id']) ? $video['id'] : false,
                    'title'  => $video_titles);
            }

        } else if ($product_info) {
            foreach ($this->model_catalog_product->getProductVideos($product_info->product_id, $this->language->getId()) as $product_video) {

                $video_rows[] = $product_video->product_video_id;

                $video_titles = array();

                foreach ($this->model_catalog_product->getProductVideoDescriptions($product_video->product_video_id) as $video_description) {
                    $video_titles[$video_description->language_id] = $video_description->title;
                }

                $data['videos'][$product_video->product_video_id] = array(
                    'source' => $product_video->video_server_id,
                    'id'     => $product_video->id,
                    'title'  => $video_titles);
            }
        }

        $data['video_max_row']    = max($video_rows);
        $data['video_total_rows'] = count($video_rows) - 1;


        // Specials
        $special_rows = array(0);
        $data['specials'] = array();

        if (isset($this->request->post['special'])) {
            foreach ($this->request->post['special'] as $row => $special) {

                $special_rows[] = $row;

                $data['specials'][$row] = array(
                    'regular_price'   => isset($special['regular_price']) ? $special['regular_price'] : false,
                    'exclusive_price' => isset($special['exclusive_price']) ? $special['exclusive_price'] : false,
                    'date_start'      => isset($special['date_start']) ? $special['date_start'] : false,
                    'date_end'        => isset($special['date_end']) ? $special['date_end'] : false);
            }

        } else if ($product_info) {
            foreach ($this->model_catalog_product->getProductSpecials($product_info->product_id) as $product_special) {

                $special_rows[] = $product_special->product_special_id;

                $data['specials'][$product_special->product_special_id] = array(
                    'regular_price'   => $product_special->regular_price > 0 ? $product_special->regular_price : false,
                    'exclusive_price' => $product_special->exclusive_price > 0 ? $product_special->exclusive_price : false,
                    'date_start'      => $product_special->date_start,
                    'date_end'        => $product_special->date_end);
            }
        }

        $data['special_max_row']    = max($special_rows);
        $data['special_total_rows'] = count($special_rows) - 1;


        // Current exclusive price
        if (isset($this->request->post['withdraw_address'])) {
            $data['withdraw_address'] = $this->request->post['withdraw_address'];
        } else if ($product_info) {
            $data['withdraw_address'] = $this->model_catalog_product->getWithdrawAddress($product_info->product_id);
        } else {
            $data['withdraw_address'] = false;
        }

        // Current exclusive price
        if (isset($this->request->post['exclusive_price'])) {
            $data['exclusive_price'] = $this->request->post['exclusive_price'];
        } else if ($product_info) {
            $data['exclusive_price'] = $product_info->exclusive_price > 0 ? $product_info->exclusive_price : false;
        } else {
            $data['exclusive_price'] = false;
        }

        // Current regular price
        if (isset($this->request->post['regular_price'])) {
            $data['regular_price'] = $this->request->post['regular_price'];
        } else if ($product_info) {
            $data['regular_price'] = $product_info->regular_price > 0 ? $product_info->regular_price : false;
        } else {
            $data['regular_price'] = false;
        }

        // Video servers
        foreach ($this->model_common_video_server->getVideoServers() as $video_server) {
            $data['video_servers'][$video_server->video_server_id] = $video_server->name;
        }

        // Currencies list
        $data['currencies'] = array();
        foreach ($this->model_common_currency->getCurrencies() as $currency) {
            $data['currencies'][$currency->currency_id] = $currency->code;
        }

        // Current currency
        if (isset($this->request->post['currency_id'])) {
            $data['currency_id'] = $this->request->post['currency_id'];
        } else if ($product_info) {
            $data['currency_id'] = $product_info->currency_id;
        } else {
            $data['currency_id'] = 0;
        }

        // Categories list
        $data['categories'] = array();
        foreach ($this->model_catalog_category->getCategories(null, $this->language->getId()) as $category) {
            foreach ($this->model_catalog_category->getCategories($category->category_id, 1) as $child_category) {
                $data['categories'][$category->title][$child_category->category_id] = $child_category->title;
            }
        }

        // Current category
        if (isset($this->request->post['category_id'])) {
            $data['category_id'] = $this->request->post['category_id'];
        } else if ($product_info) {
            $data['category_id'] = $product_info->category_id;
        } else {
            $data['category_id'] = 0;
        }

        return $data;
    }

    private function _validatePackage() {

        if (!isset($this->request->files['package']['tmp_name']) || !isset($this->request->files['package']['name'])) {

            $this->_error['file']['common'] = tt('Uploaded package file is wrong!');
            $this->security_log->write('Uploaded package file is wrong (tmp_name or name indexes is not exists)');

        } else if (!ValidatorUpload::fileValid( $this->request->files['package'],
                                                $this->auth->getFileQuota() - ($this->storage->getUsedSpace($this->auth->getId()) - filesize($this->request->files['package']['tmp_name']) / 1000000),
                                                STORAGE_FILE_EXTENSION)) {

            $this->_error['file']['common'] = sprintf(tt('Package file is a not valid %s archive!'), mb_strtoupper(STORAGE_FILE_EXTENSION));
            $this->security_log->write('Uploaded package file is not valid');
        }

        return !$this->_error;
    }

    private function _validateImage() {

        if (!isset($this->request->get['row']) || empty($this->request->get['row'])) {

            $this->_error['image']['common'] = tt('Image row is wrong!');
            $this->security_log->write('Uploaded image row is wrong');

        } else if (!isset($this->request->files['image']['tmp_name'][$this->request->get['row']]) || !isset($this->request->files['image']['name'][$this->request->get['row']])) {

            $this->_error['image']['common'] = tt('Image file is wrong!');
            $this->security_log->write('Uploaded image file is wrong (tmp_name or name indexes is not exists)');

        } else if (!ValidatorUpload::imageValid(array('name'     => $this->request->files['image']['name'][$this->request->get['row']],
                                                      'tmp_name' => $this->request->files['image']['tmp_name'][$this->request->get['row']]),
                                                      QUOTA_IMAGE_MAX_FILE_SIZE,
                                                      PRODUCT_IMAGE_ORIGINAL_MIN_WIDTH,
                                                      PRODUCT_IMAGE_ORIGINAL_MIN_HEIGHT)) {

            $this->_error['image']['common'] = tt('This is a not valid image file!');
            $this->security_log->write('Uploaded image file is not valid');
        }

        return !$this->_error;
    }

    private function _validateProductForm() {

        // Category
        if (!isset($this->request->post['category_id']) ||
            ($this->request->post['category_id'] != 0 &&
            !$this->model_catalog_category->getCategory($this->request->post['category_id'], $this->language->getId()))
            ) {
            $this->_error['general']['category_id'] = tt('Wrong category field');

            // Filter critical request
            $this->security_log->write('Wrong category_id field');
            $this->request->post['category_id'] = 0;

        } else if ($this->request->post['category_id'] == 0) {
            $this->_error['general']['category_id'] = tt('Category is required');
        }

        // Product description
        if(isset($this->request->post['product_description'])) {

            foreach ($this->request->post['product_description'] as $language_id => $product_description) {

                // Language
                if (!$this->language->hasId($language_id)) {
                    $this->_error['general']['common'] = tt('Wrong language field');

                    // Filter critical request
                    $this->security_log->write('Wrong language_id field');
                    unset($this->request->post['product_description'][$language_id]);
                    break;
                }

                // Title
                if (!isset($product_description['title'])) {
                    $this->_error['general']['product_description'][$language_id]['title'] = tt('Wrong title input');

                    // Filter critical request
                    $this->security_log->write('Wrong product_description[title] field');
                    unset($this->request->post['product_description'][$language_id]);
                    break;

                } else if (empty($product_description['title'])) {
                    $this->_error['general']['product_description'][$language_id]['title'] = tt('Title is required');
                } else if (!ValidatorProduct::titleValid(html_entity_decode($product_description['title']))) {
                    $this->_error['general']['product_description'][$language_id]['title'] = tt('Invalid title format');
                }

                // Description
                if (!isset($product_description['description'])) {
                    $this->_error['general']['product_description'][$language_id]['description'] = tt('Wrong description input');

                    // Filter critical request
                    $this->security_log->write('Wrong product_description[description] field');
                    unset($this->request->post['product_description'][$language_id]);
                    break;

                } else if (empty($product_description['description'])) {
                    $this->_error['general']['product_description'][$language_id]['description'] = tt('Description is required');
                } else if (!ValidatorProduct::descriptionValid(html_entity_decode($product_description['description']))) {
                    $this->_error['general']['product_description'][$language_id]['description'] = tt('Invalid description format');
                }

                // Tags
                if (!isset($product_description['tags'])) {
                    $this->_error['general']['product_description'][$language_id]['tags'] = tt('Wrong tags input');

                    // Filter critical request
                    $this->security_log->write('Wrong product_description[tags] field');
                    unset($this->request->post['product_description'][$language_id]);
                    break;

                } else if (!ValidatorProduct::tagsValid(html_entity_decode($product_description['tags']))) {
                    $this->_error['general']['product_description'][$language_id]['tags'] = tt('Invalid tags format');
                }
            }
        }

        // Package file
        if (isset($this->request->files['package']['tmp_name']) && !empty($this->request->files['package']['tmp_name'])) {

            $this->_error['file']['common'] = tt('Package file is not allowed for this action');
            $this->security_log->write('Try to load package file without ajax interface');
            unset($this->request->files['package']);

        } else if (!isset($this->request->get['product_id']) && empty($this->request->post['product_file_id'])) {

            $this->_error['file']['common'] = tt('Package file is required');

        } else if (!isset($this->request->post['product_file_id'])) {

            $this->_error['file']['common'] = tt('Package file input is wrong');
            $this->security_log->write('Wrong product package field');

        } else if (!file_exists(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $this->request->post['product_file_id'] . '.' . STORAGE_FILE_EXTENSION)) {

            $this->_error['file']['common'] = tt('Temporary package file is wrong');
            $this->security_log->write('Try to access not own\'s temporary package file');
        }

        // Demos
        if (isset($this->request->post['demo'])) {

            // Main Demo
            if (!isset($this->request->post['main_demo'])) {
                $this->_error['demo']['common'] = tt('Main demo is required');

                // Filter critical request
                $this->security_log->write('Wrong product main_demo field');
                unset($this->request->post['demo']);
            }

            $demo_count = 0;
            foreach ($this->request->post['demo'] as $row => $demo) {

                $demo_count++;

                // Title
                if (isset($demo['title'])) {
                    foreach ($demo['title'] as $language_id => $title) {

                        // Language
                        if (!$this->language->hasId($language_id)) {
                            $this->_error['demo']['common'] = tt('Wrong language field');

                            // Filter critical request
                            $this->security_log->write('Wrong product demo language_id field');
                            unset($this->request->post['demo'][$row]);
                            break;
                        }

                        // Title validation
                        if (empty($title)) {
                            $this->_error['demo'][$row]['title'][$language_id] = tt('Title is required');
                        } else if (!ValidatorProduct::titleValid(html_entity_decode($title))) {
                            $this->_error['demo'][$row]['title'][$language_id] = tt('Invalid title format');
                        }
                    }
                } else {
                    $this->_error['demo']['common'] = tt('Wrong title input');

                    // Filter critical request
                    $this->security_log->write('Wrong product demo title field');
                    unset($this->request->post['demo'][$row]);
                    break;
                }

                // Url
                if (isset($demo['url'])) {
                    if (empty($demo['url'])) {
                        $this->_error['demo'][$row]['url'] = tt('Demo URL is required');
                    } else if (!ValidatorProduct::urlValid(html_entity_decode($demo['url']))) {
                        $this->_error['demo'][$row]['url'] = tt('Invalid URL format');
                    }
                } else {
                    $this->_error['demo']['common'] = tt('Wrong demo URL input');

                    // Filter critical request
                    $this->security_log->write('Wrong product demo URL field');
                    unset($this->request->post['demo'][$row]);
                    break;
                }

                // Sort order
                if (!isset($demo['sort_order']) || !$demo['sort_order']) {
                    $this->_error['demo']['common'] = tt('Wrong sort order input');

                    // Filter critical request
                    $this->security_log->write('Wrong product demo sort_order field');
                    unset($this->request->post['demo'][$row]);
                    break;
                }
            }

            // Maximum demo pages per product
            if (QUOTA_DEMO_PER_PRODUCT < $demo_count) {
                $this->_error['demo']['common'] = sprintf(tt('Allowed maximum %s demo pages per one product'), QUOTA_DEMO_PER_PRODUCT);

                // Filter critical request
                $this->security_log->write('Exceeded limit of product demo');
                unset($this->request->post['demo']);
            }
        }

        // Images
        if (isset($this->request->post['image'])) {

            // Filter downloads (moved to AJAX)
            unset($this->request->files['image']);

            // Required main image
            if (!isset($this->request->post['main_image'])) {
                $this->_error['image']['common'] = tt('Main image is required');

                // Filter critical request
                $this->security_log->write('Wrong product main_image field');
                unset($this->request->post['image']);
            }

            $image_count = 0;
            foreach ($this->request->post['image'] as $row => $image) {

                $image_count++;

                // Title
                if (isset($image['title'])) {
                    foreach ($image['title'] as $language_id => $title) {

                        // Language
                        if (!$this->language->hasId($language_id)) {
                            $this->_error['image']['common'] = tt('Wrong language field');

                            // Filter critical request
                            $this->security_log->write('Wrong product image language_id field');
                            unset($this->request->post['image']);
                            break;
                        }

                        // Title validation
                        if (empty($title)) {
                            $this->_error['image'][$row]['title'][$language_id] = tt('Title is required');
                        } else if (!ValidatorProduct::titleValid(html_entity_decode($title))) {
                            $this->_error['image'][$row]['title'][$language_id] = tt('Invalid title format');
                        }
                    }
                } else {
                    $this->_error['image']['common'] = tt('Wrong title input');

                    // Filter critical request
                    $this->security_log->write('Wrong product image title field');
                    unset($this->request->post['image']);
                    break;
                }

                // Require sort order field
                if (!isset($image['sort_order']) || !$image['sort_order']) {
                    $this->_error['image']['common'] = tt('Wrong sort order input');

                    // Filter critical request
                    $this->security_log->write('Wrong product image sort_order field');
                    unset($this->request->post['image']);
                    break;
                }

                // Require product product_image_id
                if (!isset($image['product_image_id'])) {
                    $this->_error['image']['common'] = tt('Wrong temporary ID image input');

                    // Filter critical request
                    $this->security_log->write('Wrong product image product_image_id field');
                    unset($this->request->post['image']);
                    break;
                }

                // Require product product_image_id
                if (!isset($image['product_image_id'])) {
                    $this->_error['image']['common'] = tt('Wrong image ID input');

                    // Filter critical request
                    $this->security_log->write('Wrong product image product_image_id field');
                    unset($this->request->post['image']);
                    break;
                }

                // Check temporary image file if exists
                if (!file_exists(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . $image['product_image_id'] . '.' . STORAGE_IMAGE_EXTENSION)) {

                    $this->_error['image']['common'] = tt('Temporary image ID is wrong');
                    $this->security_log->write('Try to access not own\'s temporary image file');

                    unset($this->request->post['image']);
                    break;
                }

                // Check if new temporary and stored image fields is not empty
                if (isset($this->request->get['product_id']) && empty($image['product_image_id']) && empty($image['product_image_id'])) {
                    $this->_error['image']['common'] = tt('Image file is required');
                }
            }

            // Maximum images per one product
            if (QUOTA_IMAGES_PER_PRODUCT < $image_count) {
                $this->_error['image']['common'] = sprintf(tt('Maximum %s images pages per one product'), QUOTA_DEMO_PER_PRODUCT);

                // Filter critical request
                $this->security_log->write('Exceeded limit of product images');
                unset($this->request->post['image']);
            }
        }

        // Videos
        if (isset($this->request->post['video'])) {

            $video_count = 0;
            foreach ($this->request->post['video'] as $row => $video) {

                $video_count++;

                // Title
                if (isset($video['title'])) {
                    foreach ($video['title'] as $language_id => $title) {

                        // Language
                        if (!$this->language->hasId($language_id)) {
                            $this->_error['video']['common'] = tt('Wrong language field');

                            // Filter critical request
                            $this->security_log->write('Wrong product video language_id field');
                            unset($this->request->post['video'][$row]);
                            break;
                        }

                        // Title string validation
                        if (empty($title)) {
                            $this->_error['video'][$row]['title'][$language_id] = tt('Title is required');
                        } else if (!ValidatorProduct::titleValid(html_entity_decode($title))) {
                            $this->_error['video'][$row]['title'][$language_id] = tt('Invalid title format');
                        }
                    }
                } else {
                    $this->_error['video']['common'] = tt('Wrong title URL input');

                    // Filter critical request
                    $this->security_log->write('Wrong product video URL field');
                    unset($this->request->post['video'][$row]);
                    break;
                }

                // Source
                if (!isset($video['source'])) {
                    $this->_error['video']['common'] = tt('Wrong video source input');

                    // Filter critical request
                    $this->security_log->write('Wrong product video source field');
                    unset($this->request->post['video'][$row]);
                    break;

                } else {

                    // Video server validate
                    $video_server_info = $this->model_common_video_server->getVideoServer($video['source']);

                    if (!$video_server_info) {
                        $this->_error['video'][$row]['source'] = tt('Wrong video_server_id source');

                        // Filter critical request
                        $this->security_log->write('Wrong product video video_server_id field');
                        unset($this->request->post['video'][$row]);
                        break;

                    } else {

                        // ID relations validate
                        if (isset($video['id'])) {

                            switch (mb_strtolower($video_server_info->name)) {
                                case 'youtube':

                                    if (empty($video['id'])) {
                                        $this->_error['video'][$row]['id'] = tt('YouTube ID is required');
                                    } else if (!ValidatorYoutube::idValid(html_entity_decode($video['id']))) {
                                        $this->_error['video'][$row]['id'] = tt('Invalid YouTube ID format');
                                    }

                                    break;
                                case 'vimeo':
                                    if (empty($video['id'])) {
                                        $this->_error['video'][$row]['id'] = tt('YouTube Vimeo is required');
                                    } else if (!ValidatorVimeo::idValid(html_entity_decode($video['id']))) {
                                        $this->_error['video'][$row]['id'] = tt('Invalid Vimeo ID format');
                                    }
                                    break;
                                default:
                                    $this->_error['video'][$row]['source'] = tt('Undefined video source');
                            }
                        } else {
                            $this->_error['video']['common'] = tt('Wrong video ID input');

                            // Filter critical request
                            $this->security_log->write('Wrong product video ID field');
                            unset($this->request->post['video'][$row]);
                            break;
                        }
                    }
                }

                // Sort order
                if (!isset($video['sort_order']) || !$video['sort_order']) {
                    $this->_error['video']['common'] = tt('Wrong sort order input');

                    // Filter critical request
                    $this->security_log->write('Wrong product video sort_order field');
                    unset($this->request->post['video'][$row]);
                    break;
                }
            }

            // Maximum video pages per product
            if (QUOTA_VIDEO_PER_PRODUCT < $video_count) {
                $this->_error['video']['common'] = sprintf(tt('Maximum %s video links per one product'), QUOTA_DEMO_PER_PRODUCT);

                // Filter critical request
                $this->security_log->write('Exceeded limit of product videos');
                unset($this->request->post['video']);
            }
        }

        // Currency
        if (!isset($this->request->post['currency_id'])) {

            // Filter critical request
            $this->security_log->write('Wrong product currency field');
            $this->request->post['currency_id'] = $this->currency->getId();

        } else if (!$this->currency->hasId($this->request->post['currency_id'])) {
            $this->_error['price']['common'] = tt('Wrong currency field');

            // Filter critical request
            $this->security_log->write('Wrong product currency_id field');
            $this->request->post['currency_id'] = $this->currency->getId();

        } else if (empty($this->request->post['currency_id']) || $this->request->post['currency_id'] == 0) {
            $this->_error['price']['currency_id'] = tt('Currency is required');
        }

        // Withdraw address
        if (!isset($this->request->post['withdraw_address'])) {
            $this->_error['price']['withdraw_address'] = tt('Wrong withdraw address field');

            // Filter critical request
            $this->security_log->write('Wrong product withdraw_address field');
            $this->request->post['withdraw_address'] = false;

        } else if (empty($this->request->post['withdraw_address'])) {
            $this->_error['price']['withdraw_address'] = tt('Withdraw address is required');
        } else if (!ValidatorBitcoin::addressValid(html_entity_decode($this->request->post['withdraw_address']))) {
            $this->_error['price']['withdraw_address'] = tt('Invalid withdraw address');
        }

        // Pricing

        // Requirements
        if (!isset($this->request->post['regular_price'])) {

            $this->_error['price']['regular_price'] = tt('Wrong regular price field');

            // Filter critical request
            $this->security_log->write('Wrong regular price field');
            $this->request->post['regular_price'] = 0;
        }

        if (!isset($this->request->post['exclusive_price'])) {

            $this->_error['price']['exclusive_price'] = tt('Wrong exclusive price field');

            // Filter critical request
            $this->security_log->write('Wrong exclusive price field');
            $this->request->post['exclusive_price'] = 0;
        }

        // Regular price
        if (!empty($this->request->post['regular_price'])) {

            if ($this->request->post['regular_price'] < ALLOWED_PRODUCT_MIN_PRICE) {
                $this->_error['price']['regular_price'] = sprintf(tt('Price must be %s or more'), $this->currency->format(ALLOWED_PRODUCT_MIN_PRICE));
            } else if ($this->request->post['regular_price'] > ALLOWED_PRODUCT_MAX_PRICE) {
                $this->_error['price']['regular_price'] = sprintf(tt('Maximum price is %s'), $this->currency->format(ALLOWED_PRODUCT_MAX_PRICE));
            } else if (!ValidatorBitcoin::amountValid(html_entity_decode($this->request->post['regular_price']))) {
                $this->_error['price']['regular_price'] = tt('Invalid price format');
            }
        }

        // Exclusive price
        if (!empty($this->request->post['exclusive_price'])) {
            if ($this->request->post['exclusive_price'] < ALLOWED_PRODUCT_MIN_PRICE) {
                $this->_error['price']['exclusive_price'] = sprintf(tt('Price must be %s or more'), $this->currency->format(ALLOWED_PRODUCT_MIN_PRICE));
            } else if ($this->request->post['exclusive_price'] > ALLOWED_PRODUCT_MAX_PRICE) {
                $this->_error['price']['exclusive_price'] = sprintf(tt('Maximum price is %s'), $this->currency->format(ALLOWED_PRODUCT_MAX_PRICE));
            } else if (!ValidatorBitcoin::amountValid(html_entity_decode($this->request->post['exclusive_price']))) {
                $this->_error['price']['exclusive_price'] = tt('Invalid price format');
            }
        }

        // Logic validation
        if (empty($this->request->post['regular_price']) && empty($this->request->post['exclusive_price'])) {
            $this->_error['price']['regular_exclusive_price'] = tt('Regular or exclusive price is required');
        } else if ($this->request->post['regular_price'] == $this->request->post['exclusive_price']) {
            $this->_error['price']['regular_exclusive_price'] = tt('The regular and exclusive prices should not be the same');
        } else if ($this->request->post['exclusive_price'] && $this->request->post['regular_price'] > $this->request->post['exclusive_price']) {
            $this->_error['price']['regular_exclusive_price'] = tt('The regular price should not be greater than exclusive price');
        }

        // Special
        if (isset($this->request->post['special'])) {

            $special_count = 0;

            foreach ($this->request->post['special'] as $row => $special) {

                $special_count++;

                // Requirements
                if (!isset($special['regular_price'])) {

                    $this->_error['special'][$row]['regular_price'] = tt('Wrong regular price field');

                    // Filter critical request
                    $this->security_log->write('Wrong special regular price field');
                    $special['regular_price'] = 0;
                }

                if (!isset($special['exclusive_price'])) {

                    $this->_error['special'][$row]['price']['exclusive_price'] = tt('Wrong exclusive price field');

                    // Filter critical request
                    $this->security_log->write('Wrong special exclusive price field');
                    $special['exclusive_price'] = 0;
                }

                // Regular price
                if (!empty($special['regular_price'])) {

                    if ($special['regular_price'] < ALLOWED_PRODUCT_MIN_PRICE) {
                        $this->_error['special'][$row]['regular_price'] = sprintf(tt('Price must be %s or more'), $this->currency->format(ALLOWED_PRODUCT_MIN_PRICE));
                    } else if ($special['regular_price'] > ALLOWED_PRODUCT_MAX_PRICE) {
                        $this->_error['special'][$row]['regular_price'] = sprintf(tt('Maximum price is %s'), $this->currency->format(ALLOWED_PRODUCT_MAX_PRICE));
                    } else if (!ValidatorBitcoin::amountValid(html_entity_decode($special['regular_price']))) {
                        $this->_error['special'][$row]['regular_price'] = tt('Invalid price format');
                    }
                }

                // Exclusive price
                if (!empty($special['exclusive_price'])) {
                    if ($special['exclusive_price'] < ALLOWED_PRODUCT_MIN_PRICE) {
                        $this->_error['special'][$row]['exclusive_price'] = sprintf(tt('Price must be %s or more'), $this->currency->format(ALLOWED_PRODUCT_MIN_PRICE));
                    } else if ($special['exclusive_price'] > ALLOWED_PRODUCT_MAX_PRICE) {
                        $this->_error['special'][$row]['exclusive_price'] = sprintf(tt('Maximum price is %s'), $this->currency->format(ALLOWED_PRODUCT_MAX_PRICE));
                    } else if (!ValidatorBitcoin::amountValid(html_entity_decode($special['exclusive_price']))) {
                        $this->_error['special'][$row]['exclusive_price'] = tt('Invalid price format');
                    }
                }

                // Logic validation
                if (empty($special['regular_price']) && empty($special['exclusive_price'])) {
                    $this->_error['special'][$row]['regular_exclusive_price'] = tt('Regular or exclusive price is required');
                } else if ($special['regular_price'] == $special['exclusive_price']) {
                    $this->_error['special'][$row]['regular_exclusive_price'] = tt('The regular and exclusive prices should not be the same');
                } else if ($special['exclusive_price'] && $special['regular_price'] > $special['exclusive_price']) {
                    $this->_error['special'][$row]['regular_exclusive_price'] = tt('The regular price should not be greater than exclusive price');
                }

                // Date start
                if (!isset($special['date_start'])) {
                    $this->_error['special'][$row]['date_start'] = tt('Wrong date start input');

                    // Filter critical request
                    $this->security_log->write('Wrong product special date_start field');
                    unset($this->request->post['special'][$row]);
                    break;

                } else if (empty($special['date_start'])) {
                    $this->_error['special'][$row]['date_start'] = tt('Date start is required');
                } else if (!ValidatorProduct::dateValid(html_entity_decode($special['date_start']))) {
                    $this->_error['special'][$row]['date_start'] = tt('Invalid date format');
                }

                // Date end
                if (!isset($special['date_end'])) {
                    $this->_error['special'][$row]['date_end'] = tt('Wrong date end input');

                    // Filter critical request
                    $this->security_log->write('Wrong product special date_end field');
                    unset($this->request->post['special'][$row]);
                    break;

                } else if (empty($special['date_end'])) {
                    $this->_error['special'][$row]['date_end'] = tt('Date end is required');
                } else if (!ValidatorProduct::dateValid(html_entity_decode($special['date_end']))) {
                    $this->_error['special'][$row]['date_end'] = tt('Invalid date format');
                }

                // Logic validation
                if (strtotime($special['date_start']) >= strtotime($special['date_end'])) {
                    $this->_error['special'][$row]['date_end'] = tt('Date end should not begin prior to Date start');
                }

                // Sort order
                if (!isset($special['sort_order']) || !$special['sort_order']) {
                    $this->_error['special']['common'] = tt('Wrong sort order input');

                    // Filter critical request
                    $this->security_log->write('Wrong product special sort_order field');
                    unset($this->request->post['special'][$row]);
                }
            }

            // Maximum special pages per product
            if (QUOTA_SPECIALS_PER_PRODUCT < $special_count) {
                $this->_error['special']['common'] = sprintf(tt('Maximum %s specials per one product'), QUOTA_DEMO_PER_PRODUCT);

                // Filter critical request
                $this->security_log->write('Exceeded limit of product specials');
                unset($this->request->post['special']);
            }
        }

        return !$this->_error;
    }
}
