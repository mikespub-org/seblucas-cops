<?php
/**
 * COPS (Calibre OPDS PHP Server) Epub Loader (example)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 *
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use Marsender\EPubLoader\RequestHandler;
use Marsender\EPubLoader\App\ExtraActions;

require_once __DIR__ . '/config.php';

if (!class_exists('Marsender\EPubLoader\RequestHandler')) {
    echo 'This endpoint is an example for development only';
    return;
}

// specify a cache directory for any Google or Wikidata lookup
$cacheDir = 'test/cache';
if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
    echo 'Please make sure the cache directory can be created';
    return;
}
if (!is_writable($cacheDir)) {
    echo 'Please make sure the cache directory is writeable';
    return;
}

// get the global config for epub-loader from somewhere
$gConfig = [];
$gConfig['endpoint'] = 'loader.php';
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
$gConfig['actions']['authors'] = 'Check authors in database';
$gConfig['actions']['books'] = 'Check books for author';
$gConfig['actions']['series'] = 'Check series for author';
$gConfig['actions']['wikidata'] = 'Find Wikidata entity';
$gConfig['actions']['google'] = 'Search Google Books';
$gConfig['actions']['volume'] = 'Get Google Books Volume';
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

// get the current action and dbNum if any
$request = new Request();
$action = $request->get('action');
$dbNum = $request->get('dbnum');

// you can define extra actions for your app - see example.php
$handler = new RequestHandler($gConfig, ExtraActions::class, $cacheDir);
$result = $handler->request($action, $dbNum);

header('Content-Type:text/html;charset=utf-8');

// handle the result yourself or let epub-loader generate the output
$result = array_merge($gConfig, $result);
//$templateDir = 'templates/twigged/loader';  // if you want to use custom templates
$templateDir = null;
$template = null;
echo $handler->output($result, $templateDir, $template);
