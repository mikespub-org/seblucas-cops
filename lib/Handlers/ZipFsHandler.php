<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\Response;
use Exception;

/**
 * Handle Epub filesystem for epubjs-reader
 * URL format: zipfs.php/{db}/{idData}/{component}
 */
class ZipFsHandler extends BaseHandler
{
    public const HANDLER = "zipfs";

    public static function getRoutes()
    {
        // support custom pattern for route placeholders - see nikic/fast-route
        return [
            "/zipfs/{db:\d+}/{idData:\d+}/{component:.+}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        if (php_sapi_name() === 'cli' && $request->getHandler() !== 'phpunit') {
            return;
        }

        //$database = $request->getId('db');
        $idData = $request->getId('idData');
        if (empty($idData)) {
            // this will call exit()
            Response::notFound($request);
        }
        $component = $request->get('component');
        if (empty($component)) {
            // this will call exit()
            Response::notFound($request);
        }

        try {
            EPubReader::sendZipContent($idData, $component, $request);

        } catch (Exception $e) {
            error_log($e);
            Response::sendError($request, $e->getMessage());
        }
    }
}
