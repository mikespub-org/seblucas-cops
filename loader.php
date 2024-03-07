<?php
/**
 * COPS (Calibre OPDS PHP Server) Epub Loader (example)
 * URL format: loader.php/{action}/{dbNum}/{authorId}?...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use Marsender\EPubLoader\RequestHandler;
use Marsender\EPubLoader\App\ExtraActions;

require_once __DIR__ . '/config.php';

if (!class_exists('Marsender\EPubLoader\RequestHandler')) {
    echo 'This endpoint is an example for development only';
    return;
}

// specify a cache directory for any Google or Wikidata lookup
$cacheDir = 'test/cache';
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0o777, true)) {
    echo 'Please make sure the cache directory can be created';
    return;
}
if (!is_writable($cacheDir)) {
    echo 'Please make sure the cache directory is writeable';
    return;
}

// get the global config for epub-loader from somewhere
$gConfig = [];
$gConfig['endpoint'] = Route::url(Config::ENDPOINT['loader']);
$gConfig['app_name'] = 'COPS Loader';
$gConfig['version'] = Config::VERSION;
$gConfig['admin_email'] = '';
$gConfig['create_db'] = false;
$gConfig['databases'] = [];
$gConfig['actions'] = [];

// specify the actions you want to support here
$gConfig['actions']['csv_export'] = 'Csv export';
// only if you start without an existing calibre database
//$gConfig['actions']['db_load'] = 'Create database';
$gConfig['actions']['authors'] = 'List authors in database';
$gConfig['actions']['wd_author'] = 'Check authors in database';
$gConfig['actions']['wd_books'] = 'Check books for author';
$gConfig['actions']['wd_series'] = 'Check series for author';
$gConfig['actions']['wd_entity'] = 'Check Wikidata entity';
$gConfig['actions']['gb_books'] = 'Search Google Books';
$gConfig['actions']['gb_volume'] = 'Search Google Books Volume';
$gConfig['actions']['ol_author'] = 'Find OpenLibrary author';
$gConfig['actions']['ol_books'] = 'Find OpenLibrary books';
$gConfig['actions']['ol_work'] = 'Find OpenLibrary work';
$gConfig['actions']['notes'] = 'Get Calibre Notes';
$gConfig['actions']['resource'] = 'Get Calibre Resource';
$gConfig['actions']['hello_world'] = 'Example: Hello, World - see app/example.php';
//$gConfig['actions']['goodbye'] = 'Example: Goodbye - see app/example.php';

// get the current COPS calibre directories
$calibreDir = Config::get('calibre_directory');
if (!is_array($calibreDir)) {
    $calibreDir = ['COPS Database' => $calibreDir];
}
foreach ($calibreDir as $name => $path) {
    $gConfig['databases'][] = ['name' => $name, 'db_path' => rtrim($path, '/'), 'epub_path' => '.'];
}

// don't try to match path params here
$request = new Request();
$path = $request->path('/');
$path = substr($path, 1);
[$action, $dbNum, $itemId, $other] = explode('/', $path . '///', 4);
$action = $action ?: null;
$dbNum = ($dbNum !== '') ? (int) $dbNum : null;
if (!empty($itemId)) {
    $request->set('authorId', $itemId);
}
$urlParams = $request->urlParams;

// you can define extra actions for your app - see example.php
$handler = new RequestHandler($gConfig, ExtraActions::class, $cacheDir);
$result = $handler->request($action, $dbNum, $urlParams);

header('Content-Type:text/html;charset=utf-8');

// handle the result yourself or let epub-loader generate the output
$result = array_merge($gConfig, $result);
//$templateDir = 'templates/twigged/loader';  // if you want to use custom templates
$templateDir = null;
$template = null;
echo $handler->output($result, $templateDir, $template);
