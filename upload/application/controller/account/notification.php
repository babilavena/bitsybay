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

class ControllerAccountNotification extends Controller
{

    public function __construct($registry)
    {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('account/notification');
    }


    public function index() {

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->link('account/notification'))));
        }

        $this->document->setTitle(tt('Notifications'));

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $notifications = $this->model_account_notification->getNotifications($this->auth->getId(),
                                                                             $this->language->getId(),
                                                                             isset($this->request->get['all']) ? array() : array('read' => 0));

        $data['notifications'] = array();
        foreach ($notifications as $notification) {



            $data['notifications'][] = array(
                'title'       => $notification->title,
                'read'        => $notification->read,
                'label'       => $this->_prepareLabel($notification->label),
                'date_added'  => date(tt('Y.m.d H:i'), strtotime($notification->date_added)),
                'href'        => $this->url->link('account/notification/read', 'notification_id=' . $notification->user_notification_id),
            );
        }

        $data['module_notification'] = $this->load->controller('module/notification');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Notifications'), 'href' => $this->url->link('account/notification'), 'active' => true)
            ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/notification/notification_list.tpl', $data));
    }

    public function read() {

        // Check for required variables
        if (!isset($this->request->get['notification_id'])) {
            $this->response->redirect($this->url->link('account/notification'));
        }

        // Set current notification
        $notification_id = (int) $this->request->get['notification_id'];

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->link('account/notification/read', 'notification_id=' . $notification_id))));
        }

        // Check the access
        if (!$notification = $this->model_account_notification->getNotification($notification_id, $this->auth->getId(), $this->language->getId())) {
            $this->response->redirect($this->url->link('account/notification'));
        }

        $this->document->setTitle(tt('Notifications'));

        $data['title']       = $notification->title;
        $data['description'] = $notification->description;
        $data['label']       = $this->_prepareLabel($notification->label);
        $data['date_added']  = date(tt('Y.m.d H:i'), strtotime($notification->date_added));

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_notification'] = $this->load->controller('module/notification');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Notifications'), 'href' => $this->url->link('account/notification'), 'active' => false),
                    array('name' => tt('Read'), 'href' => $this->url->link('account/notification/read', 'notification_id=' . $notification_id), 'active' => true)
            ));

        $this->model_account_notification->setReadStatus($notification_id, $this->auth->getId(), 1);

        // Renter the template
        $this->response->setOutput($this->load->view('account/notification/notification_read.tpl', $data));
    }

    private function _prepareLabel($label) {

        switch ($label) {
            case 'activity':
                $label = array(
                    'name'  => tt('activity'),
                    'class' => tt('label-success')
                );
                break;

            case 'news':
                $label = array(
                    'name'  => tt('news'),
                    'class' => tt('label-primary')
                );
                break;

            case 'security':
                $label = array(
                    'name'  => tt('security'),
                    'class' => tt('label-danger')
                );
                break;

            case 'billing':
                $label = array(
                    'name'  => tt('billing'),
                    'class' => tt('label-warning')
                );
                break;

            default:
                $label = array(
                    'name'  => tt('common'),
                    'class' => tt('label-info')
                );
        }

        return $label;
    }
}
