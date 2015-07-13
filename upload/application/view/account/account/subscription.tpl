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
      <h1 id="forms"><?php echo tt('Subscriptions') ?></h1>
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
    <?php echo $alert_success ?>
    <div class="bs-component">
      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <?php foreach ($subscriptions as $group => $subscription) { ?>
          <legend><?php echo $group ?></legend>
          <?php $i = 0; ?>
          <?php foreach ($subscription as $subscription) { ?>
            <?php $i++; ?>
            <div class="row">
              <div class="col-lg-3">
                <?php if ($i == 1) { ?>
                  <?php echo $subscription['label'] ?>
                <?php } ?>
              </div>
              <div class="col-lg-8">
                <div class="checkbox">
                  <label>
                    <?php if ($subscription['active']) { ?>
                      <input type="checkbox" name="subscription[<?php echo $subscription['subscription_id'] ?>]" value="1" checked="checked">
                    <?php } else { ?>
                      <input type="checkbox" name="subscription[<?php echo $subscription['subscription_id'] ?>]" value="1">
                    <?php } ?>
                    <?php echo $subscription['title'] ?>
                  </label>
                </div>
              </div>
            </div>
          <?php } ?>
          <div>&nbsp;</div>
        <?php } ?>
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
