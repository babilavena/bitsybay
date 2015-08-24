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

class ControllerCommonContact extends Controller {

    private $_error = array();

    public function __construct($registry) {

        parent::__construct($registry);

    }

    public function index() {

        $data['email']   = isset($this->request->post['email']) ? $this->request->post['email'] : ($this->auth->isLogged() ? $this->auth->getEmail() : false);
        $data['subject'] = isset($this->request->post['subject']) ? $this->request->post['subject'] : false;
        $data['message'] = isset($this->request->post['message']) ? $this->request->post['message'] : false;

        if ('POST' == $this->request->getRequestMethod() && $this->_validatePost()) {

            $this->mail->setTo(MAIL_EMAIL_SUPPORT_ADDRESS);
            $this->mail->setReplyTo($this->request->post['email']);
            $this->mail->setSubject($this->request->post['subject']);
            $this->mail->setText($this->request->post['message']);
            $this->mail->send();

            $this->session->setUserMessage(array('success' => tt('Your message was sent successfully!')));

            $data['subject'] = false;
            $data['message'] = false;
        }

        $this->document->setTitle(tt('Contact Us'));

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Contact Us'), 'href' => $this->url->link('common/contact'), 'active' => true),
        ));

        $data['error']  = $this->_error;

        $data['href_common_information_licenses']  = $this->url->link('common/information/licenses');
        $data['href_common_information_terms']     = $this->url->link('common/information/terms');
        $data['href_common_information_faq']       = $this->url->link('common/information/faq');

        $data['action'] = $this->url->link('common/contact');

        $data['alert_success']  = $this->load->controller('common/alert/success');

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/contact.tpl', $data));
    }

    private function _validatePost() {

        if (!isset($this->request->post['email']) || empty($this->request->post['email'])) {
            $this->_error['email'] = tt('Email is required');
        }

        if (!isset($this->request->post['subject']) || empty($this->request->post['subject'])) {
            $this->_error['subject'] = tt('Subject is required');
        }

        if (!isset($this->request->post['message']) || empty($this->request->post['message'])) {
            $this->_error['message'] = tt('Message is required');
        }

        return !$this->_error;
    }
}
