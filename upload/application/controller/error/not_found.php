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

class ControllerErrorNotFound extends Controller {

    public function index() {

        // Load dependencies
        $this->load->model('catalog/category');
        $this->load->model('common/log');

        $data = array();

        $this->document->setTitle(tt('Page not found'));

        // Generate categories menu

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

        // Add error to the log
        $this->model_common_log->createLog404($this->auth->getId(), $this->request->getRequestString(), $this->request->getRequestReferrer());

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_catalog_search'] = $this->url->link('catalog/search');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => true),
        ));

        $this->response->addHeader($this->request->getServerProtocol() . '/1.1 404 Not Found');
        $this->response->setOutput($this->load->view('error/not_found.tpl', $data));
    }
}
