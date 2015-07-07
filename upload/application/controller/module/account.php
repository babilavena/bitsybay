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

class ControllerModuleAccount extends Controller {

    public function index() {

        $data = array();

        $data['href_account_account'] = $this->url->link('account/account', '', 'SSL');
        $data['href_account_account_edit'] = $this->url->link('account/account/update', '', 'SSL');
        $data['href_account_account_logout'] = $this->url->link('account/account/logout', '', 'SSL');
        $data['href_account_account_verification'] = $this->url->link('account/account/verification', '', 'SSL');

        $data['verified'] = !$this->auth->isVerified();

        return $this->load->view('module/account.tpl', $data);
    }
}
