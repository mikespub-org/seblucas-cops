<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Output\Response;
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
        $response = new Response('application/json;charset=utf-8');

        $json = new JsonRenderer($request, $response);

        try {
            return $response->setContent(json_encode($json->getJson($request)));
        } catch (Throwable $e) {
            error_log($e);
            // this will call exit()
            Response::sendError($request, $e->getMessage());
        }
    }
}
