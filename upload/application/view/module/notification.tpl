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
    <div class="list-group-item active"><?php echo tt('Filter') ?></div>
    <a href="<?php echo $href_account_notification ?>" class="list-group-item"><span class="badge"><?php echo $total_unread ?></span><?php echo tt('Unread') ?></a>
    <a href="<?php echo $href_account_notification_all ?>" class="list-group-item"><span class="badge"><?php echo $total_all ?></span><?php echo tt('All notifications') ?></a>
  </div>
</div>


