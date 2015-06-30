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
  <span class="text-<?php echo $class ?>"><?php echo ($available_space == $total_space ? sprintf(tt('Available: %s Mb'), $total_space) : sprintf(tt('Available: %s of %s Mb'), $available_space, $total_space)) ?></span>
  <?php if ($file_percent) { ?>
    <span class="text-muted">/</span> <span class="text-info"><?php echo sprintf(tt('Current: %s Mb'), $file_space) ?></span>
  <?php } ?>
  <div class="progress">
    <div class="progress-bar progress-bar-<?php echo $class ?>" style="width: <?php echo $used_percent ?>%"></div>
    <?php if ($file_percent) { ?>
    <div class="progress-bar progress-bar-info" style="width: <?php echo $file_percent ?>%"></div>
    <?php } ?>
  </div>
</div>
