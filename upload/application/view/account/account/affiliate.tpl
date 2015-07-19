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
      <h1 id="forms"><?php echo tt('Affiliate') ?></h1>
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
    <div class="bs-component">
      <div class="row">
        <div class="col-lg-12" id="accountAlert">
          <?php echo $alert_danger ?>
          <?php echo $alert_success ?>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <div class="well">
            <p>
              <?php echo tt('When a new user clicks your referral link, signs up for an account and purchases an item, you will receive 5% of that purchase amount.') ?>
              Note that this offer will be <strong>available after 2016</strong> but all your referrals will be saved.
            </p>
            <p>
              <?php echo sprintf(tt('Also you\'ll automatically get %s BTC for each seller who send a valid verification request and referred by your affiliate link.'), $fee_amount) ?>
              <?php echo sprintf(tt('It\'s %s of the total verification cost.'), $fee_percent . '%') ?>
            </p>
            <p>
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;<?php echo tt('We reserve the right to change these fees.') ?><br />
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;<?php echo tt('To activate payouts you shall provide your valid withdraw address.') ?><br />
              <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;&nbsp;<?php echo tt('These statistics are in real time and update instantly.') ?>
            </p>
          </div>
        </div>
      </div>
      <ul class="nav nav-tabs">
        <li class="active"><a href="#affiliateReport" data-toggle="tab"><?php echo tt('Reports') ?></a></li>
        <li><a href="#affiliateLinks" data-toggle="tab"><?php echo tt('Links') ?></a></li>
        <li><a href="#affiliateSettings" data-toggle="tab"><?php echo tt('Settings') ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade" id="affiliateSettings">
          <div class="row">
            <div class="col-lg-12">
              <legend><?php echo tt('Withdraw Address') ?></legend>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="well">
                <form action="<?php echo $action ?>" method="POST" class="form-inline">
                  <span class="<?php if (isset($error['withdraw_address'])) { ?> has-error<?php } ?>">
                    <input type="text" name="withdraw_address" value="<?php echo $withdraw_address ?>" placeholder="<?php echo tt('Address') ?>" size="50" class="form-control form-group" />
                  </span>
                  <span class="<?php if (isset($error['currency_id'])) { ?> has-error<?php } ?>">
                    <select name="currency_id" class="form-control form-group" id="inputCurrencyId">
                      <?php foreach ($currencies as $id => $currency_code) { ?>
                        <option value="<?php echo $id ?>" <?php echo $currency_id == $id ? 'selected="selected"' : false ?>><?php echo $currency_code ?></option>
                      <?php } ?>
                    </select>
                  </span>
                  <button type="submit" class="btn btn-primary form-control form-group"><?php echo tt('Save Address') ?></button>
                  <?php if (isset($error['currency_id'])) { ?>
                    <div class="text-danger small"><?php echo $error['currency_id'] ?></div>
                  <?php } ?>
                  <?php if (isset($error['withdraw_address'])) { ?>
                    <div class="text-danger small"><?php echo $error['withdraw_address'] ?></div>
                  <?php } ?>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="affiliateLinks">
          <div class="row">
            <div class="col-lg-12">
              <legend><?php echo tt('Chose your target') ?></legend>
              <span><?php echo tt('Simple Link') ?></span>
              <pre><?php echo $href_ref ?></pre>
              <span><?php echo tt('HTML') ?></span>
              <pre>&lt;a href=&quot;<?php echo $href_ref ?>&quot;&gt;<?php echo sprintf(tt('%s - Sell and Buy Digital Content with BitCoin'), PROJECT_NAME) ?>&lt;/a&gt;</pre>
              <span><?php echo tt('BBC') ?></span>
              <pre>[URL=<?php echo $href_ref ?>]<?php echo sprintf(tt('%s - Sell and Buy Digital Content with BitCoin'), PROJECT_NAME) ?>[/URL]</pre>
            </div>
          </div>
        </div>
        <div class="tab-pane fade active in" id="affiliateReport">
        <div class="row">
          <div class="col-lg-12">
            <legend><?php echo tt('Your referrals') ?></legend>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 text-center">
            <div class="col-lg-3 col-md-3 col-sm-3 alert alert-warning" >
              <h5><?php echo tt('Products Purchased') ?></h5>
              <h2><?php echo $total_purchased ?></h2>
            </div>
            <div class="col-lg-3 col-lg-offset-1 col-rg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 alert alert-success" >
              <h5><?php echo tt('Verifies Requested') ?></h5>
              <h2>
                <?php echo $total_requests ?>
                <?php if ($total_joined) { ?>
                  <sup class="small"><?php echo $total_joined ?></sup>
                <?php } ?>
              </h2>
            </div>
            <div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 alert alert-info" >
              <h5><?php echo tt('Conversion Rate') ?></h5>
              <h2><?php echo $total_conversion ?> <sup class="small">%<sup></h2>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <?php if (!$referrals) { ?>
              <?php echo tt('No Referrals Found.') ?>
            <?php } else { ?>
              <table class="table table-striped table-hover">
                <thead>
                <tr>
                  <th><?php echo tt('Username') ?></th>
                  <th><?php echo tt('Joined') ?></th>
                  <th class="text-center"><?php echo tt('Verify Requests') ?></th>
                  <th class="text-center"><?php echo tt('Total Purchases') ?></th>
                  <th class="text-center"><?php echo tt('Conversion Rate') ?></th>
                </tr>
                </thead>
                <tbody>
                  <?php foreach ($referrals as $referral) { ?>
                    <tr>
                      <td><a href="<?php echo $referral['href'] ?>"><?php echo $referral['username'] ?></a></td>
                      <td><?php echo $referral['date_added'] ?></td>
                      <td class="text-center"><?php echo $total_requests ?></td>
                      <td class="text-center"><?php echo $total_purchased ?></td>
                      <td class="text-center">0%</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>
<?php echo $footer ?>
