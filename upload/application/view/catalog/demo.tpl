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
    <?php echo $meta_title ?>
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
      <div class="navbar navbar-default navbar-fixed-top demo-toolbar">
        <div class="container">
          <div class="row">
            <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
              <a href="/" class="brand-logo">
                <span>B</span><span>i</span><span>t</span><span>s</span><span>y</span>Bay
              </a>
            </div>
            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 text-muted small">
              <h1><i class="glyphicon glyphicon-eye-open"></i> <?php echo $title ?></h1>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
              <div class="demo-action">
                <?php if ($download) { ?>
                  <a class="btn btn btn-success" href="<?php echo $href_download ?>"><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo tt('Get') ?></a>
                <?php } else { ?>
                  <a class="btn btn-primary" href="<?php echo $href_view ?>"><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo tt('Buy') ?></a>
                <?php } ?>
                <a class="btn btn-default" href="<?php echo $href_original ?>" rel="nofollow"><i class="glyphicon glyphicon-remove-circle"></i> <?php echo tt('Remove Frame') ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
  <iframe class="demo fs" frameborder="0" width="100%" src="<?php echo $href_original ?>"><?php echo tt('The external iframe view is not allowed from your browser') ?></iframe>
</body>
</html>
