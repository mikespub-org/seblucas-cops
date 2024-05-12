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
use Exception;

/**
 * Handle epub reader with monocle
 * URL format: epubreader.php?data={idData}&version={version}
 */
class ReadHandler extends BaseHandler
{
    public const HANDLER = "read";

    public static function getRoutes()
    {
        return [
            "/read/{db:\d+}/{data:\d+}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $idData = $request->getId('data');
        if (empty($idData)) {
            // this will call exit()
            $request->notFound();
        }

        try {
            header('Content-Type: text/html;charset=utf-8');
            echo EPubReader::getReader($idData, $request);
        } catch (Exception $e) {
            error_log($e);
            $request->notFound();
        }
    }
}
