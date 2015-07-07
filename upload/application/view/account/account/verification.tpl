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
      <h1 id="forms"><?php echo tt('Account Verification') ?></h1>
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

      <div class="row">
        <div class="col-lg-12 form-group">
          <div class="well">
            Verification feature is intended primarily for sellers. It's looks like <span class="text-success"><span class="glyphicon glyphicon-ok"></span> <strong><?php echo tt('Verified Seller') ?></strong></span> label in product cards.<br />
            Offers from verified sellers haves more trust for theirs potential buyers.<br /><br />

            <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;This function is based on expert judgment, so we charge a small fee for it.<br />
            <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;Be sure that information you provided is correct and contain only facts with proof links.<br />
            <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;We do not refund any payments and do not warrant that your request will be approved.<br />
            <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;Average time to review your verification request varied in 1-7 days.<br />
          </div>
        </div>
      </div>


      <form class="form-horizontal" action="<?php echo $action ?>" method="POST">
        <fieldset>
          <legend><?php echo tt('Step 1: Verification Fee') ?></legend>
          <div class="col-lg-11 form-group">
            <div class="row">
              <div class="col-lg-5 text-center">
                <img src="<?php echo $payment_qr_href ?>" />
              </div>
              <div class="col-lg-7">
                <div class="row">
                  <div class="col-lg-12">
                    <p><?php echo $payment_instruction ?></p>
                    <pre><?php echo $payment_address ?></pre>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-6 text-left">
                    <a class="btn btn-primary" href="<?php echo $payment_wallet_href ?>"><?php echo tt('Use wallet') ?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Step 2: Proof Arguments') ?></legend>
          <div class="col-lg-11 form-group">
            <div class="row">
            <div class="col-lg-5">
              <?php echo tt('Provide detailed information that in your opinion proves your identity. It should include at least one web page or social profile that you own:') ?>
            </div>
            <div class="col-lg-7">
              <div class="<?php if (isset($error['proof'])) { ?> has-error<?php } ?>">
                <textarea name="proof" class="form-control" rows="5"><?php echo $proof ?></textarea>
                <?php if (isset($error['proof'])) { ?>
                  <div class="text-danger"><?php echo $error['proof'] ?></div>
                <?php } ?>
              </div>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php echo tt('Step 3: Confirmation') ?></legend>
          <div class="col-lg-11 form-group">
            <div class="row">
              <div class="col-lg-5">
                <?php echo tt("Copy one of these codes provided in your social profile page or site's home page:") ?>
              </div>
              <div class="col-lg-7">
                <pre><?php echo sprintf(tt('%s ID: %s'), PROJECT_NAME, $confirmation_code) ?></pre>
                <pre><?php echo sprintf(tt("<meta \nname=\"%s-id\" \ncontent=\"%s\" />"), strtolower(PROJECT_NAME), $confirmation_code) ?></pre>
              </div>
            </div>
          </div>
        </fieldset>
        <div class="col-lg-11 form-group">
          <div class="row">
            <div class="col-lg-7 col-lg-offset-5">
              <div class="checkbox<?php if (isset($error['accept_1'])) { ?> has-error<?php } ?>">
                <label>
                  <?php if ($accept_1) { ?>
                    <input type="checkbox" name="accept_1" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="accept_1" value="1">
                  <?php } ?>
                  <?php echo tt('I agree that my request may be decline.') ?>
                </label>
              </div>
              <div class="checkbox<?php if (isset($error['accept_2'])) { ?> has-error<?php } ?>">
                <label>
                  <?php if ($accept_2) { ?>
                    <input type="checkbox" name="accept_2" value="1" checked="checked">
                  <?php } else { ?>
                    <input type="checkbox" name="accept_2" value="1">
                  <?php } ?>
                  <?php echo tt('I agree that my payment no refund.') ?>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-11 text-right form-group">
          <a href="<?php echo $href_cancel ?>" class="btn btn-default"><?php echo tt('Cancel') ?></a>
          <button type="submit" class="btn btn-primary"><?php echo tt('Send Request') ?></button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php echo $footer ?>
