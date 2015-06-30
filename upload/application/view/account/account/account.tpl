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
      <h1 id="forms"><?php echo sprintf(tt('Hi, %s'), $username) ?></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $module_breadcrumbs ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-12" id="accountAlert">
    <?php echo $alert_danger ?>
    <?php echo $alert_warning ?>
    <?php echo $alert_success ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-3">
    <?php echo $module_account ?>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
    <h3><?php echo tt('Profile info') ?></h3>
    <div class="row">
      <div class="col-lg-5 col-md-5 col-sm-3 col-xs-12">
      <form role="form" class="form-vertical" id="accountForm" action="<?php echo $avatar_action ?>" method="POST" enctype="multipart/form-data">
        <div class="btn-file user-avatar">
          <img src="<?php echo $avatar_url ?>" alt="" id="userAvatar" />
          <input type="file" name="avatar" id="inputAvatar" value="" />
        </div>
      </form>
      </div>
      <div class="col-lg-7 col-md-7 col-sm-9 col-xs-12">
        <p><?php echo $username ?> <sup><a href="<?php echo $href_account_account_update ?>"><i class="glyphicon glyphicon-pencil"></i> edit</a></sup></p>
        <p><?php echo tt('Joined on') ?>: <?php echo $date_added ?></p>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-4 col-sm-6">
    <h3><?php echo tt('Account status') ?></h3>
    <p><?php echo tt('Active') ?>: <?php echo $active ? '<span class="text-success">' . tt('Yes') . '</span>' : '<span class="text-danger">' . tt('No') . '</span>' ?></p>
    <p><?php echo tt('Approved') ?>: <?php echo $approved ? '<span class="text-success">' . tt('Yes') . '</span>' : '<span class="text-warning">' . tt('No') . '</span>' ?></p>
    <p><?php echo tt('Verified') ?>: <?php echo $verified ? '<span class="text-success">' . tt('Yes') . '</span>' : '<span class="text-warning">' . tt('No') . '</span>' ?></p>
  </div>
  <div class="col-lg-2 col-md-4 col-sm-6">
    <h3><?php echo tt('Get started') ?></h3>
      <p><a href="<?php echo $href_catalog_search_favorites ?>"><?php echo tt('Favorites') ?></a></p>
      <p><a href="<?php echo $href_catalog_search_purchased ?>"><?php echo tt('Purchases') ?></a></p>
      <p><a href="<?php echo $href_account_product_create ?>" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> <?php echo tt('Make money') ?></a></p>
  </div>
</div>
<div class="row">
  <div class="col-lg-9 col-lg-offset-3">
    <h3><?php echo tt('Disk usage') ?></h3>
    <?php echo $module_quota_bar ?>
  </div>
</div>
<script type="text/javascript"><!--

  $('#inputAvatar').change(function() {

    var formData = new FormData($('#accountForm').get(0));

    $.ajax({
        url: 'index.php?route=account/account/uploadAvatar',
        type: 'POST',
        beforeSend: function(e) {
          $('.alert-danger, .alert-success').remove();
        },
        success: function (e) {
          if (e['error_message']) {
            $('#accountAlert').html('<div class="alert alert-dismissible alert-danger">' + e['error_message'] + '</div>');
          } else if (e['success_message']) {
            $('#userAvatar').attr('src', e['url']);
          } else {
            alert('Internal server error! Please, try again later.');
          }
        },
        error: function (e) {
          alert('Connection error! Please, try again later.');
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
  });
//--></script>
<?php echo $footer ?>
