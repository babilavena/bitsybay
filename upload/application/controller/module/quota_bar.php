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

class ControllerModuleQuotaBar extends Controller {

    public function index($settings) {

        // Only for registered users
        if (!$this->auth->isLogged()) {
            return false;
        }

        $data            = array();
        $total_space     = $this->auth->getFileQuota();

        if (isset($settings['except_size'])) {
            $used_space  = $this->storage->getUsedSpace($this->auth->getId(), (float)$settings['except_size']);
        } else {
            $used_space  = $this->storage->getUsedSpace($this->auth->getId());
        }

        if (isset($settings['custom_file_space'])) {
            $file_space   = (float) $settings['custom_file_space'];
            $file_percent = ($file_space / $total_space) * 100;

        } else {
            $file_space   = 0;
            $file_percent = 0;

        }

        $available_space = $total_space - $used_space;
        $used_percent    = $used_space > 0 ? ($used_space / $total_space) * 100 : 0;

        switch (true) {
            case $used_percent <= 35:
                $data['class'] = 'success';
            break;
            case $used_percent <= 75 && $used_percent > 35:
                $data['class'] = 'warning';
            break;
            case $used_percent > 75:
                $data['class'] = 'danger';
            break;
            default:
                $data['class'] = false;
        }

        // $data['used_space']      = number_format($used_space, 2, '.', ' ');
        $data['file_space']      = number_format($file_space, 2, '.', ' ');
        $data['available_space'] = number_format($available_space, 2, '.', ' ');
        $data['total_space']     = number_format($total_space, 0, '.', ' ');
        $data['used_percent']    = $used_percent;
        $data['file_percent']    = $file_percent;

        return $this->load->view('module/quota_bar.tpl', $data);
    }
}
