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

class ControllerCommonFooter extends Controller {

    public function index() {

        $data['user_is_logged'] = $this->auth->isLogged();

        $redirect = base64_encode($this->url->getCurrentLink($this->request->getHttps()));

        $data['href_catalog_search'] = $this->url->link('catalog/search', '&q=', 'SSL');
        $data['href_common_contact'] = $this->url->link('common/contact', '', 'SSL');

        $data['href_account_account_forgot'] = $this->url->link('account/account/forgot', 'redirect=' . $redirect, 'SSL');
        $data['href_account_account_create'] = $this->url->link('account/account/create', 'redirect=' . $redirect, 'SSL');
        $data['action_account_account_login'] = $this->url->link('account/account/login', 'redirect=' . $redirect, 'SSL');

        $data['href_common_information_about']  = $this->url->link('common/information/about');
        $data['href_common_information_licenses']  = $this->url->link('common/information/licenses');
        $data['href_common_information_terms']   = $this->url->link('common/information/terms');
        $data['href_common_information_faq']  = $this->url->link('common/information/faq');

        return $this->load->view('common/footer.tpl', $data);
    }
}
