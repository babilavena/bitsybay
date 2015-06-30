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

class ControllerCommonAlertWarning extends Controller {

    public function index() {

        $message = $this->session->getUserMessage();

        if (isset($message['warning']) && $message['warning']) {

            $this->session->setUserMessage(array('warning' => false));
            return $this->load->view('common/alert/warning.tpl', array('content' => $message['warning']));

        } else {
            return false;
        }
    }
}
