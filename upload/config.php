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

// COMMON
define('PROJECT_NAME', 'BitsyBay');
define('HOST_COUNTRY', 'Sweden'); // Law

// URL
define('URL_BASE', 'http://localhost/');

define('URL_FACEBOOK', '');
define('URL_TWITTER', '');
define('URL_TUMBLR', '');
define('URL_GITHUB', '');

// DIR
define('DIR_BASE', '/var/www/html/');
define('DIR_STORAGE', '/var/www/html/storage/');
define('DIR_IMAGE', '/var/www/html/public/image/');
define('DIR_SEPARATOR', '/');

// DB
define('DB_HOSTNAME', 'localhost');
define('DB_DATABASE', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

// USERS
define('NEW_USER_STATUS', 1);
define('NEW_USER_VERIFIED', 0);

// COMPRESSION
define('GZIP_COMPRESSION_LEVEL', 0);

// LOCALIZATION
define('DEFAULT_LANGUAGE_ID', 1);
define('DEFAULT_CURRENCY_ID', 1);
define('DATE_FORMAT_DEFAULT', 'j M, Y');

// EXTENSIONS
define('STORAGE_FILE_EXTENSION', 'zip');
define('STORAGE_IMAGE_EXTENSION', 'jpg');

// IMAGES
define('PRODUCT_IMAGE_ORIGINAL_WIDTH', 800);
define('PRODUCT_IMAGE_ORIGINAL_HEIGHT', 800);
define('PRODUCT_IMAGE_ORIGINAL_MIN_WIDTH', 400);
define('PRODUCT_IMAGE_ORIGINAL_MIN_HEIGHT', 400);

define('USER_IMAGE_ORIGINAL_JPEG_COMPRESSION', 100);
define('USER_IMAGE_ORIGINAL_WIDTH', 400);
define('USER_IMAGE_ORIGINAL_HEIGHT', 400);
define('USER_IMAGE_ORIGINAL_MIN_WIDTH', 200);
define('USER_IMAGE_ORIGINAL_MIN_HEIGHT', 200);

// PRICES
define('FEE_PER_ORDER', 11);                  // Percent
define('FEE_USER_VERIFICATION', 0.1);         // BTC
define('ALLOWED_PRODUCT_MIN_PRICE', 0.01);    // BTC
define('ALLOWED_PRODUCT_MAX_PRICE', 1000000); // BTC

// ORDERS
define('ORDER_PENDING_STATUS_ID', 1);   // INT order_status_id in order_status table
define('ORDER_APPROVED_STATUS_ID', 2);  // INT order_status_id in order_status table
define('ORDER_PROCESSED_STATUS_ID', 3); // INT order_status_id in order_status table

// QUOTA
define('QUOTA_FILE_SIZE_BY_DEFAULT', 100); // int Mb
define('QUOTA_BONUS_SIZE_PER_ORDER', 1);   // int Mb
define('QUOTA_IMAGE_MAX_FILE_SIZE', 500);  // int Kb
define('QUOTA_IMAGES_PER_PRODUCT', 6);     // int Qty
define('QUOTA_DEMO_PER_PRODUCT', 5);       // int Qty
define('QUOTA_VIDEO_PER_PRODUCT', 5);      // int Qty
define('QUOTA_SPECIALS_PER_PRODUCT', 5);   // int Qty

// VALIDATORS
define('VALIDATOR_PRODUCT_TITLE_MIN_LENGTH', 2);
define('VALIDATOR_PRODUCT_TITLE_MAX_LENGTH', 60);
define('VALIDATOR_PRODUCT_DESCRIPTION_MIN_LENGTH', 2);
define('VALIDATOR_PRODUCT_DESCRIPTION_MAX_LENGTH', 100000);
define('VALIDATOR_PRODUCT_URL_MIN_LENGTH', 5);
define('VALIDATOR_PRODUCT_URL_MAX_LENGTH', 1000);
define('VALIDATOR_PRODUCT_TAGS_MIN_LENGTH', 2);
define('VALIDATOR_PRODUCT_TAGS_MAX_LENGTH', 100);
define('VALIDATOR_PRODUCT_TAG_MIN_LENGTH', 2);
define('VALIDATOR_PRODUCT_TAG_MAX_LENGTH', 20);

// MAILING AND NOTICES
define('MAIL_EMAIL_BILLING_ADDRESS', '');
define('MAIL_EMAIL_SUPPORT_ADDRESS', '');
define('MAIL_EMAIL_SENDER_ADDRESS', '');
define('MAIL_EMAIL_SENDER_NAME', PROJECT_NAME);

// BITCOIN
define('BITCOIN_RPC_PORT', '8332');
define('BITCOIN_RPC_HOST', 'localhost');
define('BITCOIN_RPC_USERNAME', '');
define('BITCOIN_RPC_PASSWORD', '');
define('BITCOIN_DAEMON_PATH', '/usr/local/bin/bitcoind');

define('BITCOIN_FUND_ADDRESS', '');
define('BITCOIN_ORDER_PREFIX', 'order_');
define('BITCOIN_USER_VERIFICATION_PREFIX', 'user_verification_');
define('BITCOIN_MIN_TRANSACTION_CONFIRMATIONS', 6); // Minimum BTC transaction confirmations requires before the order will be marked as APPROVED
