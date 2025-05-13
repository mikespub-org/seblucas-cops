<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
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
            $session = $this->getContext()->getSession();
            $session->start();
            $connected = $session->get('connected');
            if (!isset($connected)) {
                return Response::notFound($request);
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
            return Response::notFound($request);
        }

        if (!empty($file)) {
            return $this->sendExtraFile($request, $book, $file);
        }

        // -DC- Add png type
        if (in_array($type, ['jpg', 'png'])) {
            return $this->sendThumbnail($request, $book, $type);
        }

        $data = $book->getDataById($idData);
        if (!$data) {
            return Response::notFound($request);
        }

        if (!$viewOnly && $type == 'epub' && Config::get('update_epub-metadata')) {
            return $this->sendUpdatedEpub($request, $book, $data);
        }

        if ($viewOnly) {
            // disposition inline here
            return $data->sendFile(true);
        }

        if ($type == 'epub' && Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
            return $data->sendConvertedKepub();
        }

        return $data->sendFile();
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
            return Response::notFound($request);
        }
        // send back extra file
        $filepath = $book->path . '/' . Book::DATA_DIR_NAME . '/' . $file;
        if (!file_exists($filepath)) {
            return Response::notFound($request);
        }
        $mimetype = Response::getMimeType($filepath);
        $response = new FileResponse($mimetype, 0, basename($filepath));
        $response->setFile($filepath);
        if ($response->isNotModified($request)) {
            return $response->setNotModified();
        }
        return $response;
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
            return Response::sendError($request, "Invalid zipped: " . $zipper->getMessage());
        }
    }

    /**
     * Summary of sendThumbnail
     * @param Request $request
     * @param Book $book
     * @param string $type
     * @return FileResponse|Response
     */
    public function sendThumbnail($request, $book, $type)
    {
        $file = $book->getCoverFilePath($type);
        if (empty($file) || !file_exists($file)) {
            return Response::notFound($request);
        }
        $cover = new Cover($book);
        // create empty file response to start with!?
        $response = new FileResponse();
        $response = $cover->sendThumbnail($request, $response);
        if ($response->isNotModified($request)) {
            return $response->setNotModified();
        }
        return $response;
    }

    /**
     * Summary of sendUpdatedEpub
     * @param Request $request
     * @param Book $book
     * @param Data $data
     * @return FileResponse|Response
     */
    public function sendUpdatedEpub($request, $book, $data)
    {
        // update epub metadata + provide kepub if needed (with update of opf properties for cover-image in EPub)
        if (Config::get('provide_kepub') == '1'  && preg_match('/Kobo/', $request->agent())) {
            $book->updateForKepub = true;
        }
        // set updateForKepub if necessary
        return $data->sendUpdatedEpub($book->updateForKepub);
    }
}
