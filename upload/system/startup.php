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

// Error Reporting
error_reporting(E_ALL);

// Check Version
if (version_compare(phpversion(), '5.3.0', '<') == true) {
    exit('PHP5.3+ Required');
}

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Windows IIS Compatibility
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
    }
}

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    if (isset($_SERVER['PATH_TRANSLATED'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
    }
}

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

    if (isset($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
}

if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
}

// Check if SSL
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
    $_SERVER['HTTPS'] = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $_SERVER['HTTPS'] = true;
} else {
    $_SERVER['HTTPS'] = false;
}

// Engine
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/action.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/controller.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/front.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/loader.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/model.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'engine/registry.php');

// Helper
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'helper/tt.php');

// Library
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/url.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/request.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/response.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/session.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/language.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/currency.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/cache.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/document.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/auth.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/log.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/image.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/storage.php');
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'library/mail.php');
