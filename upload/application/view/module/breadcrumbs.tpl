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
  <ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
    <?php $i = 1 ?>
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" <?php echo ($breadcrumb['active'] ? 'class="active"' : false) ?>>
        <a href="<?php echo $breadcrumb['href'] ?>" itemprop="item">
          <span itemprop="name"><?php echo $breadcrumb['name'] ?></span>
        </a>
        <meta itemprop="position" content="<?php echo $i++ ?>" />
      </li>
    <?php } ?>
  </ul>
</div>
