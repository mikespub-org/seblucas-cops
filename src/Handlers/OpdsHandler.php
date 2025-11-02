<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Middleware\ConnectMiddleware;
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

    public static function getMiddleware()
    {
        return [
            ConnectMiddleware::class,
        ];
    }

    public function handle($request)
    {
        if (!class_exists('\Kiwilan\Opds\OpdsResponse')) {
            echo 'For standard OPDS 1.2 feeds in XML format please use /feed instead of /opds in URL links' . "<br/>\n";
            echo 'This handler for OPDS 2.0 in JSON format is available in developer mode only (without --no-dev option):' . "<br/>\n";
            echo '$ composer install -o' . "<br/>\n";
            return;
        }
        // deal with /handler/{path:.*}
        $path = $request->get('path');
        if (!empty($path)) {
            // match path against default page handler
            $params = $this->getContext()->getRouter()->match('/' . $path);
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

        // set session connected in ConnectMiddleware

        $opdsRenderer = new OpdsRenderer();

        switch ($page) {
            case PageId::OPENSEARCH :
            case PageId::SEARCH :
                $response = $opdsRenderer->getOpenSearch($request);
                break;
            default:
                $currentPage = PageId::getPage($page, $request);
                $response = $opdsRenderer->render($currentPage, $request);
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
