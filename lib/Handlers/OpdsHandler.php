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
//use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Output\KiwilanOPDS as OpdsRenderer;
use SebLucas\Cops\Pages\PageId;

/**
 * Handle OPDS 2.0 feed (dev only)
 * URL format: opds.php{/route}?query={query} etc.
 */
class OpdsHandler extends BaseHandler
{
    public const HANDLER = "opds";

    public static function getRoutes()
    {
        return [
            "/opds/{page}/{id}" => [static::PARAM => static::HANDLER],
            "/opds/{page}" => [static::PARAM => static::HANDLER],
            "/opds" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $page = $request->get('page', PageId::INDEX);
        $query = $request->get('query');  // 'q' by default for php-opds
        if ($query) {
            $page = PageId::OPENSEARCH_QUERY;
        }

        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                $_SESSION['connected'] = 0;
            }
        }

        $OPDSRender = new OpdsRenderer();

        switch ($page) {
            case PageId::OPENSEARCH :
            case PageId::SEARCH :
                $response = $OPDSRender->getOpenSearch($request);
                break;
            default:
                $currentPage = PageId::getPage($page, $request);
                $currentPage->InitializeContent();
                $response = $OPDSRender->render($currentPage, $request);
        }

        foreach ($response->getHeaders() as $type => $value) {
            header($type . ': ' . $value);
        }
        http_response_code($response->getStatus());

        echo $response->getContents();
    }
}
