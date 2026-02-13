<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;
use InvalidArgumentException;
use Throwable;

/**
 * Handle JSON ajax requests
 * URL format: index.php?page={page}&...
 * with Accept: application/json or X-Requested-With: XMLHttpRequest
 */
class JsonHandler extends PageHandler
{
    public const HANDLER = "json";

    public static function getRoutes()
    {
        // same routes as HtmlHandler - see util.js
        //return parent::getRoutes();
        return [];
    }

    public function handle($request)
    {
        $page = $request->get('page');
        if (in_array($page, [PageId::CUSTOMIZE, PageId::FILTER])) {
            $session = $this->getContext()->getSession();
            $session->start();
            $request->setSession($session);
        }
        $response = new Response(Response::MIME_TYPE_JSON);

        $json = new JsonRenderer($request, $response);

        try {
            return $response->setContent(json_encode($json->getJson($request)));
        } catch (InvalidArgumentException $e) {
            return Response::notFound($request, $e->getMessage());
        } catch (Throwable $e) {
            error_log($e);
            return Response::sendError($request, $e->getMessage());
        }
    }
}
