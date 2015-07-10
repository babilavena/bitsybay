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

class ControllerModuleSearch extends Controller {

    public function index($settings) {

        // Load dependencies
        $this->load->model('catalog/tag');

        // Hide block to next pages
        if (isset($this->request->get['favorites']) || isset($this->request->get['purchased']) || isset($this->request->get['user_id'])) {
            return false;
        }

        // Set class
        if (isset($settings['class'])) {
            $data['class'] = $settings['class'];
        } else {
            $data['class'] = 'col-lg-12';
        }

        $tags = $this->model_catalog_tag->getTags(array('limit' => 5), $this->language->getId());

        $data['tags'] = array();
        foreach ($tags as $tag) {
            $data['tags'][] = array(
                'tag_id' => $tag->tag_id,
                'name'   => $tag->name,
                'url'    => $this->url->link('catalog/search', 'q=' . urlencode($tag->name)),
            );
        }

        // Filter by search term & tags
        if (isset($this->request->get['q']) && !empty($this->request->get['q']) && ValidatorProduct::titleValid($this->request->get['q'])) {
            $data['query'] = $this->request->get['q'];
        } else {
            $data['query'] = false;
        }

        $data['action'] = $this->url->link('catalog/search', 'q=');

        return $this->load->view('module/search.tpl', $data);
    }
}
