<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;
use InvalidArgumentException;
use Throwable;

/**
 * Handle epub reader with monocle
 * URL format: index.php/read?data={idData}&version={version}
 */
class ReadHandler extends BaseHandler
{
    public const HANDLER = "read";
    public const PREFIX = "/read";
    public const PARAMLIST = ["db", "data", "title"];

    public static function getRoutes()
    {
        return [
            "read-title" => ["/read/{db:\d+}/{data:\d+}/{title}"],
            "read" => ["/read/{db:\d+}/{data:\d+}"],
        ];
    }

    /**
     * Summary of getReaderUrl
     * @param Data $data
     * @return string
     */
    public static function getReaderUrl(Data $data)
    {
        if ($data->format == "EPUB" && Config::get('epub_reader')) {
            if (in_array(Config::get('epub_reader'), ['monocle', 'epubjs'])) {
                // use standard epub reader here
                $params = [];
                $params['data'] = $data->id;
                $params['db'] = $data->book->getDatabaseId() ?? 0;
                $params['title'] = $data->book->getTitle();
                return self::route('read-title', $params) ?? '';
            }
            // use templates/custom-reader?url=... format here for now
            return UriGenerator::path('templates/' . Config::get('epub_reader')) . $data->getHtmlLink();
        }
        // use templates/comic-reader?url=... format here for now
        if (in_array($data->format, ["CBZ", "CBR", "CBT"]) && Config::get('comic_reader')) {
            return UriGenerator::path('templates/' . Config::get('comic_reader')) . $data->getHtmlLink();
        }
        // use templates/pdfjs-viewer?file=... format here for now
        if ($data->format == "PDF" && Config::get('pdfjs_viewer')) {
            return UriGenerator::path('templates/' . Config::get('pdfjs_viewer')) . $data->getHtmlLink();
        }
        return '';
    }

    public function handle($request)
    {
        $idData = $request->getId('data');
        if (empty($idData)) {
            return Response::notFound($request);
        }
        $version = $request->get('version', Config::get('epub_reader', 'monocle'));
        $database = $request->database();

        $response = new Response(Response::MIME_TYPE_HTML);

        $reader = new EPubReader($request, $response);

        try {
            return $response->setContent($reader->getReader($idData, $version, $database));
        } catch (InvalidArgumentException $e) {
            return Response::notFound($request, $e->getMessage());
        } catch (Throwable $e) {
            error_log($e);
            return Response::sendError($request, $e->getMessage());
        }
    }
}
