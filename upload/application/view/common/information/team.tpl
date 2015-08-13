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
        <h1 id="forms"><?php echo tt('Team') ?></h1>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <?php echo $module_breadcrumbs ?>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12 information-team">
      <div class="row">
        <div class="col-lg-4 text-center">
          <img src="<?php echo URL_BASE ?>image/team/eugene.jpg" />
          <h2>Eugene Kulihin<br /><small>@Lifescale, @Shaman</small></h2>
          <p>Project Creator, Developer</p>
        </div>
        <div class="col-lg-4 text-center jumbotron">
          <p>Our team is only peoples, who love this project and make it better <i class="glyphicon glyphicon-thumbs-up"></i></p>
          <p>There are no corporation and authorized fund. We fund our support and contribution instead money.</p>
        </div>
        <div class="col-lg-4 text-center">
          <img src="<?php echo URL_BASE ?>image/team/larisa.jpg" />
          <h2>Larisa Bodnar<br /><small>@Barbaryska</small></h2>
          <p>Content Analyst, Support</p>
        </div>
      </div>
      <?php if ($contributors) { ?>
        <div class="row contributors text-center">
          <div class="col-lg-12">
            <h2>Contributors</h2>
            <?php foreach ($contributors as $contributor) { ?>
              <?php $contribution = $contributor['contributions'] * 100 / $contributions ?>
              <div class="contributor">
                <a href="<?php echo $contributor['href_profile'] ?>" rel="nofollow" target="_blank">
                  <img src="<?php echo $contributor['href_avatar'] ?>" />
                </a>
                <a href="<?php echo $contributor['href_profile'] ?>" rel="nofollow" target="_blank">
                  <h5><?php echo $contributor['username'] ?> (<?php echo round($contribution) ?>%)</h5>
                </a>
              </div>
            <?php } ?>
            <div class="contributor">
              <a href="<?php echo URL_GITHUB ?>" class="join" rel="nofollow" target="_blank">+</a>
              <a href="<?php echo URL_GITHUB ?>" rel="nofollow" target="_blank">
                <h5>Join Us</h5>
              </a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
<?php echo $footer ?>
