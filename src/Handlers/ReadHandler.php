<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\Response;
use Exception;

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
        } catch (Exception $e) {
            error_log($e);
            return Response::sendError($request, $e->getMessage());
        }
    }
}
