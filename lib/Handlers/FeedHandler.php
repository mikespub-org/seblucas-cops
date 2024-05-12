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
use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Pages\PageId;

/**
 * Handle OPDS 1.2 feed
 * URL format: feed.php?page={page}&query={query}&...
 */
class FeedHandler extends BaseHandler
{
    public const HANDLER = "feed";

    public static function getRoutes()
    {
        return [
            "/feed/{page}/{id}" => [static::PARAM => static::HANDLER],
            "/feed/{page}" => [static::PARAM => static::HANDLER],
            "/feed" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $page = $request->get('page', PageId::INDEX);
        $query = $request->get('query');
        if ($query) {
            $page = PageId::OPENSEARCH_QUERY;
        }
        // @todo handle special case of OPDS not expecting filter while HTML does better
        $request->set('filter', null);

        if (Config::get('fetch_protect') == '1') {
            session_start();
            if (!isset($_SESSION['connected'])) {
                $_SESSION['connected'] = 0;
            }
        }

        header('Content-Type:application/xml');

        $OPDSRender = new OpdsRenderer();

        switch ($page) {
            case PageId::OPENSEARCH :
            case PageId::SEARCH :
                echo $OPDSRender->getOpenSearch($request);
                return;
            default:
                $currentPage = PageId::getPage($page, $request);
                $currentPage->InitializeContent();
                echo $OPDSRender->render($currentPage, $request);
                return;
        }
    }
}
