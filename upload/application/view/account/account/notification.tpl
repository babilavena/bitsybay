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
      <h1 id="forms"><?php echo tt('Notification Center') ?></h1>
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
    <div class="bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt('Activity from Catalog') ?></legend>
          <div class="col-lg-12 form-group">
            <div class="row">
            <div class="col-lg-3">
              <?php echo tt('Email me when') ?>
            </div>
            <div class="col-lg-8">
              <div class="checkbox">
                <label>
                  <?php if ($notify_pf) { ?>
                    <input type="checkbox" name="notify_pf" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_pf" value="1">
                  <?php } ?>
                  <?php echo tt('My Offers are marked as favorites') ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <?php if ($notify_pp) { ?>
                    <input type="checkbox" name="notify_pp" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_pp" value="1">
                  <?php } ?>
                  <?php echo tt('My Offers has been purchased') ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <?php if ($notify_pc) { ?>
                    <input type="checkbox" name="notify_pc" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_pc" value="1">
                  <?php } ?>
                  <?php echo tt('My Offers get a new comment') ?>
                </label>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Project Updates') ?></legend>
          <div class="col-lg-12 form-group">
            <div class="row">
            <div class="col-lg-3">
              <?php echo tt('Email me with') ?>
            </div>
            <div class="col-lg-8">
              <div class="checkbox">
                <label>
                  <?php if ($notify_pn) { ?>
                    <input type="checkbox" name="notify_pn" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_pn" value="1">
                  <?php } ?>
                  <?php echo sprintf(tt('News about %s service and feature updates'), PROJECT_NAME) ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <?php if ($notify_on) { ?>
                    <input type="checkbox" name="notify_on" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_on" value="1">
                  <?php } ?>
                  <?php echo sprintf(tt('News about %s on partner products and other third party services'), PROJECT_NAME) ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <?php if ($notify_au) { ?>
                    <input type="checkbox" name="notify_au" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_au" value="1">
                  <?php } ?>
                  <?php echo tt('News about Terms of Service and Licensing Policy updates') ?>
                </label>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Security') ?></legend>
          <div class="col-lg-12 form-group">
            <div class="row">
            <div class="col-lg-3">
              <?php echo tt('Email me with') ?>
            </div>
            <div class="col-lg-8">
              <div class="checkbox">
                <label>
                  <?php if ($notify_ni) { ?>
                    <input type="checkbox" name="notify_ni" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_ni" value="1">
                  <?php } ?>
                  <?php echo tt('Login from another IP address') ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <?php if ($notify_ns) { ?>
                    <input type="checkbox" name="notify_ns" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="notify_ns" value="1">
                  <?php } ?>
                  <?php echo tt('Changing account settings') ?>
                </label>
              </div>
            </div>
          </div>
        </fieldset>
        <div class="col-lg-12 form-group">
          <div class="row">
            <div class="col-lg-8 col-lg-offset-3">
              <button type="submit" class="btn btn-primary"><?php echo tt('Save Settings') ?></button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php echo $footer ?>
