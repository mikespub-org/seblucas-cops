<?php
/**
 * COPS (Calibre OPDS PHP Server) Epub Loader (example)
 * URL format: loader.php/{action}/{dbNum}/{authorId}?...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use Marsender\EPubLoader\RequestHandler;
use Marsender\EPubLoader\App\ExtraActions;

require_once __DIR__ . '/config/config.php';

if (!class_exists('\Marsender\EPubLoader\RequestHandler')) {
    echo 'This endpoint is an example for development only';
    return;
}

// specify a cache directory for any Google or Wikidata lookup
$cacheDir = 'tests/cache';
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0o777, true)) {
    echo 'Please make sure the cache directory can be created';
    return;
}
if (!is_writable($cacheDir)) {
    echo 'Please make sure the cache directory is writeable';
    return;
}

// get the global config for epub-loader from config/loader.php
$gConfig = require __DIR__ . '/config/loader.php';
// adapt for use with COPS
$gConfig['endpoint'] = Route::link('loader');
$gConfig['app_name'] = 'COPS Loader';
$gConfig['version'] = Config::VERSION;
$gConfig['admin_email'] = '';
$gConfig['create_db'] = false;
$gConfig['databases'] = [];

// get the current COPS calibre directories
$calibreDir = Config::get('calibre_directory');
if (!is_array($calibreDir)) {
    $calibreDir = ['COPS Database' => $calibreDir];
}
foreach ($calibreDir as $name => $path) {
    $gConfig['databases'][] = ['name' => $name, 'db_path' => rtrim((string) $path, '/'), 'epub_path' => '.'];
}

$request = Framework::getRequest('loader');
$action = $request->get('action');
$dbNum = $request->getId('dbNum');
$itemId = $request->getId('authorId');

$urlParams = $request->urlParams;

// you can define extra actions for your app - see example.php
$handler = new RequestHandler($gConfig, ExtraActions::class, $cacheDir);
$result = $handler->request($action, $dbNum, $urlParams);

if (method_exists($handler, 'isDone')) {
    if ($handler->isDone()) {
        return;
    }
}

// handle the result yourself or let epub-loader generate the output
$result = array_merge($gConfig, $result);
//$templateDir = 'templates/twigged/loader';  // if you want to use custom templates
$templateDir = null;
$template = null;

$response = new Response('text/html;charset=utf-8');
$response->sendData($handler->output($result, $templateDir, $template));
