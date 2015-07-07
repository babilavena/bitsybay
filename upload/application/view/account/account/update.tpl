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
      <h1 id="forms"><?php echo tt('Account Settings') ?></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $module_breadcrumbs ?>
  </div>
</div>
<div class="row">

  <div class="col-lg-3">
    <?php echo $module_account ?>
  </div>

  <div class="col-lg-9">

    <?php echo $alert_danger ?>
    <?php echo $alert_warning ?>
    <?php echo $alert_success ?>

    <div class="form-group bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt('Change username') ?></legend>

          <div class="form-group<?php if (isset($error['username'])) { ?> has-error<?php } ?>">
            <div class="col-lg-6">
              <input onkeyup="lengthFilter(this, <?php echo VALIDATOR_USER_USERNAME_MAX_LENGTH ?>)" type="text" name="username" class="form-control" id="inputUsername" placeholder="<?php echo tt('Username') ?>" value="<?php echo $username ?>">
              <?php if (isset($error['username'])) { ?>
                <div class="text-danger"><?php echo $error['username'] ?></div>
              <?php } ?>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Change email') ?></legend>
          <div class="form-group<?php if (isset($error['email'])) { ?> has-error<?php } ?>">
            <div class="col-lg-6">
              <input type="text" name="email" class="form-control" id="inputEmail" placeholder="<?php echo tt('Email') ?>" value="<?php echo $email ?>">
              <?php if (isset($error['email'])) { ?>
                <div class="text-danger"><?php echo $error['email'] ?></div>
              <?php } ?>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Change password') ?></legend>
          <div class="form-group<?php if (isset($error['password'])) { ?> has-error<?php } ?>">
            <div class="col-lg-6">
              <input type="password" name="password" class="form-control" id="inputPassword" placeholder="<?php echo tt('New Password') ?>">
              <?php if (isset($error['password'])) { ?>
                <div class="text-danger"><?php echo $error['password'] ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if (isset($error['confirm'])) { ?> has-error<?php } ?>">
            <div class="col-lg-6">
              <input type="password" name="confirm" class="form-control" id="inputConfirm" placeholder="<?php echo tt('Confirm new password') ?>">
              <?php if (isset($error['confirm'])) { ?>
                <div class="text-danger"><?php echo $error['confirm'] ?></div>
              <?php } ?>
            </div>
          </div>
          <fieldset>
            <legend><?php echo tt('Enter your old password') ?></legend>
            <div class="form-group<?php if (isset($error['old_password'])) { ?> has-error<?php } ?>">
              <div class="col-lg-6">
                <input type="password" name="old_password" class="form-control" id="inputOldPassword" placeholder="<?php echo tt('Old Password') ?>">
                <?php if (isset($error['old_password'])) { ?>
                  <div class="text-danger"><?php echo $error['old_password'] ?></div>
                <?php } ?>
              </div>
            </div>
          </fieldset>
          <div class="form-group">
            <div class="col-lg-10">
              <button type="submit" class="btn btn-primary"><?php echo tt('Save Settings') ?></button>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
<?php echo $footer ?>
