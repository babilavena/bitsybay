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
      <h1 id="tables"><?php echo tt('Product list') ?></h1>
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
    <?php echo $alert_danger ?>
    <?php echo $alert_success ?>
    <div class="bs-component">
      <?php if ($products) { ?>
        <table class="table table-striped table-hover ">
          <thead>
            <tr>
              <th><?php echo tt('Pic') ?></th>
              <th><?php echo tt('Title') ?></th>
              <th><?php echo tt('Added') ?></th>
              <th class="text-center"><?php echo tt('Regular') ?></th>
              <th class="text-center"><?php echo tt('Exclusive') ?></th>
              <th class="text-center"><?php echo tt('Views') ?></th>
              <th class="text-center"><?php echo tt('Fans') ?></th>
              <th class="text-center"><?php echo tt('Sales') ?></th>
              <th class="text-center"><?php echo tt('Status') ?></th>
              <th><?php echo tt('Action') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product) { ?>
              <tr>
                <td><img src="<?php echo $product['image'] ?>" alt="<?php echo $product['title'] ?>" title="<?php echo $product['title'] ?>" /></td>
                <td><?php echo $product['title'] ?></td>
                <td><?php echo $product['date_added'] ?></td>
                <td class="text-center">
                  <?php if ($product['regular_status']) { ?>
                    <?php if ($product['special_regular_price']) { ?>
                      <div class="special-price"><?php echo $product['special_regular_price'] ?></div>
                      <div class="default-price"><?php echo $product['regular_price'] ?></div>
                    <?php } else { ?>
                      <div class="price">
                        <div class="regular-price"><?php echo $product['regular_price'] ?></div>
                      </div>
                    <?php } ?>
                  <?php } else { ?>
                    <div class="text-muted glyphicon glyphicon-remove-sign"></div>
                  <?php } ?>
                </td>
                <td class="text-center">
                  <?php if ($product['exclusive_status']) { ?>
                    <?php if ($product['special_exclusive_price']) { ?>
                      <div class="special-price"><?php echo $product['special_exclusive_price'] ?></div>
                      <div class="default-price exclusive-price"><?php echo $product['exclusive_price'] ?></div>
                    <?php } else { ?>
                      <div class="price">
                        <div class="exclusive-price"><?php echo $product['exclusive_price'] ?></div>
                      </div>
                    <?php } ?>
                  <?php } else { ?>
                    <div class="exclusive-price glyphicon glyphicon-remove-sign"></div>
                  <?php } ?>
                </td>
                <td class="text-center"><?php echo $product['viewed'] ?></td>
                <td class="text-center"><?php echo $product['favorites'] ?></td>
                <td class="text-center"><?php echo $product['sales'] ?></td>
                <td class="text-center <?php echo !$product['status'] ? 'text-warning' : 'text-success' ?>"><?php echo !$product['status'] ? tt('Pending') : tt('Active') ?></td>
                <td>
                  <div class="btn-group">
                    <a href="<?php echo $product['href_edit'] ?>" class="btn btn-warning"><?php echo tt('Edit') ?></a>
                    <a href="#" class="btn btn-warning dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <?php if (in_array($product['status'], array('blocked', 'disabled'))) { ?>
                        <li class="disabled"><a><?php echo tt('View in Catalog') ?></a></li>
                      <?php } else { ?>
                        <li><a href="<?php echo $product['href_view'] ?>"><?php echo tt('View in Catalog') ?></a></li>
                      <?php } ?>
                      <li><a href="<?php echo $product['href_download'] ?>"><?php echo tt('Download') ?></a></li>
                      <li class="divider"></li>
                      <li><a href="<?php echo $product['href_delete'] ?>" onclick="return confirm('Are you sure that you want to permanently delete <?php echo $product['title'] ?>?')"><?php echo tt('Delete') ?></a></li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="9"></td>
              <td>
                <a href="<?php echo $href_account_product_create ?>" class="btn btn-success">
                  <i class="glyphicon glyphicon-plus"></i>
                  <?php echo tt('Add product') ?>
                </a>
              </td>
            </tr>
          </tfoot>
        </table>
      <?php } else { ?>
        <div class="text-center">
          <span class="glyphicon glyphicon-cloud-upload" style="font-size:38px;color:#CCC;margin-top:20px"></span>
          <br /><br />
          <a href="<?php echo $href_account_product_create ?>" class="btn btn-lg btn-success">
            <i class="glyphicon glyphicon-plus"></i>
            <?php echo tt('Add product') ?>
          </a>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php echo $footer ?>
