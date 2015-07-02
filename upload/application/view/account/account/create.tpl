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
      <h1 id="forms"><?php echo tt('Create an Account') ?></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $module_breadcrumbs ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-6">
    <div class="well bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt('Enter your data') ?></legend>
          <div class="form-group<?php if (isset($error['username'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input onkeyup="lengthFilter(this, <?php echo ValidatorUser::getUsernameMaxLength() ?>)" type="text" name="username" class="form-control" id="inputUsername" placeholder="<?php echo tt('Username') ?>" value="<?php echo $username ?>">
              <?php if (isset($error['username'])) { ?>
                <div class="text-danger"><?php echo $error['username'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['email'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input type="text" name="email" class="form-control" id="inputEmail" placeholder="<?php echo tt('Email') ?>" value="<?php echo $email ?>">
              <?php if (isset($error['email'])) { ?>
                <div class="text-danger"><?php echo $error['email'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['password'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input type="password" name="password" class="form-control" id="inputPassword" placeholder="<?php echo tt('Password') ?>">
              <?php if (isset($error['password'])) { ?>
                <div class="text-danger"><?php echo $error['password'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['confirm'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <input type="password" name="confirm" class="form-control" id="inputConfirm" placeholder="<?php echo tt('Confirm Password') ?>">
              <?php if (isset($error['confirm'])) { ?>
                <div class="text-danger"><?php echo $error['confirm'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['captcha'])) { ?> has-error<?php } ?>">
            <div class="col-lg-12">
              <img src="<?php echo $captcha ?>" />
              <input type="text" name="captcha" class="form-control input-lg" id="inputCaptcha" placeholder="<?php echo tt('Magic word ↑') ?>" style="width:160px;margin-top:15px">
              <?php if (isset($error['captcha'])) { ?>
                <div class="text-danger"><?php echo $error['captcha'] ?></div>
              <?php } ?>
            </div>
          </div>
          <?php if (isset($error['profile'])) { ?>
          <div class="form-group">
            <div class="col-lg-12">
              <div class="text-danger"><?php echo $error['profile'] ?></div>
            </div>
          </div>
          <?php } ?>
          <div class="form-group <?php echo isset($error['accept']) ? 'has-error' : false ?>">
            <div class="col-lg-10">
              <div class="checkbox">
                <label>
                  <?php if ($accept) { ?>
                    <input type="checkbox" name="accept" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="accept" value="1">
                  <?php } ?>
                  <?php echo sprintf(tt('I agree with the %s and %s'), '<a href="' . $href_common_information_terms . '" target="_blank">' . tt('Terms of Service') . '</a>', '<a href="' . $href_common_information_licenses . '" target="_blank">' . tt('License Policy') . '</a>') ?>
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-lg-10">
              <button type="submit" class="btn btn-primary"><?php echo tt('Create Account') ?></button>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div class="col-lg-5 col-lg-offset-1">
    <div class="bs-component">
      <h3><?php echo tt('Already have an account?') ?></h3>
      <ul>
        <li>Please visit the <a href="<?php echo $href_account_account_login ?>">Login page</a></li>
        <li>You can recover your password <a href="<?php echo $href_account_account_forgot ?>">here</a></li>
      </ul>
      <h4><?php echo tt('If you have a questions') ?></h4>
      <ul>
        <li>Visit the <a href="<?php echo $href_common_information_faq ?>">F.A.Q page</a></li>
        <li>To get specific answers <a href="<?php echo $href_common_contact ?>">Contact Us</a></li>
      </ul>
    </div>
  </div>
</div>
<?php echo $footer ?>
