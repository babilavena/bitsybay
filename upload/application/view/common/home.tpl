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

<?php echo $header; ?>
<div class="row">
  <div class="col-lg-12 home">
    <?php if ($user_is_logged) { ?>
      <div class="page-header text-center">
        <h2><?php echo $total_products ?> by <?php echo $total_sellers ?> for <?php echo $total_buyers ?></h2>
      </div>
      <?php echo $module_search ?>
    <?php } else { ?>
      <div class="bs-component welcome">
        <div class="jumbotron">
          <div class="col-lg-7">
            <h3>Looking for a Marketplace to buy or sell digital creative in Bitcoin?</h3>

            <!-- Social:begin //-->
            <div class="social-buttons">
              <div style="vertical-align:top" class="fb-like" data-href="https://facebook.com/bitsybay" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
              <a class="tumblr-share-button" data-posttype="link" data-color="blue" data-notes="right" data-title="BitsyBay - Sell and Buy Digital Creative with BitCoin" data-caption="BitsyBay is a simple and minimalistic service to help you buy and or sell digital creative with BitCoin. It includes a marketplace for legal CMS extensions, illustrations, photos, themes and other creative assets from various authors." data-tags="BitsyBay, BitCoin, Creative, Marketplace" data-show-via="bitsybay" href="https://embed.tumblr.com/share"></a> <script>!function(d,s,id){var js,ajs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://secure.assets.tumblr.com/share-button.js";ajs.parentNode.insertBefore(js,ajs);}}(document, "script", "tumblr-js");</script>
              <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://bitsybay.com" data-via="BitsyBayProject" data-related="bitsybay" data-hashtags="BitsyBay">Tweet</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
              <a class="github-button" href="https://github.com/bitsybay/bitsybay" data-count-href="/bitsybay/bitsybay/stargazers" data-count-api="/repos/bitsybay/bitsybay#stargazers_count" data-count-aria-label="# stargazers on GitHub" aria-label="Star bitsybay/bitsybay on GitHub">Star</a>
            </div>
            <!-- Github //-->
            <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>

            <!-- Facebook //-->
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.3";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            <!-- Social:end //-->

            Then here you are:
            <ul>
              <li><?php echo $total_products ?> by <?php echo $total_sellers ?> for <?php echo $total_buyers ?></li>
              <li>Trade from any country without bank fees and other restrictions</li>
              <li>Royalty Free and Exclusive licenses</li>
              <li><?php echo QUOTA_FILE_SIZE_BY_DEFAULT ?> Mb free disk space for all new sellers and +<?php echo QUOTA_BONUS_SIZE_PER_ORDER ?> Mb for every next sale</li>
              <li>0% seller fee up to 2016, 11% later</li>
              <li>0% seller fee for project contributors forever</li>
            </ul>
          </div>
          <div class="col-lg-4 col-lg-offset-1">
            <div class="bs-component">
              <form class="form-horizontal" action="<?php echo $login_action ?>" method="POST">
                <fieldset>
                  <legend><?php echo tt('Already have an account?') ?></legend>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <input type="text" name="login" class="form-control" id="inputLogin" placeholder="<?php echo tt('Email or username') ?>" value="">
                      <?php if (isset($error['login'])) { ?>
                        <div class="text-danger"><?php echo $error['login'] ?></div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <input type="password" name="password" class="form-control" id="inputPassword" placeholder="<?php echo tt('Password') ?>" value="">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <button type="submit" class="btn btn-primary sign-in-button"><?php echo tt('Sign In') ?></button>
                      <div class="col-lg-offset-2">
                       &nbsp;&nbsp; or <a href="<?php echo $href_account_create ?>"><?php echo tt('Join Us') ?></a>
                      </div>
                    </div>
                  </div>
                </fieldset>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="bs-component latest">
      <?php echo $module_latest; ?>
    </div>
  </div>
</div>
<?php echo $footer; ?>
