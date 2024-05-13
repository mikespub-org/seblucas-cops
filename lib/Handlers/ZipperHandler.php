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
use SebLucas\Cops\Output\Zipper;

/**
 * Download all books for a page, series or author by format (epub, mobi, any, ...)
 * URL format: zipper.php?page={page}&type={type}&id={id}
 */
class ZipperHandler extends BaseHandler
{
    public const HANDLER = "zipper";

    public static function getRoutes()
    {
        // handle endpoint with page param
        return [
            "/zipper/{page}/{type}/{id}" => [static::PARAM => static::HANDLER],
            "/zipper/{page}/{type}" => [static::PARAM => static::HANDLER],
            "/zipper/{page}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        if (empty(Config::get('download_page')) &&
            empty(Config::get('download_series')) &&
            empty(Config::get('download_author'))
        ) {
            echo 'Downloads by page, series or author are disabled in config';
            return;
        }
        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                // this will call exit()
                $request->notFound();
            }
        }

        $zipper = new Zipper($request);

        if ($zipper->isValid()) {
            // disable nginx buffering by default
            header('X-Accel-Buffering: no');
            $zipper->download();
        } else {
            echo "Invalid download: " . $zipper->getMessage();
        }
    }
}
