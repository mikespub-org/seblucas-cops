<?php
/**
 * Epub loader application
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 * @author     mikespub
 */

namespace Marsender\EPubLoader\App;

use Marsender\EPubLoader\ActionHandler;

// Application name
define('DEF_AppName', 'Epub loader');

// Application version
define('DEF_AppVersion', '2.0');

//------------------------------------------------------------------------------
// Include files
//------------------------------------------------------------------------------

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Include config file
$fileName = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
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
    if (ActionHandler::hasAction($action)) {
        $handler = new ActionHandler($dbConfig);
        $result = $handler->handle($action);

    } else {
        $fileName = sprintf('%s%saction_%s.php', __DIR__, DIRECTORY_SEPARATOR, $action);
        if (!file_exists($fileName)) {
            die('Incorrect action file: ' . $fileName);
        }
        $result = require($fileName);
    }
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
            $fileList = ActionHandler::getFiles($dbPath . DIRECTORY_SEPARATOR . $epubPath, '*.epub');
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
