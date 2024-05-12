<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Book;
use ZipArchive;
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
        if (php_sapi_name() === 'cli') {
            return;
        }

        $database = $request->getId('db');
        $idData = $request->getId('idData');
        $component = $request->get('component');

        try {
            $book = Book::getBookByDataId($idData, intval($database));
            if (!$book) {
                throw new Exception('Unknown data ' . $idData);
            }
            $epub = $book->getFilePath('EPUB', $idData);
            if (!$epub || !file_exists($epub)) {
                throw new Exception('Unknown file ' . $epub);
            }
            $zip = new ZipArchive();
            $res = $zip->open($epub, ZipArchive::RDONLY);
            if ($res !== true) {
                throw new Exception('Invalid file ' . $epub);
            }
            $res = $zip->locateName($component);
            if ($res === false) {
                throw new Exception('Unknown component ' . $component);
            }
            $expires = 60 * 60 * 24 * 14;
            header('Pragma: public');
            header('Cache-Control: maxage=' . $expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

            echo $zip->getFromName($component);
            $zip->close();
        } catch (Exception $e) {
            error_log($e);
            $request->notFound();
        }
    }
}
