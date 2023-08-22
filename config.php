<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config_default.php';
if (file_exists(__DIR__ . '/config_local.php') && (php_sapi_name() !== 'cli')) {
    require __DIR__ . '/config_local.php';
}
/** @var array<mixed> $config */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

$remote_user = array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
// Clean username, only allow a-z, A-Z, 0-9, -_ chars
$remote_user = preg_replace('/[^a-zA-Z0-9_-]/', '', $remote_user);
$user_config_file = 'config_local.' . $remote_user . '.php';
if (file_exists(__DIR__ . '/' . $user_config_file) && (php_sapi_name() !== 'cli')) {
    require __DIR__ . '/' . $user_config_file;
}

// from here on, we assume that all global $config variables have been loaded
Config::load($config);
date_default_timezone_set(Config::get('default_timezone'));

if (!Request::verifyLogin($_SERVER)) {
    header('WWW-Authenticate: Basic realm="COPS Authentication"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'This site is password protected';
    exit;
}

// load global functions if necessary
require_once __DIR__ . '/lib/functions.php';
