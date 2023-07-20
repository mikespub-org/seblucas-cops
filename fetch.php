<?php
/**
 * COPS (Calibre OPDS PHP Server)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Calibre\Book;

require_once dirname(__FILE__) . '/config.php';
/** @var array $config */

global $config;

$request = new Request();

if ($config['cops_fetch_protect'] == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $request->notFound();
        return;
    }
}
// clean output buffers before sending the ebook data do avoid high memory usage on big ebooks (ie. comic books)
if (ob_get_length() !== false) {
    ob_end_clean();
}

$expires = 60*60*24*14;
header('Pragma: public');
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
$bookId   = $request->get('id', null);
$type     = $request->get('type', 'jpg');
$idData   = $request->get('data', null);
$viewOnly = $request->get('view', false);
$database = $request->get('db');

if (is_null($bookId)) {
    $book = Book::getBookByDataId($idData, $database);
} else {
    $book = Book::getBookById($bookId, $database);
}

if (!$book) {
    $request->notFound();
    return;
}

// -DC- Add png type
if ($book && ($type == 'jpg' || $type == 'png' || empty($config['calibre_internal_directory']))) {
    if ($type == 'jpg' || $type == 'png') {
        $file = $book->getFilePath($type);
    } else {
        $file = $book->getFilePath($type, $idData);
    }
    if (is_null($file) || !file_exists($file)) {
        $request->notFound();
        return;
    }
}

switch ($type) {
    // -DC- Add png type
    case 'jpg':
    case 'png':
        if ($type == 'jpg') {
            header('Content-Type: image/jpeg');
        } else {
            header('Content-Type: image/png');
        }
        //by default, we don't cache
        $thumbnailCacheFullpath = null;
        if (isset($config['cops_thumbnail_cache_directory']) && $config['cops_thumbnail_cache_directory'] !== '') {
            $thumbnailCacheFullpath = $config['cops_thumbnail_cache_directory'];
            //if multiple databases, add a subfolder with the database ID
            $thumbnailCacheFullpath .= !is_null($database) ? 'db-' . $database . DIRECTORY_SEPARATOR : '';
            //when there are lots of thumbnails, it's better to save files in subfolders, so if the book's uuid is
            //"01234567-89ab-cdef-0123-456789abcdef", we will save the thumbnail in .../0/12/34567-89ab-cdef-0123-456789abcdef-...
            $thumbnailCacheFullpath .= substr($book->uuid, 0, 1) . DIRECTORY_SEPARATOR . substr($book->uuid, 1, 2) . DIRECTORY_SEPARATOR;
            //check if cache folder exists or create it
            if (file_exists($thumbnailCacheFullpath) || mkdir($thumbnailCacheFullpath, 0700, true)) {
                //we name the thumbnail from the book's uuid and it's dimensions (width and/or height)
                $thumbnailCacheName = substr($book->uuid, 3) . '-' . $request->get('width') . 'x' . $request->get('height') . '.' . $type;
                $thumbnailCacheFullpath = $thumbnailCacheFullpath . $thumbnailCacheName;
            } else {
                //error creating the folder, so we don't cache
                $thumbnailCacheFullpath = null;
            }
        }

        if ($thumbnailCacheFullpath !== null && file_exists($thumbnailCacheFullpath)) {
            //return the already cached thumbnail
            readfile($thumbnailCacheFullpath);
            return;
        }

        $width = $request->get('width');
        $height = $request->get('height');
        if ($book->getThumbnail($width, $height, $thumbnailCacheFullpath, $type)) {
            //if we don't cache the thumbnail, imagejpeg() in $book->getThumbnail() already return the image data
            if ($thumbnailCacheFullpath === null) {
                // The cover had to be resized
                return;
            } else {
                //return the just cached thumbnail
                readfile($thumbnailCacheFullpath);
                return;
            }
        }
        break;
    default:
        $data = $book->getDataById($idData);
        header('Content-Type: ' . $data->getMimeType());
        break;
}

// absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
$file = $book->getFilePath($type, $idData, false);
if (!$viewOnly && $type == 'epub' && $config['cops_update_epub-metadata']) {
    if ($config['cops_provide_kepub'] == '1'  && preg_match('/Kobo/', $request->agent())) {
        $book->updateForKepub = true;
    }
    $book->getUpdatedEpub($idData);
    return;
}
// -DC- Add png type
if ($type == 'jpg' || $type == 'png') {
    header('Content-Disposition: filename="' . basename($file) . '"');
} elseif ($viewOnly) {
    header('Content-Disposition: inline');
} else {
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
}

// -DC- File is a full path
//$dir = $config['calibre_internal_directory'];
//if (empty($config['calibre_internal_directory'])) {
//    $dir = Base::getDbDirectory();
//}
$dir = '';

if (empty($config['cops_x_accel_redirect'])) {
    $filename = $dir . $file;
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
} else {
    header($config['cops_x_accel_redirect'] . ': ' . $dir . $file);
}
exit();
