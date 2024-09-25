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
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Output\RestApi;
use Exception;

/**
 * Handle REST API
 * URL format: index.php/restapi{/route}?db={db} etc.
 */
class RestApiHandler extends BaseHandler
{
    public const HANDLER = "restapi";

    public static function getRoutes()
    {
        // Note: this supports all other routes with /restapi prefix
        // extra routes supported by REST API
        return [
            // add default routes for handler to generate links
            "/restapi/{route:.*}" => [static::PARAM => static::HANDLER],
            "/restapi" => [static::PARAM => static::HANDLER],
            "/custom" => [static::PARAM => static::HANDLER],
            "/databases/{db}/{name}" => [static::PARAM => static::HANDLER],
            "/databases/{db}" => [static::PARAM => static::HANDLER],
            "/databases" => [static::PARAM => static::HANDLER],
            "/openapi" => [static::PARAM => static::HANDLER],
            "/routes" => [static::PARAM => static::HANDLER],
            "/pages" => [static::PARAM => static::HANDLER],
            "/notes/{type}/{id}/{title}" => [static::PARAM => static::HANDLER],
            "/notes/{type}/{id}" => [static::PARAM => static::HANDLER],
            "/notes/{type}" => [static::PARAM => static::HANDLER],
            "/notes" => [static::PARAM => static::HANDLER],
            "/preferences/{key}" => [static::PARAM => static::HANDLER],
            "/preferences" => [static::PARAM => static::HANDLER],
            "/annotations/{bookId}/{id}" => [static::PARAM => static::HANDLER],
            "/annotations/{bookId}" => [static::PARAM => static::HANDLER],
            "/annotations" => [static::PARAM => static::HANDLER],
            "/metadata/{bookId}/{element}/{name}" => [static::PARAM => static::HANDLER],
            "/metadata/{bookId}/{element}" => [static::PARAM => static::HANDLER],
            "/metadata/{bookId}" => [static::PARAM => static::HANDLER],
            "/user/details" => [static::PARAM => static::HANDLER],
            "/user" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');

        $path = $request->path();
        if (empty($path) || $path == '/restapi/') {
            $data = ['link' => Route::link(static::HANDLER, null, ['route' => 'openapi'])];
            $template = dirname(__DIR__, 2) . '/templates/restapi.html';

            $response = new Response('text/html;charset=utf-8');
            return $response->setContent(Format::template($data, $template));
        }

        $response = new Response('application/json;charset=utf-8');

        $apiHandler = new RestApi($request, $response);

        try {
            $output = $apiHandler->getOutput();
            if ($output instanceof Response) {
                return $output;
            }
            return $response->setContent($output);
        } catch (Exception $e) {
            return $response->setContent(json_encode(["Exception" => $e->getMessage()]));
        }
    }
}
