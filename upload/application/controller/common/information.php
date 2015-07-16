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

        $data['definitions'] = $this->load->controller('common/information/licensesCommon');
        $data['regular']     = $this->load->controller('common/information/licensesRegular');
        $data['exclusive']   = $this->load->controller('common/information/licensesExclusive');

        $this->response->setOutput($this->load->view('common/information/license/layout.tpl', $data));
    }

    public function licensesCommon() {
        return $this->load->view('common/information/license/common.tpl');
    }

    public function licensesRegular() {
        return $this->load->view('common/information/license/regular.tpl');
    }

    public function licensesExclusive() {
        return $this->load->view('common/information/license/exclusive.tpl');
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

    public function team() {

        $this->document->setTitle(tt('Team'));

        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Team'), 'href' => $this->url->link('common/information/team'), 'active' => true),
        ));


        // Contributors list
        $github = curl_init(GITHUB_API_URL_CONTRIBUTORS);
        curl_setopt($github, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($github, CURLOPT_USERAGENT, PROJECT_NAME);
        curl_setopt($github, CURLOPT_RETURNTRANSFER, true);

        $contributors = curl_exec($github);

        $data['contributions'] = 0;
        $data['contributors']  = array();

        if($contributors) {
            foreach (json_decode($contributors, true) as $contributor) {
                if (isset($contributor['id'])) {
                    $data['contributions'] += $contributor['contributions'];
                    $data['contributors'][] = array(
                        'username'      => $contributor['login'],
                        'href_avatar'   => $contributor['avatar_url'],
                        'href_profile'  => $contributor['html_url'],
                        'contributions' => $contributor['contributions']
                    );
                } else if (isset($contributor['message'])) {
                    $this->security_log->write($contributor['message']);
                }
            }
        }

        $this->response->setOutput($this->load->view('common/information/team.tpl', $data));
    }
}
