<?php
/**
 * COPS (Calibre OPDS PHP Server)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;

require_once __DIR__ . '/config.php';

$request = new Request();

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        // this will call exit()
        $request->notFound();
    }
}
// clean output buffers before sending the ebook data do avoid high memory usage on big ebooks (ie. comic books)
if (ob_get_length() !== false) {
    ob_end_clean();
}

$bookId   = $request->getId();
$type     = $request->get('type', 'jpg');
$idData   = $request->getId('data');
$viewOnly = $request->get('view', false);
$database = $request->database();

if (is_null($bookId)) {
    $book = Book::getBookByDataId($idData, $database);
} else {
    $book = Book::getBookById($bookId, $database);
}

if (!$book) {
    // this will call exit()
    $request->notFound();
}

// -DC- Add png type
if ($type == 'jpg' || $type == 'png' || empty(Config::get('calibre_internal_directory'))) {
    if ($type == 'jpg' || $type == 'png') {
        $file = $book->getFilePath($type);
    } else {
        $file = $book->getFilePath($type, $idData);
    }
    if (is_null($file) || !file_exists($file)) {
        // this will call exit()
        $request->notFound();
    }
}

switch ($type) {
    // -DC- Add png type
    case 'jpg':
    case 'png':
        $cover = new Cover($book);
        $cover->sendThumbnail($request);
        return;
    default:
        break;
}

$expires = 60 * 60 * 24 * 14;
header('Pragma: public');
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

$data = $book->getDataById($idData);
header('Content-Type: ' . $data->getMimeType());

// absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
$file = $book->getFilePath($type, $idData);
if (!$viewOnly && $type == 'epub' && Config::get('update_epub-metadata')) {
    if (Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
        $book->updateForKepub = true;
    }
    $book->getUpdatedEpub($idData);
    return;
}
if ($viewOnly) {
    header('Content-Disposition: inline');
} else {
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
}

// -DC- File is a full path
//$dir = Config::get('calibre_internal_directory');
//if (empty(Config::get('calibre_internal_directory'))) {
//    $dir = Database::getDbDirectory();
//}
$dir = '';

if (empty(Config::get('x_accel_redirect'))) {
    $filename = $dir . $file;
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
} else {
    header(Config::get('x_accel_redirect') . ': ' . $dir . $file);
}
exit();
