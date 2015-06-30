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

<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>
    <?php echo $title ?>
  </title>
  <base href="<?php echo $base ?>" />
  <?php if ($description) { ?>
    <meta name="description" content="<?php echo $description ?>" />
  <?php } ?>
  <?php if ($keywords) { ?>
    <meta name="keywords" content= "<?php echo $keywords ?>" />
  <?php } ?>
  <?php if ($icon) { ?>
    <link href="<?php echo $icon ?>" rel="icon" />
  <?php } ?>
  <?php foreach ($links as $link) { ?>
    <link href="<?php echo $link['href'] ?>" rel="<?php echo $link['rel'] ?>" />
  <?php } ?>
  <?php foreach ($styles as $style) { ?>
    <link href="<?php echo $style['href'] ?>" type="text/css" rel="<?php echo $style['rel'] ?>" media="<?php echo $style['media'] ?>" />
  <?php } ?>
  <?php foreach ($scripts as $script) { ?>
    <script src="<?php echo $script ?>" type="text/javascript"></script>
  <?php } ?>
</head>
<body>
<header>
  <nav>
    <div class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a href="/" class="navbar-brand brand-logo"><span>B</span><span>i</span><span>t</span><span>s</span><span>y</span>Bay</a>
          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
          <ul class="nav navbar-nav">
            <li class="make-money"><a href="<?php echo $href_account_product_list ?>"><?php echo tt('Make coins +') ?></a></li>
            <?php if ($bool_is_logged) { ?>
              <li class="dropdown collection">
                <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $href_account_account ?>" id="m-nav-account-account"> <?php echo tt('Collection') ?><span class="caret"></span></a>
                <ul class="dropdown-menu" aria-labelledby="m-nav-account-account">
                  <li><a href="<?php echo $href_product_purchased ?>"><?php echo tt('Purchased') ?></a></li>
                  <li><a href="<?php echo $href_product_favorites ?>"><?php echo tt('Favorites') ?></a></li>
                </ul>
              </li>
            <?php } ?>
            <?php foreach ($categories as $category) { ?>
              <?php if ($category['child']) { ?>
                <li class="dropdown">
                  <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $category['href'] ?>" id="m-cat-<?php echo $category['category_id'] ?>"><?php echo $category['title'] ?> <span class="caret"></span></a>
                  <ul class="dropdown-menu" aria-labelledby="m-cat-<?php echo $category['category_id'] ?>">
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
          <ul class="nav navbar-nav navbar-right">
            <?php if ($bool_is_logged) { ?>
            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $href_account_account ?>" id="m-nav-account-account">
                <i class="glyphicon glyphicon-user"></i>
                <?php echo tt('Account') ?>
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" aria-labelledby="m-nav-account-account">
                <li><a href="<?php echo $href_account_account ?>"><?php echo tt('Profile') ?></a></li>
                <li><a href="<?php echo $href_account_account_update ?>"><?php echo tt('Account settings') ?></a></li>
                <li class="divider"></li>
                <li><a href="<?php echo $href_account_logout ?>"><?php echo tt('Logout') ?></a></li>
              </ul>
            </li>
            <?php } else { ?>
              <li><a href="<?php echo $href_account_create ?>"><?php echo tt('Join') ?></a></li>
              <li><a href="<?php echo $href_account_login ?>" onclick="$('#loginForm').modal('toggle'); return false;"><?php echo tt('Sign In') ?></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</header>
<div class="container">
