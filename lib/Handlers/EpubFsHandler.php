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
 * Handle Epub filesystem for monocle epub reader
 * URL format: epubfs.php?data={idData}&comp={component}
 */
class EpubFsHandler extends BaseHandler
{
    public const HANDLER = "epubfs";

    public static function getRoutes()
    {
        // support custom pattern for route placeholders - see nikic/fast-route
        return [
            "/epubfs/{data:\d+}/{comp:.+}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        if (php_sapi_name() === 'cli') {
            return;
        }

        //$database = $request->getId('db');
        $idData = $request->getId('data');
        if (empty($idData)) {
            // this will call exit()
            $request->notFound();
        }
        $component = $request->get('comp', null);
        if (empty($component)) {
            // this will call exit()
            $request->notFound();
        }

        try {
            $data = EPubReader::getContent($idData, $component, $request);

            $expires = 60 * 60 * 24 * 14;
            header('Pragma: public');
            header('Cache-Control: maxage=' . $expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

            echo $data;
        } catch (Exception $e) {
            error_log($e);
            $request->notFound();
        }
    }
}
