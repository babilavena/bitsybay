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
      <?php if ($notifications) { ?>
      <table class="table table-striped table-hover ">
        <thead>
          <tr>
            <th><?php echo tt('Title') ?></th>
            <th><?php echo tt('Label') ?></th>
            <th><?php echo tt('Received') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notifications as $notification) { ?>
            <tr>
              <td>
                <?php if (!$notification['read']) { ?>
                  <strong><a href="<?php echo $notification['href'] ?>"><?php echo $notification['title'] ?></a></strong>
                <?php } else { ?>
                  <a href="<?php echo $notification['href'] ?>"><?php echo $notification['title'] ?></a>
                <?php } ?>
              </td>
              <td><span class="label <?php echo $notification['label']['class'] ?>"><?php echo $notification['label']['name'] ?></span></td>
              <td><?php echo $notification['date_added'] ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
        <div class="text-center">
          <span class="glyphicon glyphicon-inbox" style="font-size:38px;color:#CCC;margin-top:20px"></span>
          <h4 class="text-center"><?php echo tt('No new notifications.') ?></h4>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php echo $footer ?>
