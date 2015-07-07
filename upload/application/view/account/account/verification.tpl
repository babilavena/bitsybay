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
            <p>
              The Verification feature is intended primarily for sellers. Verified seller can be identified by this label <span class="text-success"><span class="glyphicon glyphicon-ok"></span> <strong><?php echo tt('Verified Seller') ?></strong></span> in the product pages.
              Verified sellers enjoy a greater deal of trust from buyers.
            </p>
            <p>
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;The verification process is based on our expert judgement and we charge a small fee for this.<br />
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;Be sure that the information provided is correct and that all relevant facts and links are provided.<br />
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;We do not refund any payments and neither do we guarantee that your request will be approved.<br />
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;The review and verification process takes between 1 to 7 days.<br />
            </p>
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
                    <a class="btn btn-default" href="<?php echo $payment_wallet_href ?>"><?php echo tt('Use wallet') ?></a>
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
              <?php echo tt('Provide detailed links to sites that in your opinion proves and verifies your identity. It should include at least one web page or social profile that you own:') ?>
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
                <?php echo tt("Copy the top code and post it on your social profile page or include the bottom code on your site's homepage. This is to verify that you are the owner/administrator of the relevant site and or page.") ?>
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
        <div class="col-lg-11 form-group">
          <div class="row">
            <div class="col-lg-7 col-lg-offset-5">
              <button type="submit" class="btn btn-primary"><?php echo tt('Send Request') ?></button>
              <a href="<?php echo $href_cancel ?>" onclick="return confirm('<?php echo tt("Are you sure?") ?>')" class="btn btn-default"><?php echo tt('Cancel') ?></a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php echo $footer ?>
