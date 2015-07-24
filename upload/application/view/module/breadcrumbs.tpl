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
  <ul class="breadcrumb" xmlns:v="http://rdf.data-vocabulary.org/#">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php if ($breadcrumb['active']) { ?>
        <li typeof="v:Breadcrumb" class="active"><a href="<?php echo $breadcrumb['href'] ?>" rel="v:url" property="v:title"><?php echo $breadcrumb['name'] ?></a></li>
      <?php } else { ?>
        <li typeof="v:Breadcrumb"><a href="<?php echo $breadcrumb['href'] ?>" rel="v:url" property="v:title"><?php echo $breadcrumb['name'] ?></a></li>
      <?php } ?>
    <?php } ?>
  </ul>
</div>
