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
        $this->load->helper('validator/user');
        $this->load->helper('validator/upload');
        $this->load->library('mail');
        $this->load->library('identicon');
        $this->load->library('captcha/captcha');
    }

    public function index() {

        $this->document->setTitle(tt('Profile'));

        // Redirect to login page if user is not logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', '', 'SSL'));
        }

        $data = array();

        $data['approved']      = $this->auth->isApproved();
        $data['verified']      = $this->auth->isVerified();
        $data['active']        = $this->auth->isActive();
        $data['username']      = $this->auth->getUsername();
        $data['date_added']    = date(DATE_FORMAT_DEFAULT, strtotime($this->auth->getDateAdded()));
        $data['avatar_url']    = $this->cache->image('thumb', $this->auth->getId(), 100, 100);

        $data['avatar_action']                 = $this->url->link('account/account/uploadAvatar', '', 'SSL');
        $data['href_catalog_search_favorites'] = $this->url->link('catalog/search', 'favorites=1', 'SSL');
        $data['href_catalog_search_purchased'] = $this->url->link('catalog/search', 'purchased=1', 'SSL');
        $data['href_account_account_update']   = $this->url->link('account/account/update', '', 'SSL');
        $data['href_account_product_create']   = $this->url->link('account/product/create', '', 'SSL');

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
                    array('name' => tt('Profile'), 'href' => $this->url->link('account/account', '', 'SSL'), 'active' => false),
        ));

        $data['module_quota_bar']    = $this->load->controller('module/quota_bar');

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/account.tpl', $data));
    }

    public function create() {
        $this->document->addScript('/javascript/bootstrap-datepicker.js');

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account', '', 'SSL'));
        }

        // Validate & save incoming data
        if ('POST' == $this->request->getRequestMethod() && $this->_validateCreate()) {



            // Generate email approval link
            $approval_code = md5(rand() . microtime() . $this->request->post['email']);

            // Create new user
            if ($this->model_account_user->createUser( $this->request->post['username'],
                                                       $this->request->post['email'],
                                                       $this->request->post['password'],
                                                       1, // Is buyer
                                                       1, // Is seller
                                                       NEW_USER_STATUS,
                                                       NEW_USER_VERIFIED,
                                                       QUOTA_FILE_SIZE_BY_DEFAULT,
                                                       $approval_code)) {

                // Clear any previous login attempts for unregistered accounts.
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

                    // Send user email
                    $mail = new Mail();
                    $mail->setTo($this->request->post['email']);
                    $mail->setFrom(MAIL_FROM);
                    $mail->setReplyTo(MAIL_INFO);
                    $mail->setSender(MAIL_SENDER);
                    $mail->setSubject(tt('Welcome to the BitsyBay Store!'));
                    $mail->setText(
                        tt("Welcome and thank you for registering!\n\n").
                        sprintf(tt("Here is your BitsyBay account information:\n\nUsername: %s\nE-mail: %s\nPassword: %s\n\n"), $this->request->post['username'], $this->request->post['email'], $this->request->post['password']).
                        sprintf(tt("Please, approve your email at the following URL: \n%s"), $this->url->link('account/account/approve', 'approval_code=' . $approval_code, 'SSL'))
                    );
                    $mail->send();

                    // Send admin notice
                    $mail = new Mail();
                    $mail->setTo(MAIL_INFO);
                    $mail->setFrom(MAIL_FROM);
                    $mail->setReplyTo(MAIL_INFO);
                    $mail->setSender(MAIL_SENDER);
                    $mail->setSubject(tt('A new customer join us'));
                    $mail->setText(tt('Yes yes yes'));
                    $mail->send();

                    // Redirect to account page
                    //if (isset($this->request->get['redirect'])) {
                    //    $this->response->redirect(base64_decode($this->request->get['redirect']));
                    //} else {
                        $this->response->redirect($this->url->link('account/account', '', 'SSL'));
                    //}
                }
            }
        }

        // Set view variables
        $this->document->setTitle(tt('Create an Account'));

        $data = array();

        $data['error'] = $this->_error;

        $captcha = new Captcha();
        $this->session->setCaptcha($captcha->getCode());
        $data['captcha'] = $this->url->link('account/account/captcha', '', 'SSL');

        $data['action'] = $this->url->link('account/account/create', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false, 'SSL');
        $data['href_account_account_login'] = $this->url->link('account/account/login', '', 'SSL');
        $data['href_account_account_forgot'] = $this->url->link('account/account/forgot', '', 'SSL');
        $data['href_common_information_terms'] = $this->url->link('common/information/terms');
        $data['href_common_information_licenses'] = $this->url->link('common/information/licenses');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['username'] = isset($this->request->post['username']) ? $this->request->post['username'] : false;
        $data['email']    = isset($this->request->post['email'])    ? $this->request->post['email']    : false;
        $data['accept']   = isset($this->request->post['accept'])   ? $this->request->post['accept']   : false;

        $data['alert_danger'] = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href'  => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account', '', 'SSL'), 'active' => false),
            array('name' => tt('Create'), 'href' => $this->url->link('account/account/create', '', 'SSL'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/create.tpl', $data));
    }

    public function captcha() {

        $captcha = new Captcha();
        $captcha->getImage($this->session->getCaptcha());
    }

    public function update() {

        // Init
        $data = array();

        // Redirect if user is already logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', '', 'SSL'));
        }

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

                if ($this->request->post['email'] != $this->auth->getEmail()) {
                    $mail = new Mail();
                    $mail->setTo($this->request->post['email']);
                    $mail->setFrom(MAIL_FROM);
                    $mail->setReplyTo(MAIL_INFO);
                    $mail->setSender(MAIL_SENDER);
                    $mail->setSubject(tt('BitsyBay e-mail verification'));
                    $mail->setText(
                        sprintf(tt("Your email address has been changed from %s to %s.\n"), $this->auth->getEmail(), $this->request->post['email']) .
                        sprintf(tt("Please, approve your new email at the following URL:\n"), $this->url->link('account/account', 'approve=' . $approval_code, 'SSL')));
                    $mail->send();

                    // Success alert
                    $this->session->setUserMessage(array(
                        'success' => tt('Well done! You have successfully modified account settings!'),
                        'warning' => tt('You have successfully modified account settings! Please, check your mailbox to approve the new email address.')
                    ));
                } else {
                    // Success alert
                    $this->session->setUserMessage(array('success' => tt('Well done! You have successfully modified account settings!')));
                }


                $this->response->redirect($this->url->link('account/account/update', '', 'SSL'));
            }
        }

        // Set headers
        $this->document->setTitle(tt('Account Edit'));

        // Set variables
        $user = $this->model_account_user->getUser($this->auth->getId());

        $data['email'] = isset($this->request->post['email']) ? $this->request->post['email'] : $user->email;
        $data['username'] = isset($this->request->post['username']) ? $this->request->post['username'] : $user->username;

        // Errors
        $data['error'] = $this->_error;

        // Links
        $data['action'] = $this->url->link('account/account/update', '', 'SSL');

        // Load modules
        $data['module_account'] = $this->load->controller('module/account');
        $data['module_verification'] = $this->load->controller('module/verification');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['alert_danger'] = $this->load->controller('common/alert/danger');
        $data['alert_success'] = $this->load->controller('common/alert/success');
        $data['alert_warning'] = $this->load->controller('common/alert/warning');

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account', '', 'SSL'), 'active' => false),
            array('name' => tt('Settings'), 'href' => $this->url->link('account/account/update', '', 'SSL'), 'active' => true),
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/update.tpl', $data));

    }

    public function login() {

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account', '', 'SSL'));
        }

        $this->document->setTitle(tt('Sign In'));

        if ('POST' == $this->request->getRequestMethod() && $this->_validateLogin()) {

            if (isset($this->request->get['redirect'])) {
                $this->response->redirect(base64_decode($this->request->get['redirect']));
            } else {
                $this->response->redirect($this->url->link('account/account', '', 'SSL'));
            }
        }

        $data = array();

        $data['href_account_forgot'] = $this->url->link('account/account/forgot', '', 'SSL');

        $data['error']  = $this->_error;
        $data['action'] = $this->url->link('account/account/login', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false, 'SSL');

        $data['href_account_account_create'] = $this->url->link('account/account/create', '', 'SSL');
        $data['href_account_account_forgot'] = $this->url->link('account/account/forgot', '', 'SSL');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['alert_success'] = $this->load->controller('common/alert/success');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['login']    = isset($this->request->post['login']) ? $this->request->post['login'] : false;
        $data['password'] = isset($this->request->post['password']) ? $this->request->post['password'] : false;

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
            array('name' => tt('Home'), 'href'  => $this->url->link('common/home'), 'active' => false),
            array('name' => tt('Account'), 'href' => $this->url->link('account/account', '', 'SSL'), 'active' => false),
            array('name' => tt('Sign In'), 'href' => $this->url->link('account/account/login', '', 'SSL'), 'active' => true)
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

    public function forgot() {

        // Redirect if user is already logged
        if ($this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account', '', 'SSL'));
        }

        $this->document->setTitle(tt('Request a password reset'));

        $data = array();

        if ('POST' == $this->request->getRequestMethod() && $this->_validateForgot()) {

            // Reset password
            $password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);

            $this->model_account_user->resetPassword($this->request->post['email'], $password);

            $mail = new Mail();
            $mail->setTo($this->request->post['email']);
            $mail->setFrom(MAIL_FROM);
            $mail->setReplyTo(MAIL_INFO);
            $mail->setSender(MAIL_SENDER);
            $mail->setSubject(tt('BitsyBay - Password recovery'));
            $mail->setText(
                sprintf(tt("A new password was requested from %s\n"), $this->request->post['email']) .
                sprintf(tt("Your new password is: %s"), $password)
            );
            $mail->send();

            $this->session->setUserMessage(array('success' => tt('Recovery instructions sent to your email address!')));

            // Redirect to login page
            $this->response->redirect($this->url->link('account/account/login', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false, 'SSL'));
        }

        $data['error']  = $this->_error;
        $data['action'] = $this->url->link('account/account/forgot', isset($this->request->get['redirect']) ? 'redirect=' . $this->request->get['redirect'] : false, 'SSL');
        $data['href_account_account_create'] = $this->url->link('account/account/create', '', 'SSL');
        $data['href_account_account_login'] = $this->url->link('account/account/login', '', 'SSL');
        $data['href_common_information_faq'] = $this->url->link('common/information/faq');
        $data['href_common_contact'] = $this->url->link('common/contact');

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['email']    = isset($this->request->post['email']) ? $this->request->post['email'] : false;

        $data['module_breadcrumbs'] = $this->load->controller('module/breadcrumbs', array(
                    array('name' => tt('Home'), 'href' => $this->url->link('common/home'), 'active' => false),
                    array('name' => tt('Account'), 'href' => $this->url->link('account/account', '', 'SSL'), 'active' => false),
                    array('name' => tt('Forgot'), 'href' => $this->url->link('account/account/forgot', '', 'SSL'), 'active' => true)
        ));

        // Renter the template
        $this->response->setOutput($this->load->view('account/account/forgot.tpl', $data));
    }

    public function approve() {

        // Redirect if user is already logged
        if (!$this->auth->isLogged()) {
            $this->response->redirect($this->url->link('account/account/login', 'redirect=' . base64_encode($this->url->getCurrentLink($this->request->getHttps())), 'SSL'));
        }

        // Redirect if required parameters is missing
        if (!isset($this->request->get['approval_code']) || empty($this->request->get['approval_code'])) {
            $this->security_log->write('Try to approve email without approve param');
            $this->response->redirect($this->url->link('account/account', '', 'SSL'));
        }

        // Try to approve
        if (!$this->model_account_user->approveEmail($this->auth->getId(), $this->auth->getEmail(), $this->request->get['approval_code'])) {
            $this->security_log->write('Try to approve email with invalid approve param');
            $this->session->setUserMessage(array('danger' => tt('Invalid approval code!')));
        } else {
            $this->session->setUserMessage(array('success' => tt('Your email successfully approved!')));
        }

        $this->response->redirect($this->url->link('account/account', '', 'SSL'));

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
