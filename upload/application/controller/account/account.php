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

class ControllerAccountAccount extends Controller {

    private $_error = array();

    public function __construct($registry) {

        parent::__construct($registry);

        // Load dependencies
        $this->load->model('account/user');
        $this->load->model('account/notification');
        $this->load->model('account/subscription');
        $this->load->helper('validator/user');
        $this->load->helper('validator/upload');
        $this->load->library('bitcoin');
        $this->load->library('identicon');
        $this->load->library('captcha/captcha');
    }

    public function index() {

        $this->document->setTitle(tt('Profile'));

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login'));
        }

        $data = array();

        $data['current_ip']    = $this->request->getRemoteAddress();
        $data['last_ip']       = $this->auth->getLastIP();
        $data['approved']      = $this->auth->isApproved();
        $data['verified']      = $this->auth->isVerified();
        $data['active']        = $this->auth->isActive();
        $data['username']      = $this->auth->getUsername();
        $data['date_added']    = date(DATE_FORMAT_DEFAULT, strtotime($this->auth->getDateAdded()));
        $data['avatar_url']    = $this->cache->image('thumb', $this->auth->getId(), 100, 100);

        $data['avatar_action']                     = $this->url->link('account/account/uploadAvatar');
        $data['href_catalog_search_favorites']     = $this->url->link('catalog/search', 'favorites=1');
        $data['href_catalog_search_purchased']     = $this->url->link('catalog/search', 'purchased=1');
        $data['href_account_account_update']       = $this->url->link('account/account/update');
        $data['href_account_product_create']       = $this->url->link('account/product');
        $data['href_account_account_verification'] = $this->url->link('account/account/verification');

        $data['module_account']  = $this->load->controller('module/account');
        $data['module_billing']  = $this->load->controller('module/billing');
        $data['module_buyer']    = $this->load->controller('module/buyer');
        $data['module_seller']   = $this->load->controller('module/seller');

        $data['alert_danger']    = $this->load->controller('common/alert/danger');
        $data['alert_success']   = $this->load->controller('common/alert/success');
        $data['alert_warning']   = $this->load->controller('common/alert/warning');

