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

      <footer>
        <div class="row footer">
          <div class="col-lg-3 col-md-2 col-sm-3 col-xs-4">
            <ul class="list-unstyled">
              <li><h5><?php echo tt('Explore') ?></h5>
                <ul class="list-unstyled menu">
                  <li><a href="<?php echo $href_common_information_about ?>"><?php echo tt('About Us') ?></a></li>
                  <li><a href="<?php echo $href_common_information_terms ?>"><?php echo tt('Terms of Service') ?></a></li>
                  <li><a href="<?php echo $href_common_information_licenses ?>"><?php echo tt('Licenses') ?></a></li>
                </ul>
              </li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-2 col-sm-2 col-xs-4">
            <ul class="list-unstyled">
              <li><h5><?php echo tt('Help Center') ?></h5>
                <ul class="list-unstyled menu">
                  <li><a href="<?php echo $href_common_information_faq ?>"><?php echo tt('F.A.Q') ?></a></li>
                  <li><a data-toggle="modal" data-target="#productReport" onclick="report(0, '<?php echo tt('Abuse Report') ?>')"><?php echo tt('Abuse Report') ?></a></li>
                  <li><a href="<?php echo $href_common_contact ?>"><?php echo tt('Contact Us') ?></a></li>
                </ul>
              </li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-2 col-sm-2 col-xs-3">
            <ul class="list-unstyled">
              <li><h5><?php echo tt('Network') ?></h5>
                <ul class="list-unstyled menu">
                  <li><a href="http://github.com/bitsybay" target="_blank"><?php echo tt('GitHub') ?></a></li>
                  <li><a href="#bitsybay" target="_blank"><?php echo tt('Twister') ?></a></li>
                </ul>
              </li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-4 col-xs-4">
            <ul class="list-unstyled pull-right">
              <li>
                <?php echo tt('The') ?> <a href="/"><?php echo tt('BitsyBay') ?></a> <?php echo tt('Project') ?> &copy; <?php echo date('Y') ?> Beta
                <div style="font-size:9px">
                  <?php echo tt('Powered by') ?> <a href="https://github.com/bitsybay" target="_blank" rel="nofollow"><?php echo tt('BitsyBay Engine') ?></a>,
                  <!--
                  BitsyBay Engine is an open source software and you are free to remove the powered by BitsyBay Engine if you want, but its generally accepted practise to make a small donation.
                  Please donate via BitCoin to 13t5kVqpFgKzPBLYtRNShSU2dMSTP4wQYx
                  //-->
                  <?php echo tt('Bitcoin-accepting') ?> <a href="https://www.yourserver.se/portal/aff.php?aff=135" target="_blank" rel="nofollow">VPS</a>
                </div>
              </li>
            </ul>
            <div class="input-group pull-right" id="footerSearchForm">
              <input name="query" type="text" class="form-control" value="" placeholder="<?php echo tt('Search in catalog') ?>" />
              <input name="action" type="hidden" value="<?php echo $href_catalog_search ?>" />
              <span class="input-group-btn"><button class="btn btn-default" type="button"><i class="glyphicon glyphicon-search"></i></button></span>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <div class="modal fade" id="productReport" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title"><?php echo tt('Send your report') ?></h4>
          </div>
          <div class="modal-body">
            <input type="hidden" id="reportProductId" name="report_product_id" value="" />
            <textarea placeholder="<?php echo tt('Please, explain your report reason and click on the Submit button') ?>" id="reportMessage" name="report_message" class="form-control"></textarea>
            <p></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo tt('Cancel') ?></button>
            <button type="button" class="btn btn-primary" id="reportSubmit"><?php echo tt('Submit') ?></button>
          </div>
        </div>
      </div>
    </div>
    <?php if (!$user_is_logged) { ?>
      <div class="modal fade" id="loginForm" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title"><?php echo tt('Please login or create your account') ?></h4>
            </div>
            <div class="modal-body">
              <div class="col-lg-7">
                <div class="bs-component">
                  <form class="form-horizontal" action="<?php echo $action_account_account_login ?>" method="POST">
                    <fieldset>
                      <div class="form-group">
                        <input type="text" name="login" class="form-control" id="inputLogin" placeholder="<?php echo tt('Email or username') ?>" value="">
                      </div>
                      <div class="form-group">
                        <input type="password" name="password" class="form-control" id="inputPassword" placeholder="<?php echo tt('Password') ?>" value="">
                      </div>
                      <div class="form-group text-left">
                        <button type="submit" class="btn btn-primary sign-in-button"><?php echo tt('Sign In') ?></button>
                        &nbsp;&nbsp;
                        <a href="<?php echo $href_account_account_forgot ?>"><?php echo tt('Forgot Password?') ?></a>
                      </div>
                    </fieldset>
                  </form>
                </div>
              </div>
              <div class="col-lg-5">
                <a class="btn btn-success btn-lg" href="<?php echo $href_account_account_create ?>"><?php echo tt('Create an Account') ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </body>
</html>
