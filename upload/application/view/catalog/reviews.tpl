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

<?php if ($reviews) { ?>
  <?php foreach ($reviews as $review) { ?>
    <div class="review-item">
      <div class="review-date"><?php echo $review['date_added'] ?></div>
      <div class="review-user">
        <a href="<?php echo $review['href_user'] ?>">
          <?php echo $review['username'] ?>
        </a>
        <?php if ($review['favorite']) { ?>
          <sup><i class="glyphicon glyphicon-heart"></i> <?php echo tt('this product') ?></sup>
        <?php } ?>
      </div>
      <div class="review-body"><?php echo $review['review'] ?></div>
    </div>
  <?php } ?>
<?php } else { ?>
    <?php echo tt('There are no reviews for this product') ?>
<?php } ?>
