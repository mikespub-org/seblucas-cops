<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for epubjs-reader
 * URL format: zipfs.php/{db}/{idData}/{component}
 *
 * @author mikespub
 */

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Calibre\Book;

require_once __DIR__ . '/config.php';

if (php_sapi_name() === 'cli') {
    return;
}

// don't try to match path params here
$request = new Request(false);
$path = $request->path();
if (empty($path) || $path == '/') {
    $request->notFound();
}
$path = substr($path, 1);
$matches = [];
if (!preg_match('/^(\d+)\/(\d+)\/(.+)$/', $path, $matches)) {
    $request->notFound();
}
$database = $matches[1];
$idData = intval($matches[2]);
$component = $matches[3];

try {
    $book = Book::getBookByDataId($idData, intval($database));
    if (!$book) {
        throw new Exception('Unknown data ' . $idData);
    }
    $epub = $book->getFilePath('EPUB', $idData);
    if (!$epub || !file_exists($epub)) {
        throw new Exception('Unknown file ' . $epub);
    }
    $zip = new ZipArchive();
    $res = $zip->open($epub, ZipArchive::RDONLY);
    if ($res !== true) {
        throw new Exception('Invalid file ' . $epub);
    }
    $res = $zip->locateName($component);
    if ($res === false) {
        throw new Exception('Unknown component ' . $component);
    }
    $expires = 60 * 60 * 24 * 14;
    header('Pragma: public');
    header('Cache-Control: maxage=' . $expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

    echo $zip->getFromName($component);
    $zip->close();
} catch (Exception $e) {
    error_log($e);
    $request->notFound();
}
