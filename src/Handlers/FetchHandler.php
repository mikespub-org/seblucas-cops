<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Handlers\TestHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Output\Zipper;

/**
 * Fetch book covers or files
 * URL format: index.php/fetch?id={bookId}&type={type}&data={idData}&view={viewOnly}
 *          or index.php/fetch?id={bookId}&thumb={thumb} for book cover thumbnails
 *          or index.php/fetch?id={bookId}&file={file} for extra data file for this book
 */
class FetchHandler extends BaseHandler
{
    public const HANDLER = "fetch";
    public const PREFIX = "";  // we have multiple prefixes here
    public const PARAMLIST = ["db", "id", "file", "thumb", "data", "ignore", "type", "view"];

    public static function getRoutes()
    {
        return [
            // support custom pattern for route placeholders - see nikic/fast-route
            "fetch-file" => ["/files/{db:\d+}/{id:\d+}/{file:.+}"],
            "fetch-thumb" => ["/thumbs/{db:\d+}/{id:\d+}/{thumb}.jpg"],
            "fetch-cover" => ["/covers/{db:\d+}/{id:\d+}.jpg"],
            "fetch-inline" => ["/inline/{db:\d+}/{data:\d+}/{ignore}.{type}", ["view" => 1]],
            "fetch-data" => ["/fetch/{db:\d+}/{data:\d+}/{ignore}.{type}"],
            // @todo handle url rewriting if enabled separately - path parameters are different
            "fetch-view" => ["/view/{data}/{db}/{ignore}.{type}", ["view" => 1]],
            "fetch-download" => ["/download/{data}/{db}/{ignore}.{type}"],
        ];
    }

    /**
     * Summary of handle
     * @param Request $request
     * @return Response
     */
    public function handle($request)
    {
        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                // this will call exit()
                Response::notFound($request);
            }
        }
        // clean output buffers before sending the ebook data do avoid high memory usage on big ebooks (ie. comic books)
        if (ob_get_length() !== false && $request->getHandler() !== TestHandler::class) {
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
            Response::notFound($request);
        }

        if (!empty($file)) {
            return $this->sendExtraFile($request, $book, $file);
        }

        // -DC- Add png type
        if (in_array($type, ['jpg', 'png'])) {
            return $this->sendThumbnail($request, $book, $type);
        }

        if (!$viewOnly && $type == 'epub' && Config::get('update_epub-metadata')) {
            return $this->sendUpdatedEpub($request, $book, $idData);
        }

        $data = $book->getDataById($idData);
        if (!$data) {
            // this will call exit()
            Response::notFound($request);
        }
        // absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
        $file = $book->getFilePath($type, $idData);

        if ($viewOnly) {
            // disposition inline here
            $response = new FileResponse($data->getMimeType(), 0, '');
            return $response->setFile($file);
        }

        if ($type == 'epub' && Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
            return $this->sendConvertedKepub($book, $file, $data);
        }

        $response = new FileResponse($data->getMimeType(), 0, basename($file));
        return $response->setFile($file);
    }

    /**
     * Summary of sendExtraFile
     * @param Request $request
     * @param Book $book
     * @param string $file
     * @return FileResponse|Response
     */
    public function sendExtraFile($request, $book, $file)
    {
        if ($file == 'zipped') {
            // zip all extra files and send back
            return $this->zipExtraFiles($request, $book);
        }
        $extraFiles = $book->getExtraFiles();
        if (!in_array($file, $extraFiles)) {
            // this will call exit()
            Response::notFound($request);
        }
        // send back extra file
        $filepath = $book->path . '/' . Book::DATA_DIR_NAME . '/' . $file;
        if (!file_exists($filepath)) {
            // this will call exit()
            Response::notFound($request);
        }
        $mimetype = Response::getMimeType($filepath);
        $response = new FileResponse($mimetype, 0, basename($filepath));
        return $response->setFile($filepath);
    }

    /**
     * Summary of zipExtraFiles
     * @param Request $request
     * @param Book $book
     * @return Response
     */
    public function zipExtraFiles($request, $book)
    {
        // create empty file response to start with!?
        $response = new FileResponse();
        $zipper = new Zipper($request, $response);

        if ($zipper->isValidForExtraFiles($book)) {
            $sendHeaders = headers_sent() ? false : true;
            // disable nginx buffering by default
            if ($sendHeaders) {
                header('X-Accel-Buffering: no');
            }
            return $zipper->download(null, $sendHeaders);
        } else {
            // this will call exit()
            Response::sendError($request, "Invalid zipped: " . $zipper->getMessage());
        }
    }

    /**
     * Summary of sendThumbnail
     * @param Request $request
     * @param Book $book
     * @param string $type
     * @return FileResponse
     */
    public function sendThumbnail($request, $book, $type)
    {
        $file = $book->getCoverFilePath($type);
        if (empty($file) || !file_exists($file)) {
            // this will call exit()
            Response::notFound($request);
        }
        $cover = new Cover($book);
        // create empty file response to start with!?
        $response = new FileResponse();
        return $cover->sendThumbnail($request, $response);
    }

    /**
     * Summary of sendUpdatedEpub
     * @param Request $request
     * @param Book $book
     * @param mixed $idData
     * @return FileResponse
     */
    public function sendUpdatedEpub($request, $book, $idData)
    {
        // update epub metadata + provide kepub if needed (with update of opf properties for cover-image in EPub)
        if (Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
            $book->updateForKepub = true;
        }
        // create empty response to start with!?
        $response = new FileResponse();
        // this will also use kepubify_path internally if defined
        return $book->sendUpdatedEpub($idData, $response);
    }

    /**
     * Summary of sendConvertedKepub
     * @param Book $book
     * @param string $file
     * @param Data $data
     * @return FileResponse
     */
    public function sendConvertedKepub($book, $file, $data)
    {
        // run kepubify on original Epub file and send converted tmpfile
        if (!empty(Config::get('kepubify_path'))) {
            // @todo no cache control here!?
            $response = new FileResponse($data->getMimeType(), null, basename($data->getUpdatedFilenameKepub()));
            $result = $book->runKepubify($file, $response);
            if (empty($result)) {
                // this will call exit()
                Response::sendError(null, 'Error: failed to convert epub file');
            }
            return $result;
        }
        // provide kepub in name only (without update of opf properties for cover-image in Epub)
        $response = new FileResponse($data->getMimeType(), 0, basename($data->getUpdatedFilenameKepub()));
        return $response->setFile($file);
    }
}
