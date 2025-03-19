<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
//use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Output\KiwilanOPDS as OpdsRenderer;
use SebLucas\Cops\Output\Response as CopsResponse;
use SebLucas\Cops\Pages\PageId;

/**
 * Handle OPDS 2.0 feed (dev only)
 * URL format: index.php/opds{/route}?query={query} etc.
 */
class OpdsHandler extends BaseHandler
{
    public const HANDLER = "opds";
    public const PREFIX = "/opds";
    public const PARAMLIST = ["page", "id", "path"];
    public const SEARCH = "opds-search";

    public static function getRoutes()
    {
        return [
            //"opds-page-id" => ["/opds/{page:\w+}/{id}"],
            "opds-search" => ["/opds/search", ["page" => "search"]],
            "opds-page" => ["/opds/{page:\w+}"],
            "opds-path" => ["/opds/{path:.*}"],
            "opds" => ["/opds"],
        ];
    }

    public function handle($request)
    {
        if (!class_exists('\Kiwilan\Opds\OpdsResponse')) {
            echo 'This handler is available in developer mode only (without --no-dev option):' . "<br/>\n";
            echo '$ composer install -o';
            return;
        }
        // deal with /handler/{path:.*}
        $path = $request->get('path');
        if (!empty($path)) {
            // match path against default page handler
            $params = Route::match('/' . $path);
            if (!isset($params)) {
                return CopsResponse::sendError($request, 'Unknown path for feed: ' . $path);
            }
            // set actual path params in request
            $request->setParams($params);
        }
        $page = $request->get('page', PageId::INDEX);
        $query = $request->get('query');  // 'q' by default for php-opds
        if ($query) {
            $page = PageId::OPENSEARCH_QUERY;
        }

        if (Config::get('fetch_protect') == '1') {
            $session = $this->getContext()->getSession();
            $session->start();
            $connected = $session->get('connected');
            if (!isset($connected)) {
                $session->set('connected', 0);
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
                $response = $OPDSRender->render($currentPage, $request);
        }

        // @todo convert OPDS Response to COPS Response?
        foreach ($response->getHeaders() as $type => $value) {
            header($type . ': ' . $value);
        }
        http_response_code($response->getStatus());

        echo $response->getContents();

        $result = new CopsResponse();
        // tell response it's already sent
        $result->isSent(true);
        return $result;
    }
}
