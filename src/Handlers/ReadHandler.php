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
use SebLucas\Cops\Output\ComicReader;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;
use InvalidArgumentException;
use Throwable;

/**
 * Handle epub reader with monocle or epubjs + link to comic reader or pdf viewer
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
            "read-format" => ["/read/{path:.+}"],
        ];
    }

    /**
     * Summary of getReaderUrl
     * @param Data $data
     * @param array<string, string> $readers
     * @return string
     */
    public static function getReaderUrl(Data $data, array $readers)
    {
        if ($data->format == "EPUB" && !empty($readers['epub'])) {
            $reader = $readers['epub'];
            if (in_array($reader, ['monocle', 'epubjs'])) {
                // support reader for epub books in folders (epubjs only)
                if (empty($data->id) && isset($data->book->folderId)) {
                    if ($reader == 'epubjs') {
                        // URL format: index.php/read/{path} - let reader handle parsing etc. in browser
                        $params = [];
                        $params['path'] = $data->getFolderPath();
                        return self::route('read-format', $params) ?? '';
                    }
                    // use templates/custom-reader.html?url=... format here for now
                    return UriGenerator::path('templates/custom-reader.html?url=') . $data->getHtmlLink();
                }
                // use standard epub reader here
                $params = [];
                $params['data'] = $data->id;
                $params['db'] = $data->book->getDatabaseId() ?? 0;
                $params['title'] = $data->book->getTitle();
                return self::route('read-title', $params) ?? '';
            }
            // use templates/custom-reader.html?url=... format here for now
            return UriGenerator::path('templates/' . $reader) . $data->getHtmlLink();
        }
        // use templates/comic-reader?url=... format here for now
        if (in_array($data->format, ["CBZ", "CBR", "CBT"]) && !empty($readers['comic'])) {
            return UriGenerator::path('templates/' . $readers['comic']) . $data->getHtmlLink();
        }
        // use templates/pdfjs-viewer?file=... format here for now
        if ($data->format == "PDF" && !empty($readers['pdf'])) {
            return UriGenerator::path('templates/' . $readers['pdf']) . $data->getHtmlLink();
        }
        return '';
    }

    public function handle($request)
    {
        $idData = $request->getId('data');
        // check if we have a folder file path
        $path = null;
        if ($this->config('browse_books_directory')) {
            $path = $request->get('path');
        }
        if (empty($idData) && empty($path)) {
            return Response::notFound($request);
        }
        $version = $request->get('version', $this->config('epub_reader', 'monocle'));

        $response = new Response(Response::MIME_TYPE_HTML);

        if (!empty($path) && ComicReader::isValidFile($path)) {
            $reader = new ComicReader($request, $response);
        } else {
            $reader = new EPubReader($request, $response);
        }

        try {
            return $response->setContent($reader->getReader($idData, $version, $request));
        } catch (InvalidArgumentException $e) {
            return Response::notFound($request, $e->getMessage());
        } catch (Throwable $e) {
            error_log($e);
            return Response::sendError($request, $e->getMessage());
        }
    }
}
