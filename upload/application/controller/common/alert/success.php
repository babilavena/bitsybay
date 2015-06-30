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

class ControllerCommonAlertSuccess extends Controller {

    public function index() {

        $message = $this->session->getUserMessage();

        if (isset($message['success']) && $message['success']) {

            $this->session->setUserMessage(array('success' => false));
            return $this->load->view('common/alert/success.tpl', array('content' => $message['success']));

        } else {
            return false;
        }
    }
}
