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

class ControllerModuleVerification extends Controller {

    public function index() {

        $data = array();
        $data['alert_warning'] = false;

        // Verification required
        if (!$this->auth->isApproved()) {
            $data['alert_warning'] = $this->load->controller('common/alert/warning', tt('You need to verify your email address. Please, check your new mailbox and follow the link below to complete.'));
        }

        return $this->load->view('module/verification.tpl', $data);
    }
}
