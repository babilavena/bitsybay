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
      <h1 id="forms"><?php echo tt('Password reset') ?></h1>
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
    <?php echo $alert_success ?>
    <?php echo $alert_danger ?>
    <div class="well bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt("Change your new password") ?></legend>
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
          <div class="form-group">
            <div class="col-lg-10">
              <button type="submit" class="btn btn-primary"><?php echo tt('Save changes') ?></button>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  <div class="col-lg-5 col-lg-offset-1">
    <div class="bs-component">
      <h3><?php echo tt('I’ve remember my password!') ?></h3>
      <ul>
        <li>Nice, visit the <a href="<?php echo $href_account_account_login ?>">Login page</a></li>
        <li>If you don’t have an Account, please visit the <a href="<?php echo $href_account_account_create ?>">Registration page</a></li>
      </ul>
      <h4><?php echo tt('Help resources') ?></h4>
      <ul>
        <li>Visit the <a href="<?php echo $href_common_information_faq ?>">F.A.Q page</a></li>
        <li>To get specific answers <a href="<?php echo $href_common_contact ?>">Contact Us</a></li>
      </ul>
    </div>
  </div>
</div>
<?php echo $footer ?>
