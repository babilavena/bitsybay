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
        <h1 id="forms"><?php echo tt('Contact Us') ?></h1>
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
      <div class="well bs-component">
        <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
          <fieldset>
            <legend><?php echo tt('Send your message') ?></legend>
            <div class="form-group<?php if (isset($error['email'])) { ?> has-error<?php } ?>">
              <label for="inputEmail" class="col-lg-2 control-label"><?php echo tt('Email') ?></label>
              <div class="col-lg-10">
                <input type="text" name="email" class="form-control" id="inputEmail" placeholder="<?php echo tt('Email') ?>" value="<?php echo $email ?>" />
                <?php if (isset($error['email'])) { ?>
                  <div class="text-danger"><?php echo $error['email'] ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group<?php if (isset($error['email'])) { ?> has-error<?php } ?>">
              <label for="inputEmail" class="col-lg-2 control-label"><?php echo tt('Subject') ?></label>
              <div class="col-lg-10">
                <input type="text" name="subject" class="form-control" id="inputSubject" placeholder="<?php echo tt('Subject') ?>" value="<?php echo $subject ?>" />
                <?php if (isset($error['subject'])) { ?>
                  <div class="text-danger"><?php echo $error['subject'] ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group<?php if (isset($error['message'])) { ?> has-error<?php } ?>">
              <label for="inputEmail" class="col-lg-2 control-label"><?php echo tt('Message') ?></label>
              <div class="col-lg-10">
                <textarea name="message" class="form-control" id="inputMessage" placeholder="<?php echo tt('Message') ?>" rows="5"><?php echo $message ?></textarea>
                <?php if (isset($error['message'])) { ?>
                  <div class="text-danger"><?php echo $error['message'] ?></div>
                <?php } ?>
              </div>
            </div>
            <div class="form-group">
              <div class="col-lg-10 col-lg-offset-2 text-right">
                <button type="submit" class="btn btn-primary"><?php echo tt('Submit') ?></button>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
    <div class="col-lg-5 col-lg-offset-1">
      <div class="bs-component">
        <h3><?php echo tt('Do not forget') ?></h3>
        <ul>
          <li>Visit the <a href="<?php echo $href_common_information_faq ?>">F.A.Q. page</a></li>
          <li>Use search to find specific content</li>
          <li>View <a href="<?php echo $href_common_information_terms ?>">Terms of Service</a> and <a href="<?php echo $href_common_information_licenses ?>">License Policy</a></li>
        </ul>
        <h3><?php echo tt('Physical address') ?></h3>
        <p><a href="/">BitsyBay</a> do not have a physical address at present as its only a software resource supported by a few enthusiasts. We can however be contacted by means of email through the above form.</p>
      </div>
    </div>
  </div>

<?php echo $footer ?>
