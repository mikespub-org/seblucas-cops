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
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\RestApi;
use Exception;

/**
 * Handle REST API
 * URL format: restapi.php{/route}?db={db} etc.
 */
class RestApiHandler extends BaseHandler
{
    public const HANDLER = "restapi";

    public static function getRoutes()
    {
        // extra routes supported by REST API
        return [
            "/custom" => [static::PARAM => static::HANDLER],
            "/databases/{db}/{name}" => [static::PARAM => static::HANDLER],
            "/databases/{db}" => [static::PARAM => static::HANDLER],
            "/databases" => [static::PARAM => static::HANDLER],
            "/openapi" => [static::PARAM => static::HANDLER],
            "/routes" => [static::PARAM => static::HANDLER],
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
            "/restapi/{route:.*}" => [static::PARAM => static::HANDLER],
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
            header('Content-Type:text/html;charset=utf-8');

            $data = ['link' => Route::link(static::HANDLER) . '/openapi'];
            $template = dirname(__DIR__, 2) . '/templates/restapi.html';
            echo Format::template($data, $template);
            return;
        }

        $apiHandler = new RestApi($request);

        header('Content-Type:application/json;charset=utf-8');

        try {
            echo $apiHandler->getOutput();
        } catch (Exception $e) {
            echo json_encode(["Exception" => $e->getMessage()]);
        }
    }
}
