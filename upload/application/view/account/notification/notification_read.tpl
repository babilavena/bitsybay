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
      <h1 id="tables"><?php echo tt('Notifications') ?></h1>
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
    <?php echo $module_notification ?>
  </div>
  <div class="col-lg-9">
    <div class="bs-component">
      <span class="label <?php echo $label['class'] ?>"><?php echo $label['name'] ?></span>&nbsp;
      <span class="text-muted small"><?php echo $date_added ?></span>
      <h2><?php echo $title ?></h2>
      <p><?php echo $description ?></p>
    </div>
  </div>
</div>
<?php echo $footer ?>
