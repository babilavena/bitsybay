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

class ControllerModuleSeller extends Controller {

    public function index() {

        $data = array();

        $data['href_account_product'] = $this->url->link('account/product');

        return $this->load->view('module/seller.tpl', $data);
    }
}
