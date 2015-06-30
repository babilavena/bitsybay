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

// Configuration
require_once('config.php');

// Startup
require_once(DIR_BASE . 'system' . DIR_SEPARATOR . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Error Handler
mb_internal_encoding('UTF-8');

// PDO
try {
    $db = new PDO('mysql:dbname=' . DB_DATABASE . ';host=' . DB_HOSTNAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {
    trigger_error($e->getMessage());
}

$registry->set('db', $db);

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$response->setCompression(GZIP_COMPRESSION_LEVEL);

$registry->set('response', $response);

// Url
$url = new Url($db, $request, $response, HTTP_SERVER, HTTP_SERVER);
$registry->set('url', $url);

// Session
$session = new Session();
$registry->set('session', $session);

// Language
$language = new Language($registry, DEFAULT_LANGUAGE_ID);
$registry->set('language', $language);

// Currency
$currency = new Currency($registry, DEFAULT_CURRENCY_ID);
$registry->set('currency', $currency);

// Cache
$cache = new Cache($registry);
$registry->set('cache', $cache);

// Storage
$storage = new Storage($registry);
$registry->set('storage', $storage);

// Document
$registry->set('document', new Document());

// Auth
$auth = new Auth($registry);
$registry->set('auth', $auth);

// Security log
$security_log = new Log('security.txt', $auth->getId(), $request->getRemoteAddress());
$registry->set('security_log', $security_log);

// Front Controller
$controller = new Front($registry);

// Router
if (isset($request->get['route'])) {
    $action = new Action($request->get['route']);
} else {
    $action = new Action('common/home');
}

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();
