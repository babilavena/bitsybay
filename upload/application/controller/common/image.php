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

class ControllerCommonImage extends Controller {

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->library('qr');
        $this->load->helper('validator/bitcoin');
    }

    public function qr() {

        // Only for registered users
        if (!$this->auth->isLogged()) {
            $this->security_log->write('Somebody tried to get QR Code');
            exit;
        }

        // Request validation
        if (!isset($this->request->get['code']) || empty($this->request->get['code'])) {
            $this->security_log->write('Query is required');
            exit;
        }

        // Request validation
        if (!ValidatorBitcoin::addressValid($this->request->get['code'])) {
            $this->security_log->write('Invalid bitcoin address');
            exit;
        }

        $qr = new Qr($this->request->get['code']);

        header('Content-type: image/png');
        echo $qr->image();
        exit;
    }
}
