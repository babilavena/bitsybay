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
      <h1 id="forms"><?php echo tt('Page not found') ?></h1>
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
    <div class="col-lg-6 not-found-code">
      <span>404</span> <span>page not found</span>
    </div>
    <div class="col-lg-5 col-lg-offset-1 not-found-navigator">
      <div class="bs-component">
        <h3><?php echo tt('Do not forget') ?></h3>
        <ul>
          <li>Visit the <a href="<?php echo $href_common_information_faq ?>">F.A.Q. page</a></li>
          <li>Use the <a href="<?php echo $href_catalog_search ?>">search</a> to find specific content</li>
        </ul>
        <h4><?php echo tt('Navigate') ?></h4>
        <ul>
          <?php foreach ($categories as $category) { ?>
            <?php if ($category['child']) { ?>
              <li>
                <a href="<?php echo $category['href'] ?>"><?php echo $category['title'] ?></a>
                <ul>
                  <?php foreach ($category['child'] as $child_category) { ?>
                    <li><a href="<?php echo $child_category['href'] ?>"><?php echo $child_category['title'] ?></a></li>
                  <?php } ?>
                </ul>
              </li>
            <?php } else { ?>
              <li><a href="<?php echo $category['href'] ?>"><?php echo $category['title'] ?></a></li>
            <?php } ?>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php echo $footer ?>
