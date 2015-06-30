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

<div class="bs-component search">

  <div class="jumbotron">
    <div class="<?php echo $class ?>">
    <div class="input-group" id="topSearchForm">
      <input name="action" id="topSearchAction" type="hidden" value="<?php echo $action ?>" />
      <input name="query" id="topSearchQuery" class="form-control input-lg" type="text" id="inputLarge" placeholder="<?php echo tt('Search in catalog') ?>" value="<?php echo $query ?>" />
      <span class="input-group-btn">
        <button id="topSearchButton" class="btn btn-primary input-lg" type="button"><i class="glyphicon glyphicon-search"></i> <?php echo tt('Search') ?></button>
      </span>
    </div>
    <div class="tags">
      <?php echo tt('e.g.') ?>
      <?php $tag_data = array() ?>
      <?php foreach ($tags as $tag) { ?>
        <?php $tag_data[] = '<a href="' . $tag['url'] . '">' . $tag['name'] . '</a>' ?>
      <?php } ?>
      <?php echo implode(', ', $tag_data) ?>
    </div>
  </div>
  </div>
</div>

