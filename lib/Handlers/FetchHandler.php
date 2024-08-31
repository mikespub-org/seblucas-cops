<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Calibre\Data;

/**
 * Fetch book covers or files
 * URL format: fetch.php?id={bookId}&type={type}&data={idData}&view={viewOnly}
 *          or fetch.php?id={bookId}&thumb={thumb} for book cover thumbnails
 *          or fetch.php?id={bookId}&file={file} for extra data file for this book
 */
class FetchHandler extends BaseHandler
{
    public const HANDLER = "fetch";

    public static function getRoutes()
    {
        // check if the path starts with the endpoint param or not here
        return [
            // support custom pattern for route placeholders - see nikic/fast-route
            "/files/{db:\d+}/{id:\d+}/{file:.+}" => [static::PARAM => static::HANDLER],
            "/thumbs/{thumb}/{db:\d+}/{id:\d+}.jpg" => [static::PARAM => static::HANDLER],
            "/covers/{db:\d+}/{id:\d+}.jpg" => [static::PARAM => static::HANDLER],
            "/inline/{db:\d+}/{data:\d+}/{ignore}.{type}" => [static::PARAM => static::HANDLER, "view" => 1],
            "/fetch/{db:\d+}/{data:\d+}/{ignore}.{type}" => [static::PARAM => static::HANDLER],
            // @todo handle url rewriting if enabled separately - path parameters are different
            "/view/{data}/{db}/{ignore}.{type}" => [static::PARAM => static::HANDLER, "view" => 1],
            "/view/{data}/{ignore}.{type}" => [static::PARAM => static::HANDLER, "view" => 1],
            "/download/{data}/{db}/{ignore}.{type}" => [static::PARAM => static::HANDLER],
            "/download/{data}/{ignore}.{type}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                // this will call exit()
                $request->notFound();
            }
        }
        // clean output buffers before sending the ebook data do avoid high memory usage on big ebooks (ie. comic books)
        if (ob_get_length() !== false && $request->getHandler() !== 'phpunit') {
            ob_end_clean();
        }

        $bookId   = $request->getId();
        $type     = $request->get('type', 'jpg');
        $idData   = $request->getId('data');
        $viewOnly = $request->get('view', false);
        $database = $request->database();
        $file     = $request->get('file');

        if (is_null($bookId)) {
            $book = Book::getBookByDataId($idData, $database);
        } else {
            $book = Book::getBookById($bookId, $database);
        }

        if (!$book) {
            // this will call exit()
            $request->notFound();
        }

        if (!empty($file)) {
            $extraFiles = $book->getExtraFiles();
            if ($file == 'zipped') {
                // @todo zip all extra files and send back
                echo 'TODO: zip all extra files and send back';
                return;
            }
            if (!in_array($file, $extraFiles)) {
                // this will call exit()
                $request->notFound();
            }
            // send back extra file
            $filepath = $book->path . '/' . Book::DATA_DIR_NAME . '/' . $file;
            if (!file_exists($filepath)) {
                // this will call exit()
                $request->notFound();
            }
            $this->sendFile($filepath);
            return;
        }

        // -DC- Add png type
        if ($type == 'jpg' || $type == 'png' || empty(Config::get('calibre_internal_directory'))) {
            if ($type == 'jpg' || $type == 'png') {
                $file = $book->getCoverFilePath($type);
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
            // update epub metadata + provide kepub if needed (with update of opf properties for cover-image in EPub)
            if (Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
                $book->updateForKepub = true;
            }
            $book->getUpdatedEpub($idData);
            return;
        }
        if ($viewOnly) {
            header('Content-Disposition: inline');
        } elseif (Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
            // provide kepub if needed (without update of opf properties for cover-image in Epub)
            header('Content-Disposition: attachment; filename="' . basename($data->getUpdatedFilenameKepub()) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        }

        // -DC- File is a full path
        //$dir = Config::get('calibre_internal_directory');
        //if (empty(Config::get('calibre_internal_directory'))) {
        //    $dir = Database::getDbDirectory();
        //}
        $dir = '';

        // @todo clean up nginx x_accel_redirect
        if (empty(Config::get('x_accel_redirect'))) {
            $filename = $dir . $file;
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $dir . $file);
        }
    }

    /**
     * Summary of sendFile
     * @param string $filepath
     * @return void
     */
    public function sendFile($filepath)
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        if (array_key_exists($extension, Data::$mimetypes)) {
            $mimetype = Data::$mimetypes[$extension];
        } else {
            $mimetype = mime_content_type($filepath);
            if (!$mimetype) {
                $mimetype = 'application/octet-stream';
            }
        }

        $expires = 60 * 60 * 24 * 14;
        header('Pragma: public');
        header('Cache-Control: max-age=' . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        header('Content-Type: ' . $mimetype);
        header('Content-Disposition: attachment; filepath="' . basename($filepath) . '"');

        // @todo clean up nginx x_accel_redirect
        if (empty(Config::get('x_accel_redirect'))) {
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        } else {
            header(Config::get('x_accel_redirect') . ': ' . $filepath);
        }
    }
}
