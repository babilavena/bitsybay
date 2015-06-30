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

class ControllerCommonInformation extends Controller {

    public function about() {

        $this->document->setTitle(tt('About Us'));

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('About Us'), 'href' => $this->url->link('common/information/about'), 'active' => true),
        ));

        $this->response->setOutput($this->load->view('common/information/about.tpl', $data));
    }

    public function licenses() {

        $this->document->setTitle(tt('Licenses'));

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Licensing Policy'), 'href' => $this->url->link('common/information/licenses'), 'active' => true),
        ));

        $this->response->setOutput($this->load->view('common/information/licenses.tpl', $data));
    }

    public function terms() {

        $this->document->setTitle(tt('Terms of Service'));

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Terms of Service'), 'href' => $this->url->link('common/information/terms'), 'active' => true),
        ));

        $this->response->setOutput($this->load->view('common/information/terms.tpl', $data));
    }

    public function faq() {

        $this->document->setTitle(tt('General F.A.Q'));

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('General F.A.Q'), 'href' => $this->url->link('common/information/faq'), 'active' => true),
        ));

        $this->response->setOutput($this->load->view('common/information/faq.tpl', $data));
    }
}
