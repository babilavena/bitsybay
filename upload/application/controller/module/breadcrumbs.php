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

class ControllerModuleBreadcrumbs extends Controller {

    public function index(array $breadcrumbs = array()) {

        $data = array();
        $data['breadcrumbs'] = array();

        foreach ($breadcrumbs as $breadcrumb) {
            $data['breadcrumbs'][] = array(
                'name'   => $breadcrumb['name'],
                'href'   => $breadcrumb['href'],
                'active' => $breadcrumb['active']);
        }

        return $this->load->view('module/breadcrumbs.tpl', $data);
    }
}
