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
    public const ENDPOINT = "restapi";

    public static function getRoutes()
    {
        // extra routes supported by REST API
        return [
            "/custom" => [static::PARAM => static::ENDPOINT],
            "/databases/{db}/{name}" => [static::PARAM => static::ENDPOINT],
            "/databases/{db}" => [static::PARAM => static::ENDPOINT],
            "/databases" => [static::PARAM => static::ENDPOINT],
            "/openapi" => [static::PARAM => static::ENDPOINT],
            "/routes" => [static::PARAM => static::ENDPOINT],
            "/notes/{type}/{id}/{title}" => [static::PARAM => static::ENDPOINT],
            "/notes/{type}/{id}" => [static::PARAM => static::ENDPOINT],
            "/notes/{type}" => [static::PARAM => static::ENDPOINT],
            "/notes" => [static::PARAM => static::ENDPOINT],
            "/preferences/{key}" => [static::PARAM => static::ENDPOINT],
            "/preferences" => [static::PARAM => static::ENDPOINT],
            "/annotations/{bookId}/{id}" => [static::PARAM => static::ENDPOINT],
            "/annotations/{bookId}" => [static::PARAM => static::ENDPOINT],
            "/annotations" => [static::PARAM => static::ENDPOINT],
            "/metadata/{bookId}/{element}/{name}" => [static::PARAM => static::ENDPOINT],
            "/metadata/{bookId}/{element}" => [static::PARAM => static::ENDPOINT],
            "/metadata/{bookId}" => [static::PARAM => static::ENDPOINT],
            "/user/details" => [static::PARAM => static::ENDPOINT],
            "/user" => [static::PARAM => static::ENDPOINT],
        ];
    }

    public function handle($request)
    {
        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');

        $path = $request->path();
        if (empty($path)) {
            header('Content-Type:text/html;charset=utf-8');

            $data = ['link' => Route::url(Config::ENDPOINT[static::ENDPOINT]) . '/openapi'];
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
