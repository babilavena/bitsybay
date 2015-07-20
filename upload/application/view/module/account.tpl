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

<div class="bs-component">
  <div class="list-group">
    <div class="list-group-item active"><?php echo tt('Account') ?></div>
    <a href="<?php echo $href_account_account ?>" class="list-group-item"><span class="glyphicon glyphicon-briefcase"></span>&nbsp; <?php echo tt('Profile') ?></a>
    <?php if ($verified) { ?>
      <a href="<?php echo $href_account_account_verification ?>" class="list-group-item"><span class="glyphicon glyphicon-leaf"></span>&nbsp; <?php echo tt('Verification') ?></a>
    <?php } ?>
    <a href="<?php echo $href_account_account_subscription ?>" class="list-group-item"><span class="glyphicon glyphicon-envelope"></span>&nbsp; <?php echo tt('Subscriptions') ?></a>
    <a href="<?php echo $href_account_account_edit ?>" class="list-group-item"><span class="glyphicon glyphicon-cog"></span>&nbsp; <?php echo tt('Account settings') ?></a>
    <a href="<?php echo $href_account_account_affiliate ?>" class="list-group-item"><span class="glyphicon glyphicon-tower"></span>&nbsp; <?php echo tt('Affiliate') ?></a>
    <a href="<?php echo $href_account_account_logout ?>" class="list-group-item"><span class="glyphicon glyphicon-log-out"></span>&nbsp; <?php echo tt('Logout') ?></a>
  </div>
</div>




