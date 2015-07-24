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
      <h1 id="forms"><?php echo $title ?></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $module_breadcrumbs ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <?php echo $alert_warning ?>
    <?php echo $alert_success ?>
    <?php echo $alert_danger ?>
  </div>
</div>
<div class="catalog-product">
  <div class="row">
    <div class="col-lg-4 col-md-5 col-sm-12 col-xs-12">
      <div class="bs-component product-image">
        <img onclick="zoomImage('<?php echo $product_image_orig_url ?>', '<?php echo $product_title ?>')" src="<?php echo $product_image_url ?>" alt="<?php echo $product_title ?>" title="<?php echo $product_title ?>" data-toggle="modal" data-target="#zoomImage" />
      </div>
      <?php if ($product_images > 1) { ?>
        <div class="bs-component product-images">
          <?php foreach ($product_images as $key => $image) { ?>
            <img onclick="zoomImage('<?php echo $image['original'] ?>', '<?php echo $image['title'] ?>')" src="<?php echo $image['preview'] ?>" alt="<?php echo $image['title'] ?>" title="<?php echo $image['title'] ?>" data-toggle="modal" data-target="#zoomImage" />
          <?php } ?>
        </div>
      <?php } ?>
      <?php if ($product_videos > 1) { ?>
        <div class="bs-component product-videos">
          <?php foreach ($product_videos as $key => $video) { ?>
            <div class="product-video <?php echo $color_labels[$key+3] ?>" onclick="zoomVideo('<?php echo $video['url'] ?>', '<?php echo $video['title'] ?>')" data-toggle="modal" data-target="#zoomVideo"><i class="glyphicon glyphicon-facetime-video"></i></div>
          <?php } ?>
        </div>
      <?php } ?>
      <?php if ($product_audios > 1) { ?>
        <div class="bs-component product-audios">
          <?php foreach ($product_audios as $key => $audio) { ?>
            <div class="product-audio <?php echo $color_labels[$key+3] ?>" onclick="zoomAudio('<?php echo $audio['url'] ?>&amp;auto_play=true&amp;hide_related=false&amp;show_comments=false&amp;show_user=false&amp;show_reposts=false&amp;buying=false&amp;sharing=false&amp;download=false&amp;download=show_bpm&amp;show_artwork=true&amp;show_playcount=false', '<?php echo $audio['title'] ?>')" data-toggle="modal" data-target="#zoomAudio"><i class="glyphicon glyphicon-music"></i></div>
          <?php } ?>
        </div>
      <?php } ?>
      <div id="zoomImage" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="zoomImage" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 <h4 class="modal-title"><?php echo $product_title ?></h4>
               </div>
              <div class="modal-body">
                <img src="<?php echo $product_image_url ?>" alt="<?php echo $product_title ?>" title="<?php echo $product_title ?>" />
              </div>
          </div>
        </div>
      </div>
      <div id="zoomVideo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="zoomVideo" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 <h4 class="modal-title"><?php echo $product_title ?></h4>
               </div>
              <div class="modal-body">
                <iframe width="570" height="400"></iframe>
              </div>
          </div>
        </div>
      </div>
      <div id="zoomAudio" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="zoomAudio" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                 <h4 class="modal-title"><?php echo $product_title ?></h4>
               </div>
              <div class="modal-body">
                <iframe width="570" height="166"></iframe>
              </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12">
      <div class="bs-component catalog-product-info">
        <div class="product-misc">
          <?php if ($verified) { ?>
            <div class="text-success"><span class="glyphicon glyphicon-ok"></span> <strong><?php echo tt('Verified Seller') ?></strong></div>
          <?php } else { ?>
            <div><span class="glyphicon glyphicon-eye-close"></span> <strong><?php echo tt('Unverified Seller') ?></strong></div>
          <?php } ?>
          <div><?php echo sprintf(tt('Release: %s'), $product_date_added) ?></div>
          <div><?php echo sprintf(tt('Update: %s'), $product_date_modified) ?></div>
          <div><?php echo $product_sales ? sprintf(tt('Sales: %s'), $product_sales)  : false ?></div>
        </div>
        <div class="product-user">
          <?php if ($product_is_self) { ?>
          <a href="<?php echo $product_href_user ?>"><?php echo tt('My') ?></a> <?php echo tt('product') ?>
          <?php } else { ?>
            <a href="<?php echo $product_href_user ?>"><?php echo $product_username ?></a> <?php echo tt('made it for you') ?>
          <?php } ?>
        </div>
        <div class="product-price">
          <form id="priceForm" name="license-form" method="POST" action="<?php echo $license_form_action ?>">
            <?php if ($product_has_special_regular_price || $product_has_special_exclusive_price) { ?>
              <div class="price-text"><?php echo tt('Special price') ?></div>
              <div class="special-price">
                <?php if ($product_has_special_regular_price) { ?><label><input type="radio" name="license" value="regular" <?php echo ($regular ? 'checked="checked"' : false) ?> /> <span class="regular-price <?php echo $product_has_special_exclusive_price ? 'bold' : false ?>"><?php echo $product_special_regular_price ?></span></label><?php } ?>
                <?php if ($product_has_special_regular_price && $product_has_special_exclusive_price) { ?>/<?php } ?>
                <?php if ($product_has_special_exclusive_price) { ?><label><input type="radio" name="license" value="exclusive" <?php echo ($exclusive ? 'checked="checked"' : false) ?> /> <span class="exclusive-price"><?php echo $product_special_exclusive_price ?></span></label><?php } ?>
                <sup class="time-left"><?php echo $product_special_expires ?></sup>
              </div>
              <div class="price-text"><?php echo tt('Default') ?></div>
              <?php if ($product_has_special_regular_price) { ?><span class="default-price"><?php echo $product_regular_price ?></span><?php } ?>
              <?php if ($product_has_special_exclusive_price) { ?><span class="default-price exclusive-price"><?php echo $product_exclusive_price ?></span><?php } ?>
            <?php } else { ?>
              <?php if ($product_has_regular_price) { ?>
                <div class="price-text"><?php echo tt('Price') ?></div>
                <div class="regular-price"><label><input type="radio" name="license" value="regular" <?php echo ($regular ? 'checked="checked"' : false) ?> /> <?php echo $product_regular_price ?></label></div>
              <?php } ?>
              <?php if ($product_has_exclusive_price) { ?>
                <div class="price-text"><?php echo tt('Exclusive price') ?></div>
                <div class="exclusive-price"><label><input type="radio" name="license" value="exclusive" <?php echo ($exclusive ? 'checked="checked"' : false) ?> /> <?php echo $product_exclusive_price ?></label></div>
              <?php } ?>
            <?php } ?>
          </form>
        </div>
        <div class="product-action">
          <?php if ($product_demo) { ?>
            <div class="btn-group">
              <a class="btn btn-primary btn-lg" href="<?php echo $product_href_demo ?>" target="_blank"><i class="glyphicon glyphicon-eye-open"></i> <?php echo tt('Live preview') ?></a>
              <a href="#" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
              <?php if ($product_demos) { ?>
                <ul class="dropdown-menu">
                  <?php foreach ($product_demos as $demo) { ?>
                    <li><a href="<?php echo $demo['url'] ?>" target="_blank"><?php echo $demo['title'] ?></a></li>
                  <?php } ?>
                </ul>
              <?php } ?>
            </div>
          <?php } ?>
          <?php if ($product_order_status == 'approved') { ?>
            <div class="btn-group">
              <a href="<?php echo $product_href_download ?>" class="btn btn-success btn-lg"><i class="glyphicon glyphicon-circle-arrow-down"></i> <?php echo tt('Get') ?></a>
              <a href="#" class="btn btn-success dropdown-toggle btn-lg" data-toggle="dropdown"><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-toggle="modal" data-target="#productReport" onclick="report(<?php echo $product_id ?>, '<?php echo tt('Report') ?>: <?php echo $product_title ?>')"><?php echo tt('Report') ?></a></li>
              </ul>
            </div>
          <?php } else if ($product_order_status == 'processed') { ?>
            <div class="btn-group">
              <a class="btn btn-info btn-lg disabled dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-hourglass"></i> <?php echo tt('Confirmation...') ?></a>
            </div>
          <?php } else { ?>
            <div class="btn-group">
              <a class="btn btn-primary btn-lg" id="buyProduct"><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo tt('Buy') ?></a>
              <a href="#" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a data-toggle="modal" data-target="#productReport" onclick="report(<?php echo $product_id ?>, '<?php echo tt('Report') ?>: <?php echo $product_title ?>')"><?php echo tt('Report') ?></a></li>
              </ul>
            </div>
          <?php } ?>
          <div class="btn btn-success btn-lg" onclick="favorite(<?php echo $product_id ?>, <?php echo (int) $user_is_logged ?>)" id="productFavoriteButton<?php echo $product_id ?>"><i class="glyphicon <?php echo $product_favorite ? 'glyphicon-heart' : 'glyphicon-heart-empty' ?>"></i> <span><?php echo $product_favorites ?></span></div>
        </div>
      </div>
      <?php if ($product_tags) { ?>
        <div class="bs-component catalog-product-tags">
          <?php foreach ($product_tags as $key => $tag) { ?>
            <a href="<?php echo $tag['url'] ?>" class="label <?php echo $color_labels[$key] ?>"><i class="glyphicon glyphicon-record"></i> <?php echo $tag['name'] ?></a>
          <?php } ?>
        </div>
      <?php } ?>
      <div class="bs-component catalog-product-details">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#productDescription" data-toggle="tab"><?php echo tt('Description') ?></a></li>
          <li><a href="#productLicense" data-toggle="tab"><?php echo tt('License') ?></a></li>
          <li><a href="#productReviews" data-toggle="tab"><?php echo tt('Reviews') ?></a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="productDescription">
            <p><?php echo $product_description ?></p>
          </div>
          <div class="tab-pane fade" id="productLicense">
            <?php echo $license ?>
            <p><?php echo tt('This is a human-readable summary of (and not a substitute for) the') ?> <a href="licenses"><?php echo tt('Licensing Policy') ?></a>.</p>
          </div>
          <div class="tab-pane fade" id="productReviews">
            <div id="productReviewList" class="product-reviews"></div>
            <div id="productReviewForm" class="product-review-form">
              <?php if ($user_is_logged) { ?>
                <div class="review-form"><textarea name="review" id="productReviewContent" class="form-control" placeholder="<?php echo tt('Please explain you review and click submit button') ?>"></textarea></div>
                <div class="review-control"><div class="btn btn-primary" id="productReviewButton"> <span><?php echo tt('Submit') ?></span></div></div>
              <?php } else { ?>
                <textarea name="review" id="productReviewContent" class="form-control disabled" disabled="disabled"><?php echo tt('Please login or register to write reviews') ?></textarea>
                <div class="review-control"><div class="btn btn-primary disabled" id="productReviewButton"> <span><?php echo tt('Submit') ?></span></div></div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="productPurchase" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?php echo sprintf(tt('%s Purchase'), $title) ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-lg-12">
            <div class="modal-loading"><i class="glyphicon glyphicon-hourglass"></i> <?php echo tt('Please, wait...') ?></div>
          </div>
        </div>

        <div class="row hide" id="paymentResult">
          <div class="col-lg-4" id="paymentResultImg"></div>
          <div class="col-lg-8 text-left" style="padding: 20px">
            <div class="row">
              <div class="col-lg-12">
                <p></p>
                <pre><?php echo tt('Loading...') ?></pre>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6 text-left">
                <a id="initBitcoinWallet" class="btn btn-primary" href=""><?php echo tt('Use wallet') ?></a>
              </div>
              <div class="col-lg-6 text-right">
                <div id="paymentTimer" class="text-muted">15:00</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--

  $(document).ready(function() {

    // License changing
    $('#priceForm input[name=license]').change(function () {
      $('#priceForm').submit();
    });

    // Product ordering
    $('#buyProduct').click(function () {
      <?php if (!$user_is_logged) { ?>
        $('#loginForm').modal('toggle');
      <?php } else { ?>
        $.ajax({
            url:  'index.php?route=order/bitcoin/create',
            type: 'POST',
            data: { product_id: <?php echo $product_id ?>, license: $('#priceForm input[name=license]:checked').val() },
            beforeSend: function(e) {
              $('#productPurchase').modal('toggle');
            },
            success: function (e) {
              if (e['status']) {
                $('#paymentResult').removeClass('hide');
                $('#productPurchase .modal-loading').hide();
                $('#productPurchase pre').html(e['address']);
                $('#productPurchase p').html(e['text']);
                $('#paymentResultImg').html('<img src="' + e['src'] + '" alt="' + e['address'] + '" />');
                $('#initBitcoinWallet').attr('href', e['href']);

                timer(900, document.getElementById('paymentTimer'));
              } else {
                $('#productPurchase .modal-loading').html('Maintenance mode. Please wait a few minutes and try again.');
              }
            },
            error: function (e) {
              $('#productPurchase').modal('toggle');
              alert('Connection error! Please, try again later.');
            }
        });
      <?php } ?>
    });

    // Init reviews
    function loadReviews(product_id) {
        $('#productReviewList').load('index.php?route=catalog/product/reviews&product_id=' + product_id);
    }

    loadReviews(<?php echo $product_id ?>);

    // Add review
    $('#productReviewButton').click(function () {

      if ('' == $('#productReviewContent').val()) {
        $('#productReviewForm .review-form').addClass('has-error');
      } else {
        $.ajax({
            url:  'index.php?route=catalog/product/review',
            type: 'POST',
            data: { product_id: <?php echo $product_id ?>, review: $('#productReviewContent').val() },
            beforeSend: function(e) {

              $('#productReviewForm .alert').remove();
              $('#productReviewForm .review-form').removeClass('has-error');
              $('#productReviewButton').addClass('disabled').prepend('<i class="glyphicon glyphicon-hourglass"></i> ');
              $('#productReviewContent').attr('disabled', true);

            },
            success: function (e) {
              if (e['success_message']) {

                $('#productReviewContent').val('').before('<div class="alert alert-dismissible alert-success"><button type="button" class="close" data-dismiss="alert">×</button>' + e['success_message'] + '</div>');
                loadReviews(<?php echo $product_id ?>);

              } else if (e['error_message']) {
                alert(e['error_message']);
                $('#productReviewForm .review-form').addClass('has-error');
              }
            },
            error: function (e) {
              alert('Internal server error! Please, try again later.');
            }
        });

        $('#productReviewButton').removeClass('disabled').find('i').remove();
        $('#productReviewContent').attr('disabled', false);
      }
    });
  });
//--></script>
<?php echo $footer ?>
