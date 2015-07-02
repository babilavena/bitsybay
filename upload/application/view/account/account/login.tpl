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
 ?>

<?php echo $header ?>
<div class="row">
  <div class="col-lg-12">
    <div class="page-header">
      <h1 id="forms"><?php echo tt('Sign In') ?></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $module_breadcrumbs ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $alert_success ?>
  </div>
</div>
<?php if (isset($error['bull'])) { ?>
<div class="row">
  <div class="col-lg-6">
    <div class="bs-component">
      <div class="alert alert-dismissible alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><?php echo tt('Oops! Incorrect login data.') ?></strong> <?php echo tt('You can') ?> <a href="<?php echo $href_account_forgot ?>" class="alert-link normal"><?php echo tt('reset your password here') ?></a>.
      </div>
    </div>
  </div>
</div>
<?php } ?>
<div class="row">
  <div class="col-lg-6">
    <div class="well bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt('Who are you?') ?></legend>
          <div class="form-group<?php if (isset($error['login'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input type="text" name="login" class="form-control" id="inputLogin" placeholder="<?php echo tt('Email or username') ?>" value="<?php echo $login ?>">
              <?php if (isset($error['login'])) { ?>
                <div class="text-danger"><?php echo $error['login'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['password'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input type="password" name="password" class="form-control" id="inputPassword" placeholder="<?php echo tt('Password') ?>" value="<?php echo $password ?>">
              <?php if (isset($error['password'])) { ?>
                <div class="text-danger"><?php echo $error['password'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-12">
              <button type="submit" class="btn btn-primary sign-in-button"><?php echo tt('Sign In') ?></button>
              <div class="col-lg-offset-2">
                <a href="<?php echo $href_account_forgot ?>"><?php echo tt('Forgot Password') ?></a>
              </div>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div class="col-lg-5 col-lg-offset-1">
    <div class="bs-component">
      <h3><?php echo tt('Don’t have an account?') ?></h3>
      <ul>
        <li>Please visit the <a href="<?php echo $href_account_account_create ?>">Registration page</a></li>
        <li>You can recover your password <a href="<?php echo $href_account_account_forgot ?>">here</a></li>
      </ul>
      <h4><?php echo tt('If you have any questions') ?></h4>
      <ul>
        <li>Visit the <a href="<?php echo $href_common_information_faq ?>">F.A.Q page</a></li>
        <li>To get specific answers <a href="<?php echo $href_common_contact ?>">Contact Us</a></li>
      </ul>
    </div>
  </div>
</div>
<?php echo $footer ?>
