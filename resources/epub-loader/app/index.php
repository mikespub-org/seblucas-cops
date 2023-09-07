<?php
/**
 * Epub loader application
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

// Application name
define('DEF_AppName', 'Epub loader');

// Application version
define('DEF_AppVersion', '1.1');

//------------------------------------------------------------------------------
// Include files
//------------------------------------------------------------------------------

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Include config file
$fileName = __DIR__ . DIRECTORY_SEPARATOR . 'epub-loader-config.php';
if (!file_exists($fileName)) {
    die('Missing configuration file: ' . $fileName);
}
// Global vars
$gConfig = [];
require_once($fileName);
/** @var array<mixed> $gConfig */

//------------------------------------------------------------------------------
// Start application
//------------------------------------------------------------------------------

// Global vars
$gErrorArray = [];

// Get the url parameters
$action = $_GET['action'] ?? null;
$dbNum = isset($_GET['dbnum']) ? (int)$_GET['dbnum'] : null;

$data = [
    'app_name' => empty($gConfig['app_name']) ? DEF_AppName : $gConfig['app_name'],
    'version' => DEF_AppVersion,
    'admin_email' => empty($gConfig['admin_email']) ? '' : str_rot13($gConfig['admin_email']),
];
$template = 'index.html';

/**
 * Recursive get files
 *
 * @param string $inPath Base directory to search in
 * @param string $inPattern Search pattern
 * @return array<string>
 */
function RecursiveGlob($inPath = '', $inPattern = '*')
{
    $res = [];

    // Check path
    if (!is_dir($inPath)) {
        return $res;
    }

    // Get the list of directories
    if (substr($inPath, -1) != DIRECTORY_SEPARATOR) {
        $inPath .= DIRECTORY_SEPARATOR;
    }

    // Add files from the current directory
    $files = glob($inPath . $inPattern, GLOB_MARK | GLOB_NOSORT);
    foreach ($files as $item) {
        if (substr($item, -1) == DIRECTORY_SEPARATOR) {
            continue;
        }
        $res[] = $item;
    }

    // Scan sub directories
    $paths = glob($inPath . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
    foreach ($paths as $path) {
        $res = array_merge($res, RecursiveGlob($path, $inPattern));
    }

    sort($res);

    return $res;
}

$result = null;
// Html content
if (isset($action) && isset($dbNum)) {
    if (!array_key_exists($action, $gConfig['actions'])) {
        die('Invalid action');
    }
    if (!isset($gConfig['databases'][$dbNum])) {
        die('Incorrect database num: ' . $dbNum);
    }
    $dbConfig = $gConfig['databases'][$dbNum];
    $dbPath = $dbConfig['db_path'];
    if (!is_dir($dbPath)) {
        if (!mkdir($dbPath, 0755, true)) {
            die('Cannot create directory: ' . $dbPath);
        }
    }
    $fileName = sprintf('%s%saction_%s.php', __DIR__, DIRECTORY_SEPARATOR, $action);
    if (!file_exists($fileName)) {
        die('Incorrect action file: ' . $fileName);
    }
    $result = require($fileName);
    $data['action'] = $action;
    $data['actionTitle'] = $gConfig['actions'][$action];
    $data['dbNum'] = $dbNum;
    $data['dbConfig'] = $gConfig['databases'][$dbNum];
    if (is_file(dirname(__DIR__) . '/templates/' . $action . '.html')) {
        $template = $action . '.html';
    }
} else {
    if (!isset($action)) {
        // Display the available actions
        $data['actions'] = $gConfig['actions'];
        $template = 'actions.html';
    } else {
        // Display databases
        $data['action'] = $action;
        $data['actionTitle'] = $gConfig['actions'][$action];
        $data['databases'] = $gConfig['databases'];
        foreach ($gConfig['databases'] as $dbNum => $dbConfig) {
            $dbPath = $dbConfig['db_path'];
            $epubPath = $dbConfig['epub_path'];
            $fileList = RecursiveGlob($dbPath . DIRECTORY_SEPARATOR . $epubPath, '*.epub');
            $data['databases'][$dbNum]['count'] = count($fileList);
        }
        $template = 'databases.html';
    }
}

if (is_array($result)) {
    $data = array_merge($data, $result);
} else {
    $data['result'] = $result;
}
$data['errors'] = $gErrorArray;

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader);

header('Content-type: text/html; charset=utf-8');

echo $twig->render($template, $data);