        $data['footer']          = $this->load->controller('common/footer');
        $data['header']          = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Profile'), 'href' => $this->url->link('account/account'), 'active' => false),
        ));

        $data['module_quota_bar']    = $this->load->controller('module/quota_bar');

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/account.tpl', $data));
    }

    public function create() {
        $this->document->addScript('/javascript/bootstrap-datepicker.js');

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account'));
        }

        // Validate & save incoming data
        if ('POST' == $this->request->getRequestMethod() && $this->_validateCreate()) {

            // Generate email approval link
            $approval_code = md5(rand() . microtime() . $this->request->post['email']);

            // Create new user
            if ($user_id = $this->model_account_user->createUser(  $this->request->post['username'],
                                                                   $this->request->post['email'],
                                                                   $this->request->post['password'],
                                                                   1, // Is buyer
                                                                   1, // Is seller
                                                                   NEW_USER_STATUS,
                                                                   NEW_USER_VERIFIED,
                                                                   QUOTA_FILE_SIZE_BY_DEFAULT,
                                                                   $approval_code)) {

                // Clear previous login attempts for unregistered accounts.
                $this->model_account_user->deleteLoginAttempts($this->request->post['email']);

                // Try to login
                if ($this->auth->login($this->request->post['email'], $this->request->post['password'], true)) {

                    // Generate identicon
                    $identicon = new Identicon();
                    $image     = new Image($identicon->generateImageResource(sha1($this->request->post['username']),
                                                                             USER_IMAGE_ORIGINAL_WIDTH,
                                                                             USER_IMAGE_ORIGINAL_HEIGHT,
                                                                             IMG_FILTER_GRAYSCALE), true);

                    $image->save(DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . 'thumb.' . STORAGE_IMAGE_EXTENSION);

                    // Subscription
                    $subscriptions = $this->model_account_subscription->getSubscriptions($this->language->getId());

                    foreach ($subscriptions as $subscription) {
                        $this->model_account_subscription->addUserSubscription($this->auth->getId(), $subscription->subscription_id);
                    }

                    // Add welcome notification
                    $this->model_account_notification->addNotification($user_id,
                                                                       DEFAULT_LANGUAGE_ID,
                                                                       'common',
                                                                       sprintf(tt('Welcome to %s!'), PROJECT_NAME),
                                                                       tt("We're so happy you've joined us.\n") .
                                                                       tt("Make every day awesome with inspired finds!"));

                    // Send greetings email with verification code
                    $mail_data['project_name'] = PROJECT_NAME;

                    $mail_data['subject'] = sprintf(tt('Welcome to %s!'), PROJECT_NAME);
                    $mail_data['message'] = tt('Welcome and thank you for registering!');

                    $mail_data['href_home']         = $this->url->link('common/home');
                    $mail_data['href_contact']      = $this->url->link('common/contact');
                    $mail_data['href_subscription'] = $this->url->link('account/account/subscription');
                    $mail_data['href_approve']      = $this->url->link('account/account/approve', 'code=' . $approval_code);

                    $mail_data['href_facebook'] = URL_FACEBOOK;
                    $mail_data['href_twitter']  = URL_TWITTER;
                    $mail_data['href_tumblr']   = URL_TUMBLR;
                    $mail_data['href_github']   = URL_GITHUB;

                    $mail_data['module'] = $this->load->view('email/module/approve.tpl', $mail_data);

                    $this->mail->setTo($this->request->post['email']);
                    $this->mail->setSubject($mail_data['subject']);
                    $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                    $this->mail->send();

                    $this->response->redirect($this->url->link('account/account'));
                }
            }
        }

        // Set view variables
        $this->document->setTitle(tt('Create an Account'));

        $data = array();

        $data['error'] = $this->_error;

        $captcha = new Captcha();
        $this->session->setCaptcha($captcha->getCode());

        $data['captcha'] = $this->url->link('account/account/captcha');
        $data['action']  = $this->url->link('account/account/create', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false);

        $data['href_account_account_login']       = $this->url->link('account/account/login');
        $data['href_account_account_forgot']      = $this->url->link('account/account/forgot');
        $data['href_common_information_terms']    = $this->url->link('common/information/terms');
        $data['href_common_information_licenses'] = $this->url->link('common/information/licenses');
        $data['href_common_information_faq']      = $this->url->link('common/information/faq');
        $data['href_common_contact']              = $this->url->link('common/contact');

        $data['username'] = isset($this->request->post['username']) ? $this->request->post['username'] : false;
        $data['email']    = isset($this->request->post['email'])    ? $this->request->post['email']    : false;
        $data['accept']   = isset($this->request->post['accept'])   ? $this->request->post['accept']   : false;

        $data['alert_danger'] = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href'  => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
            array('name' => tt('Create'), 'href' => $this->url->link('account/account/create'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/create.tpl', $data));
    }

    public function update() {

        // Init
        $data = array();

        // Redirect if user is already logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login'));
        }

        // Set variables
        $user = $this->model_account_user->getUser($this->auth->getId());


        // Validate & save incoming data
        if ('POST' == $this->request->getRequestMethod() && $this->_validateUpdate()) {

            // Generate email approval link
            $approval_code = md5(rand() . microtime() . $this->request->post['email']);

            // Create new user
            if ($this->model_account_user->updateUser($this->auth->getId(),
                                                      $this->request->post['username'],
                                                      $this->request->post['email'],
                                                      $this->request->post['password'],
                                                      $approval_code)) {

                // Success alert
                $this->session->setUserMessage(array('success' => tt('Well done! You have successfully modified account settings!')));

                // Add notification about new account settings
                $this->model_account_notification->addNotification($this->auth->getId(),
                                                                   DEFAULT_LANGUAGE_ID,
                                                                   'security',
                                                                   tt('Your account settings has been updated'),
                                                                   tt('If you did not make this change and believe your account has been compromised, please contact us.'));



                // If subscription enabled
                if ($this->model_account_subscription->checkUserSubscription($this->auth->getId(), SECURITY_ACCOUNT_SUBSCRIPTION_ID)) {

                    // Send mail
                    $mail_data['project_name'] = PROJECT_NAME;

                    $mail_data['subject'] = sprintf(tt('Your account settings has been updated - %s'), PROJECT_NAME);
                    $mail_data['message'] = tt('Your account settings has been updated.') . ' ';
                    $mail_data['message'].= tt('If you did not make this change, please contact us.');

                    $mail_data['href_home']         = $this->url->link('common/home');
                    $mail_data['href_contact']      = $this->url->link('common/contact');
                    $mail_data['href_subscription'] = $this->url->link('account/account/subscription');

                    $mail_data['href_facebook'] = URL_FACEBOOK;
                    $mail_data['href_twitter']  = URL_TWITTER;
                    $mail_data['href_tumblr']   = URL_TUMBLR;
                    $mail_data['href_github']   = URL_GITHUB;

                    $this->mail->setTo($user->email);
                    $this->mail->setSubject($mail_data['subject']);
                    $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                    $this->mail->send();
                }

                // If old and new email is not match or email is not exist
                if ($this->request->post['email'] != $this->auth->getEmail() || !$this->model_account_user->checkEmail($this->request->post['email'])) {

                    // Send email verification code
                    $mail_data['project_name'] = PROJECT_NAME;

                    $mail_data['subject'] = sprintf(tt('Your email address has been changed - %s'), PROJECT_NAME);
                    $mail_data['message'] = tt('Your email address has been successfully changed!');

                    $mail_data['href_home']         = $this->url->link('common/home');
                    $mail_data['href_contact']      = $this->url->link('common/contact');
                    $mail_data['href_subscription'] = $this->url->link('account/account/subscription');
                    $mail_data['href_approve']      = $this->url->link('account/account/approve', 'code=' . $approval_code);

                    $mail_data['href_facebook'] = URL_FACEBOOK;
                    $mail_data['href_twitter']  = URL_TWITTER;
                    $mail_data['href_tumblr']   = URL_TUMBLR;
                    $mail_data['href_github']   = URL_GITHUB;

                    $mail_data['module'] = $this->load->view('email/module/approve.tpl', $mail_data);

                    $this->mail->setTo($user->email);
                    $this->mail->setSubject($mail_data['subject']);
                    $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                    $this->mail->send();

                    // Success alert
                    $this->session->setUserMessage(array(
                        'success' => tt('You have successfully modified account settings!'),
                        'warning' => tt('You have successfully modified account settings! Please, check your mailbox to approve the new email address.')
                    ));
                }

                $this->response->redirect($this->url->link('account/account/update'));
            }
        }

        // Set headers
        $this->document->setTitle(tt('Account Edit'));

        $data['email']    = isset($this->request->post['email']) ? $this->request->post['email'] : $user->email;
        $data['username'] = isset($this->request->post['username']) ? $this->request->post['username'] : $user->username;

        // Errors
        $data['error'] = $this->_error;

        // JS
        $data['username_max_lenght'] = ValidatorUser::getUsernameMaxLength();

        // Links
        $data['action'] = $this->url->link('account/account/update');

        // Load modules
        $data['module_account'] = $this->load->controller('module/account');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['alert_danger'] = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');
        $data['alert_warning'] = $this->load->controller('common/alert/warning');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
            array('name' => tt('Settings'), 'href' => $this->url->link('account/account/update'), 'active' => true),
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/update.tpl', $data));

    }

    public function subscription() {

        // Set headers
        $this->document->setTitle(tt('Subscriptions'));

        // Redirect if user is already logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login'));
        }

        // Save incoming settings
        if ('POST' == $this->request->getRequestMethod()) {

            // Remove old settings
            $this->model_account_subscription->deleteUserSubscriptions($this->auth->getId());

            // If new settings exist
            if (isset($this->request->post['subscription']) && is_array($this->request->post['subscription'])) {

                foreach ($this->request->post['subscription'] as $subscription_id => $value) {

                    // Validate subscription ID
                    if ($this->model_account_subscription->checkSubscription((int) $subscription_id)) {

                        // Add new settings
                        $this->model_account_subscription->addUserSubscription($this->auth->getId(), (int) $subscription_id);
                    }
                }
            }

            // Success alert
            $this->session->setUserMessage(array('success' => tt('You have successfully modified your subscriptions!')));
        }

        // Init
        $data = array();

        // Form
        $subscriptions = $this->model_account_subscription->getSubscriptions($this->language->getId());

        foreach ($subscriptions as $subscription) {
            $data['subscriptions'][$subscription->group][] = array(
                'subscription_id' => $subscription->subscription_id,
                'title'           => $subscription->title,
                'label'           => $subscription->label,
                'active'          => isset($this->request->post['subscription'][$subscription->subscription_id]) ? true : $this->model_account_subscription->checkUserSubscription($this->auth->getId(), $subscription->subscription_id),
            );
        }

        // Errors
        $data['error'] = $this->_error;

        // Links
        $data['action'] = $this->url->link('account/account/subscription');

        // Load modules
        $data['module_account'] = $this->load->controller('module/account');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['alert_danger']  = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
            array('name' => tt('Subscriptions'), 'href' => $this->url->link('account/account/subscription'), 'active' => true),
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/subscription.tpl', $data));

    }

    public function login() {

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account'));
        }

        $this->document->setTitle(tt('Sign In'));

        if ('POST' == $this->request->getRequestMethod() && $this->_validateLogin()) {

            if (isset($this->request->get['redirect'])) {
                $this->response->redirect(base64_decode($this->request->get['redirect']));
            } else {
                $this->response->redirect($this->url->link('account/account'));
            }
        }

        $data = array();

        $data['href_account_forgot'] = $this->url->link('account/account/forgot');

        $data['error']  = $this->_error;
        $data['action'] = $this->url->link('account/account/login', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false);

        $data['href_account_account_create'] = $this->url->link('account/account/create');
        $data['href_account_account_forgot'] = $this->url->link('account/account/forgot');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['login']    = isset($this->request->post['login']) ? $this->request->post['login'] : false;
        $data['password'] = isset($this->request->post['password']) ? $this->request->post['password'] : false;

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href'  => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
            array('name' => tt('Sign In'), 'href' => $this->url->link('account/account/login'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/login.tpl', $data));
    }

    public function logout() {

        // Validate login status
        if ($this->auth->isLogged()) {
            if ($this->auth->logout()) {
                $this->response->redirect(isset($this->request->get['redirect']) ? base64_decode($this->request->get['redirect']) : $this->url->link('common/home'));
            } else {
                $this->security_log->write('Can not logout');
            }
        }
    }

    public function reset() {

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account'));
        }

        // Redirect to step one if code not exist
        if (!isset($this->request->get['code'])) {
            $this->security_log->write('Undefined verification code.');
            $this->response->redirect($this->url->link('account/account/forgot'));
        }

        // If reset code is not valid
        if (!$user_id = $this->model_account_user->getPasswordReset($this->request->get['code'])) {
            $this->security_log->write('Code is either invalid, expired or reached it\'s usage limit.');
            $this->session->setUserMessage(array('danger' => tt('Your code is either invalid or expired!')));
            $this->response->redirect($this->url->link('account/account/forgot'));
        }

        // Processing incoming data
        if ('POST' == $this->request->getRequestMethod() && $this->_validateReset()) {

            // Get user info
            $user = $this->model_account_user->getUser($user_id);

            // Reset password and login
            if ($this->model_account_user->updatePassword($user->user_id, $this->request->post['password']) &&
                $this->auth->login($user->username, $this->request->post['password'], false)) {

                // Clear all password requests
                $this->model_account_user->deletePasswordReset($user->user_id);

                // If subscription enabled
                if ($this->model_account_subscription->checkUserSubscription($user->user_id, SECURITY_ACCOUNT_SUBSCRIPTION_ID)) {

                    // Send mail
                    $mail_data['project_name'] = PROJECT_NAME;

                    $mail_data['subject'] = sprintf(tt('Your password has been updated - %s'), PROJECT_NAME);
                    $mail_data['message'] = sprintf(tt('You recently changed the password associated with your @%s account.'), $user->username);
                    $mail_data['message'].= tt('If you did not make this change, please contact us.');

                    $mail_data['href_home']         = $this->url->link('common/home');
                    $mail_data['href_contact']      = $this->url->link('common/contact');
                    $mail_data['href_subscription'] = $this->url->link('account/account/subscription');

                    $mail_data['href_facebook'] = URL_FACEBOOK;
                    $mail_data['href_twitter']  = URL_TWITTER;
                    $mail_data['href_tumblr']   = URL_TUMBLR;
                    $mail_data['href_github']   = URL_GITHUB;

                    $this->mail->setTo($user->email);
                    $this->mail->setSubject($mail_data['subject']);
                    $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
                    $this->mail->send();
                }

                // Add notification
                $this->model_account_notification->addNotification($user->user_id,
                                                                   DEFAULT_LANGUAGE_ID,
                                                                   'security',
                                                                   tt('Your password has been updated'),
                                                                   tt('If you did not make this change and believe your account has been compromised, please contact us.'));

                // Set success message
                $this->session->setUserMessage(array('success' => tt('Your new password has been successfully changed!')));

                // Redirect to the login page
                $this->response->redirect($this->url->link('account/account'));
            }
        }

        $this->document->setTitle(tt('Password reset'));

        $data = array();

        $data['error']  = $this->_error;
        $data['action'] = $this->url->link('account/account/reset', 'code=' . $this->request->get['code']);
        $data['href_account_account_create'] = $this->url->link('account/account/create');
        $data['href_account_account_login']  = $this->url->link('account/account/login');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['password'] = isset($this->request->post['password']) ? $this->request->post['password'] : false;
        $data['confirm']  = isset($this->request->post['confirm'])  ? $this->request->post['confirm']  : false;

        $data['alert_danger']  = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
                    array('name' => tt('Password reset'), 'href' => $this->url->link('account/account/reset', 'code=' . $this->request->get['code']), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/reset.tpl', $data));
    }

    public function forgot() {

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account'));
        }

        $this->document->setTitle(tt('Account recovery'));

        $data = array();

        if ('POST' == $this->request->getRequestMethod() && $this->_validateForgot()) {

            // Get user info
            $user = $this->model_account_user->getUserByEmail($this->request->post['email']);

            // Generate temporary code
            $reset_code = sha1($user->salt . microtime() . rand());

            // Register new password request
            $this->model_account_user->addPasswordReset($user->user_id, $this->request->getRemoteAddress(), $reset_code);

            // Send email verification code
            $mail_data['project_name'] = PROJECT_NAME;

            $mail_data['subject'] = sprintf(tt('New password request - %s'), PROJECT_NAME);
            $mail_data['message'] = sprintf(tt('Somebody requested a new password for your account. If you did not make this change please contact us.'), $user->username);

            $mail_data['href_home']         = $this->url->link('common/home');
            $mail_data['href_contact']      = $this->url->link('common/contact');
            $mail_data['href_subscription'] = $this->url->link('account/account/subscription');
            $mail_data['href_change']       = $this->url->link('account/account/reset', 'code=' . $reset_code);

            $mail_data['href_facebook'] = URL_FACEBOOK;
            $mail_data['href_twitter']  = URL_TWITTER;
            $mail_data['href_tumblr']   = URL_TUMBLR;
            $mail_data['href_github']   = URL_GITHUB;

            $mail_data['module'] = $this->load->view('email/module/forgot.tpl', $mail_data);

            $this->mail->setTo($user->email);
            $this->mail->setSubject($mail_data['subject']);
            $this->mail->setHtml($this->load->view('email/common.tpl', $mail_data));
            $this->mail->send();

            $this->session->setUserMessage(array('success' => tt('Further instructions have been sent to your e-mail address!')));
        }

        $data['error']  = $this->_error;
        $data['action'] = $this->url->link('account/account/forgot', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false);
        $data['href_account_account_create'] = $this->url->link('account/account/create');
        $data['href_account_account_login'] = $this->url->link('account/account/login');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['email']    = isset($this->request->post['email']) ? $this->request->post['email'] : false;

        $data['alert_success'] = $this->load->controller('common/alert/success');
        $data['alert_danger']  = $this->load->controller('common/alert/danger');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
                    array('name' => tt('Recovery'), 'href' => $this->url->link('account/account/forgot'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/forgot.tpl', $data));
    }

    public function verification() {

        // Redirect if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->getCurrentLink())));
        }

        // Redirect if user is already verified
        if ($this->auth->isVerified()) {
            $this->response->redirect($this->url->link('account/account'));
        }

        $this->document->setTitle(tt('Account verification'));

        $data = array();
        $code = md5(PROJECT_NAME . $this->auth->getId());

        // Create a new BitCoin Address
        try {
            $bitcoin = new BitCoin(BITCOIN_RPC_USERNAME, BITCOIN_RPC_PASSWORD, BITCOIN_RPC_HOST, BITCOIN_RPC_PORT);
            $address = $bitcoin->getaccountaddress(BITCOIN_USER_VERIFICATION_PREFIX . $this->auth->getId());
        } catch (Exception $e) {
            $this->security_log->write('BitCoin connection error ' . $e->error);
            exit;
        }

        if ('POST' == $this->request->getRequestMethod() && $this->_validateVerification()) {

            // Save verification request into the DB
            if ($this->model_account_user->addVerificationRequest($this->auth->getId(),
                                                                  $this->currency->getId(),
                                                                  'pending',
                                                                  $address,
                                                                  $code,
                                                                  $this->request->post['proof'])) {

                // Add notification
                $this->model_account_notification->addNotification($this->auth->getId(),
                                                                   DEFAULT_LANGUAGE_ID,
                                                                   'common',
                                                                   tt('Your verification request was sent successfully'),
                                                                   tt('We will process the request as quickly as possible.'));

                // Admin alert
                $this->mail->setTo(MAIL_EMAIL_SUPPORT_ADDRESS);
                $this->mail->setSubject(sprintf(tt('Account Verification Request - %s'), PROJECT_NAME));
                $this->mail->setText(tt('A new verification was requested.'));
                $this->mail->send();

                // Success message
                $this->session->setUserMessage(array('success' => tt('Your verification request was sent successfully!')));

            } else {

                // Something wrong
                $this->session->setUserMessage(array('danger' => tt('Undefined error! Please contact us.')));
            }
        }

        $data['error']     = $this->_error;
        $data['action']    = $this->url->link('account/account/verification');

        $data['proof']     = isset($this->request->post['proof']) ? $this->request->post['proof'] : false;
        $data['accept_1']  = isset($this->request->post['accept_1']) ? $this->request->post['accept_1'] : false;
        $data['accept_2']  = isset($this->request->post['accept_2']) ? $this->request->post['accept_2'] : false;

        // Step 1
        $data['payment_instruction'] = sprintf(tt('Send exactly %s to this address:'), $this->currency->format(FEE_USER_VERIFICATION));
        $data['payment_address']     = $address;
        $data['payment_qr_href']     = $this->url->link('common/image/qr', 'code=' . $address);
        $data['payment_wallet_href'] = sprintf('bitcoin:%s?amount=%s&label=%s Verification Request for Account ID %s', $address, FEE_USER_VERIFICATION, PROJECT_NAME, $this->auth->getId());

        // Step 3
        $data['confirmation_code']   = $code;

        $data['href_cancel'] = $this->url->link('account/account');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['alert_danger']  = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');
        $data['alert_warning'] = $this->load->controller('common/alert/warning');

        $data['module_account']  = $this->load->controller('module/account');
        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Account'), 'href' => $this->url->link('account/account'), 'active' => false),
                    array('name' => tt('Verification'), 'href' => $this->url->link('account/account/verification'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/verification.tpl', $data));
    }

    public function approve() {

        // Redirect if user is already logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->getCurrentLink())));
        }

        // Redirect if required parameters is missing
        if (!isset($this->request->get['code']) || empty($this->request->get['code'])) {
            $this->security_log->write('Try to approve email without approve param');
            $this->response->redirect($this->url->link('account/account'));
        }

        // Try to approve
        if (!$this->model_account_user->approveEmail($this->auth->getId(), $this->request->get['code'])) {
            $this->security_log->write('Try to approve email with invalid approve param');
            $this->session->setUserMessage(array('danger' => tt('Invalid approval code!')));
        } else {
            $this->session->setUserMessage(array('success' => tt('Your email has been successfully approved!')));
        }

        $this->response->redirect($this->url->link('account/account'));

    }

    public function captcha() {
        $captcha = new Captcha();
        $captcha->getImage($this->session->getCaptcha());
    }

    // AJAX actions begin
    public function uploadAvatar() {

        if (!$this->auth->isLogged()) {
            $this->security_log->write('Try to upload image from guest request');
            exit;
        }

        if (!$this->request->isAjax()) {
            $this->security_log->write('Try to upload image without ajax request');
            exit;
        }

        $json = array('error_message' => tt('Undefined upload error'));

        if ('POST' == $this->request->getRequestMethod() && $this->_validateAvatar()) {

            // If image file looks good, lets prepare temporary save it
            $image = new Image($this->request->files['avatar']['tmp_name']);

            // Resize to default original format
            if (USER_IMAGE_ORIGINAL_WIDTH < $image->getWidth() || USER_IMAGE_ORIGINAL_HEIGHT < $image->getHeight()) {
                $image->resize(USER_IMAGE_ORIGINAL_WIDTH, USER_IMAGE_ORIGINAL_HEIGHT);
            }

            // Return result
            $filename = DIR_STORAGE . $this->auth->getId() . DIR_SEPARATOR . 'thumb' . '.' . STORAGE_IMAGE_EXTENSION;

            if ($image->save($filename)) {
                $json = array('success_message' => tt('Image successfully uploaded!'),
                              'url'             => $this->cache->image('thumb', $this->auth->getId(), 100, 100, false, true) . '?update=' . time());
            }

        } else if (isset($this->_error['common'])) {
            $json = array('error_message' => $this->_error['common']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Local helpers begin
    private function _validateLogin() {

        // Login by email
        if (preg_match('/^.+[@]{1}.+$/', $this->request->post['login'])) {

            $login_is_email = true;

            if (!isset($this->request->post['login']) || empty($this->request->post['login'])) {
                $this->_error['login'] = tt('Email is required');
            } else if (!ValidatorUser::emailValid($this->request->post['login'])) {
                $this->_error['login'] = tt('Invalid email address');
            } else if (!$this->model_account_user->checkEmail($this->request->post['login'])) {
                $this->_error['login'] = tt('E-Mail is not exists');
            }

        // Login by username
        } else {

            $login_is_email = false;

            if (!isset($this->request->post['login']) || empty($this->request->post['login'])) {
                $this->_error['login'] = tt('Username is required');
            } else if (mb_strlen($this->request->post['login']) < ValidatorUser::getUsernameMinLength() || mb_strlen($this->request->post['login']) > ValidatorUser::getUsernameMaxLength()) {
                $this->_error['username'] = sprintf(tt('Username must be between %s and %s characters'), ValidatorUser::getUsernameMinLength(), ValidatorUser::getUsernameMaxLength());
            } else if (!ValidatorUser::usernameValid($this->request->post['login'])) {
                $this->_error['username'] = tt('Username can only contain latin letters, numbers and hyphen');
            } else if (!$this->model_account_user->checkUsername($this->request->post['login'])) {
                $this->_error['login'] = tt('Username is not exists');
            }
        }

        // Password
        if (!isset($this->request->post['password']) || empty($this->request->post['password'])) {
            $this->_error['password'] = tt('Password is required');
        } else if ((mb_strlen($this->request->post['password']) < ValidatorUser::getPasswordMinLength()) || (mb_strlen($this->request->post['password']) > ValidatorUser::getPasswordMaxLength())) {
            $this->_error['password'] = sprintf(tt('Password must be between %s and %s characters'), ValidatorUser::getPasswordMinLength(), ValidatorUser::getPasswordMaxLength());
        } else if (!ValidatorUser::passwordValid($this->request->post['password'])) {
            $this->_error['password'] = tt('Invalid password');
        }

        // Try to login
        if (!isset($this->_error['login']) &&
            !isset($this->_error['password']) &&
            $this->auth->login($this->request->post['login'], $this->request->post['password'], $login_is_email)) {

            unset($this->_error['warning']);
            $this->model_account_user->deleteLoginAttempts($this->request->post['login']);
        } else {
            $this->model_account_user->addLoginAttempt($this->request->post['login']);

            if (!$this->_error) {
                $this->_error['bull'] = true;
            }
        }

        return !$this->_error;
    }

    private function _validateReset() {

        // Password
        if (!isset($this->request->post['password']) || empty($this->request->post['password'])) {
            $this->_error['password'] = tt('Password is required');
        } else if ((mb_strlen($this->request->post['password']) < ValidatorUser::getPasswordMinLength()) || (mb_strlen($this->request->post['password']) > ValidatorUser::getPasswordMaxLength())) {
            $this->_error['password'] = sprintf(tt('Password must be between %s and %s characters'), ValidatorUser::getPasswordMinLength(), ValidatorUser::getPasswordMaxLength());
        } else if (!ValidatorUser::passwordValid($this->request->post['password'])) {
            $this->_error['password'] = tt('Invalid password');
        }

        // Password confirm
        if (!isset($this->request->post['confirm']) || empty($this->request->post['confirm'])) {
            $this->_error['confirm'] = tt('Confirm is required');
        } else if ($this->request->post['confirm'] != $this->request->post['password']) {
            $this->_error['confirm'] = tt('Password confirmation does not match password');
        }

        return !$this->_error;
    }

    private function _validateForgot() {

        if (!isset($this->request->post['email']) || empty($this->request->post['email'])) {
            $this->_error['email'] = tt('Email is required');
        } else if (!ValidatorUser::emailValid($this->request->post['email'])) {
            $this->_error['email'] = tt('Invalid email address');
        } else if (!$this->model_account_user->checkEmail($this->request->post['email'])) {
            $this->_error['email'] = tt('E-Mail is not exists');
        }

        return !$this->_error;
    }

    private function _validateVerification() {

        // Proof should be available
        if (!isset($this->request->post['proof']) || empty($this->request->post['proof'])) {
            $this->_error['proof'] = tt('Proof information is required!');
        }

        // Accept terms
        if (!isset($this->request->post['accept_1']) || empty($this->request->post['accept_1']) || $this->request->post['accept_1'] != 1) {
            $this->_error['accept_1'] = tt('You must accept terms and conditions!');
        }

        if (!isset($this->request->post['accept_2']) || empty($this->request->post['accept_2']) || $this->request->post['accept_2'] != 1) {
            $this->_error['accept_2'] = tt('You must accept terms and conditions!');
        }

        // Common message
        if ($this->_error) {
            $this->session->setUserMessage(array('danger' => tt('Please check the form carefully for errors!')));
        }

        return !$this->_error;
    }

    private function _validateCreate() {

        // Username
        if (!isset($this->request->post['username']) || empty($this->request->post['username'])) {
            $this->_error['username'] = tt('Username is required');
        } else if ($this->model_account_user->checkUsername($this->request->post['username'])) {
            $this->_error['username'] = tt('Username is already registered');
        } else if (mb_strlen($this->request->post['username']) < ValidatorUser::getUsernameMinLength() || mb_strlen($this->request->post['username']) > ValidatorUser::getUsernameMaxLength()) {
            $this->_error['username'] = sprintf(tt('Username must be between %s and %s characters'), ValidatorUser::getUsernameMinLength(), ValidatorUser::getUsernameMaxLength());
        } else if (!ValidatorUser::usernameValid($this->request->post['username'])) {
            $this->_error['username'] = tt('Username can only contain latin letters, numbers and hyphen');
        }

        // Email
        if (!isset($this->request->post['email']) || empty($this->request->post['email'])) {
            $this->_error['email'] = tt('Email is required');
        } else if ($this->model_account_user->checkEmail($this->request->post['email'])) {
            $this->_error['email'] = tt('Email address is already registered or reserved');
        } else if (!ValidatorUser::emailValid($this->request->post['email'])) {
            $this->_error['email'] = tt('Invalid email address');
        }

        // Password
        if (!isset($this->request->post['password']) || empty($this->request->post['password'])) {
            $this->_error['password'] = tt('Password is required');
        } else if ((mb_strlen($this->request->post['password']) < ValidatorUser::getPasswordMinLength()) || (mb_strlen($this->request->post['password']) > ValidatorUser::getPasswordMaxLength())) {
            $this->_error['password'] = sprintf(tt('Password must be between %s and %s characters'), ValidatorUser::getPasswordMinLength(), ValidatorUser::getPasswordMaxLength());
        } else if (!ValidatorUser::passwordValid($this->request->post['password'])) {
            $this->_error['password'] = tt('Invalid password');
        }

        // Password confirm
        if (!isset($this->request->post['confirm']) || empty($this->request->post['confirm'])) {
            $this->_error['confirm'] = tt('Confirm is required');
        } else if ($this->request->post['confirm'] != $this->request->post['password']) {
            $this->_error['confirm'] = tt('Password confirmation does not match password');
        }

        // Captcha verification
        if (!isset($this->request->post['captcha']) || empty($this->request->post['captcha'])) {
            $this->_error['captcha'] = tt('Magic word is required');
        } else if (strtoupper($this->request->post['captcha']) != strtoupper($this->session->getCaptcha())) {
            $this->_error['captcha'] = tt('Incorrect magic word');
        }

        // Accept terms
        if (!isset($this->request->post['accept']) || empty($this->request->post['accept']) || $this->request->post['accept'] != 1) {
            $this->_error['accept'] = tt('You must accept Terms of Service to continue registration');
        }

        return !$this->_error;
    }

    private function _validateUpdate() {

        // Username
        if (!isset($this->request->post['username']) || empty($this->request->post['username'])) {
            $this->_error['username'] = tt('Username is required');
        } else if (mb_strtolower($this->request->post['username']) != mb_strtolower($this->auth->getUsername()) && $this->model_account_user->checkUsername($this->request->post['username'])) {
            $this->_error['username'] = tt('Username is already registered');
        } else if (mb_strlen($this->request->post['username']) < ValidatorUser::getUsernameMinLength() || mb_strlen($this->request->post['username']) > ValidatorUser::getUsernameMaxLength()) {
            $this->_error['username'] = sprintf(tt('Username must be between %s and %s characters'), ValidatorUser::getUsernameMinLength(), ValidatorUser::getUsernameMaxLength());
        } else if (!ValidatorUser::usernameValid($this->request->post['username'])) {
            $this->_error['username'] = tt('Username can only contain latin letters, numbers and hyphen');
        }

        // Email
        if (!isset($this->request->post['email']) || empty($this->request->post['email'])) {
            $this->_error['email'] = tt('Email is required');
        } else if (mb_strtolower($this->request->post['email']) != mb_strtolower($this->auth->getEmail()) && $this->model_account_user->checkEmail($this->request->post['email'])) {

            $user_emails = $this->model_account_user->getEmails($this->auth->getId());
            $available_emails = array();

            foreach ($user_emails as $user_email) {
                $available_emails[] = $user_email->email;
            }

            if (!in_array($this->request->post['email'], $available_emails)) {
                $this->_error['email'] = tt('Email address is already registered or reserved');
            }
        } else if (!ValidatorUser::emailValid($this->request->post['email'])) {
            $this->_error['email'] = tt('Invalid email address');
        }

        if (!isset($this->request->post['confirm']) || !isset($this->request->post['password'])) {
            $this->_error['password'] = tt('Wrong password fields');
            $this->security_log->write('Wrong password fields');

        } else if (!empty($this->request->post['password']) || !empty($this->request->post['confirm'])) {

            // New password
            if (empty($this->request->post['password'])) {
                $this->_error['password'] = tt('Password is required');
            } else if ((mb_strlen($this->request->post['password']) < ValidatorUser::getPasswordMinLength()) || (mb_strlen($this->request->post['password']) > ValidatorUser::getPasswordMaxLength())) {
                $this->_error['password'] = sprintf(tt('Password must be between %s and %s characters'), ValidatorUser::getPasswordMinLength(), ValidatorUser::getPasswordMaxLength());
            } else if (!ValidatorUser::passwordValid($this->request->post['password'])) {
                $this->_error['password'] = tt('Invalid password');
            }

            // New password confirm
            if (empty($this->request->post['confirm'])) {
                $this->_error['confirm'] = tt('Confirm is required');
            } else if ($this->request->post['confirm'] != $this->request->post['password']) {
                $this->_error['confirm'] = tt('Password confirmation does not match password');
            }
        }

        // Check the old password
        if (!isset($this->request->post['old_password']) || empty($this->request->post['old_password'])) {
            $this->_error['old_password'] = tt('Old password is required');
        } else if (!$this->model_account_user->checkPassword($this->auth->getId(), $this->request->post['old_password'])) {
            $this->_error['old_password'] = tt('Incorrect old password');
        }

        return !$this->_error;
    }

    private function _validateAvatar() {

        if (!isset($this->request->files['avatar']['tmp_name']) || !isset($this->request->files['avatar']['name'])) {

            $this->_error['common'] = tt('Image file is wrong!');
            $this->security_log->write('Uploaded image file is wrong (tmp_name or name indexes is not exists)');

        } else if (!ValidatorUpload::imageValid(array('name'     => $this->request->files['avatar']['name'],
                                                      'tmp_name' => $this->request->files['avatar']['tmp_name']),
                                                      QUOTA_IMAGE_MAX_FILE_SIZE,
                                                      USER_IMAGE_ORIGINAL_MIN_WIDTH,
                                                      USER_IMAGE_ORIGINAL_MIN_HEIGHT)) {

            $this->_error['common'] = tt('This is a not valid image file!');
            $this->security_log->write('Uploaded image file is not valid');
        }

        return !$this->_error;
    }
}
