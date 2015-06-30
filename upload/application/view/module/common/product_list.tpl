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
  <div class="page-header text-center">
    <h2><?php echo tt('Last offers') ?></h2>
  </div>
  <div class="row">
    <div class="col-lg-14">
  <?php if ($products) { ?>
    <div class="catalog-category-product grid">
      <?php foreach ($products as $product) { ?>
        <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
          <div class="product-item">
            <div class="product-image"><a href="<?php echo $product['href_view'] ?>"><img src="<?php echo $product['src'] ?>" alt="<?php echo $product['title'] ?>" /></a></div>
            <div class="product-title"><h4><a href="<?php echo $product['href_view'] ?>"><?php echo $product['title'] ?></a></h4></div>
              <div class="product-price">
                <?php if ($product['has_special_regular_price'] || $product['has_special_exclusive_price']) { ?>
                  <div class="special-price">
                    <?php if ($product['has_special_regular_price']) { ?>
                      <span class="regular-price"><?php echo $product['special_regular_price'] ?></span>
                    <?php } else if ($product['has_special_exclusive_price']) { ?>
                      <span class="exclusive-price"><?php echo $product['special_exclusive_price'] ?></span>
                    <?php } ?>
                    <sup class="time-left"><?php echo $product['special_expires'] ?></sup>
                  </div>

                <!--
                <?php if ($product['has_special_regular_price']) { ?>
                  <div class="default-price"><?php echo $product['regular_price'] ?></div>
                <?php } else if ($product['has_special_exclusive_price']) { ?>
                  <div class="default-price">
                    <span class="exclusive-price"><?php echo $product['exclusive_price'] ?></span>
                  </div>
                <?php } ?>
                -->

                <?php } else { ?>
                  <?php if ($product['has_regular_price']) { ?>
                    <div class="regular-price"><?php echo $product['regular_price'] ?></div>
                  <?php } else if ($product['has_exclusive_price']) { ?>
                    <div class="exclusive-price"><?php echo $product['exclusive_price'] ?></div>
                  <?php } ?>
                <?php } ?>
              </div>
              <div class="product-action">
                <?php if ($product['product_order_status'] == 'approved') { ?>
                  <div class="btn-group">
                    <a href="<?php echo $product['href_download'] ?>" class="btn btn-success"><i class="glyphicon glyphicon-circle-arrow-down"></i> <?php echo tt('Get') ?></a>
                    <a href="#" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a href="<?php echo $product['href_view'] ?>"><?php echo tt('Details') ?></a></li>
                      <li class="divider"></li>
                      <li><a data-toggle="modal" data-target="#productReport" onclick="report(<?php echo $product['product_id'] ?>, '<?php echo tt('Report') ?>: <?php echo $product['title'] ?>')"><?php echo tt('Report') ?></a></li>
                    </ul>
                  </div>
                <?php } else if ($product['product_order_status'] == 'processed') { ?>
                <div class="btn-group">
                  <a class="btn btn-info disabled dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-hourglass"></i> <?php echo tt('Transaction...') ?></a>
                </div>
                <?php } else { ?>
                  <div class="btn-group">
                    <a class="btn btn-primary" href="<?php echo $product['href_view'] ?>"><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo tt('Buy') ?></a>
                    <a href="#" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <?php if ($product['demo']) { ?>
                        <li><a href="<?php echo $product['href_demo'] ?>" target="_blank"><?php echo tt('Live preview') ?></a></li>
                        <li class="divider"></li>
                      <?php } ?>
                      <li><a data-toggle="modal" data-target="#productReport" onclick="report(<?php echo $product['product_id'] ?>, '<?php echo tt('Report') ?>: <?php echo $product['title'] ?>')"><?php echo tt('Report') ?></a></li>
                    </ul>
                  </div>
                <?php } ?>
                <div class="btn btn-success" onclick="favorite(<?php echo $product['product_id'] ?>, <?php echo (int) $user_is_logged ?>)" id="productFavoriteButton<?php echo $product['product_id'] ?>"><i class="glyphicon <?php echo $product['favorite'] ? 'glyphicon-heart' : 'glyphicon-heart-empty' ?>"></i> <span><?php echo $product['favorites'] ?></span></div>
              </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <?php } else { ?>
      <div class="col-lg-12">
        <div class="product-not-found"><?php echo tt('Products not found. Try again later') ?></div>
      </div>
    <?php } ?>
  </div>
  </div>
</div>
