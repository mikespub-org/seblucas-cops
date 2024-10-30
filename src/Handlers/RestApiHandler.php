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
class RestApiHandler extends PageHandler
{
    public const HANDLER = "restapi";
    public const PREFIX = "/restapi";
    public const RESOURCE = "_resource";

    /** @var ?string */
    protected static $baseUrl = null;

    public static function getRoutes()
    {
        // Note: this supports all other routes with /restapi prefix
        // extra routes supported by REST API
        return [
            static::PREFIX . "/custom" => [static::PARAM => static::HANDLER, static::RESOURCE => "CustomColumnType"],
            static::PREFIX . "/databases/{db}/{name}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Database"],
            static::PREFIX . "/databases/{db}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Database"],
            static::PREFIX . "/databases" => [static::PARAM => static::HANDLER, static::RESOURCE => "Database"],
            static::PREFIX . "/openapi" => [static::PARAM => static::HANDLER, static::RESOURCE => "openapi"],
            static::PREFIX . "/routes" => [static::PARAM => static::HANDLER, static::RESOURCE => "route"],
            static::PREFIX . "/groups" => [static::PARAM => static::HANDLER, static::RESOURCE => "group"],
            static::PREFIX . "/notes/{type}/{id}/{title}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Note"],
            static::PREFIX . "/notes/{type}/{id}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Note"],
            static::PREFIX . "/notes/{type}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Note"],
            static::PREFIX . "/notes" => [static::PARAM => static::HANDLER, static::RESOURCE => "Note"],
            static::PREFIX . "/preferences/{key}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Preference"],
            static::PREFIX . "/preferences" => [static::PARAM => static::HANDLER, static::RESOURCE => "Preference"],
            static::PREFIX . "/annotations/{bookId}/{id}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Annotation"],
            static::PREFIX . "/annotations/{bookId}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Annotation"],
            static::PREFIX . "/annotations" => [static::PARAM => static::HANDLER, static::RESOURCE => "Annotation"],
            static::PREFIX . "/metadata/{bookId}/{element}/{name}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Metadata"],
            static::PREFIX . "/metadata/{bookId}/{element}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Metadata"],
            static::PREFIX . "/metadata/{bookId}" => [static::PARAM => static::HANDLER, static::RESOURCE => "Metadata"],
            static::PREFIX . "/user/details" => [static::PARAM => static::HANDLER, static::RESOURCE => "User"],
            static::PREFIX . "/user" => [static::PARAM => static::HANDLER, static::RESOURCE => "User"],
            // add default routes for handler to generate links
            static::PREFIX . "/{route:.*}" => [static::PARAM => static::HANDLER],
            //static::PREFIX . "" => [static::PARAM => static::HANDLER],
        ];
    }

    /**
     * Summary of addResourceParam
     * @param string $className
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public static function addResourceParam($className, $params = [])
    {
        $classParts = explode('\\', $className);
        $params[static::RESOURCE] ??= end($classParts);
        return $params;
    }

    /**
     * Get REST API link to resource handled by RestApiHandler
     * @param string $className
     * @param array<mixed> $params
     * @return string
     */
    public static function getResourceLink($className, $params = [])
    {
        $params = static::addResourceParam($className, $params);
        return static::getLink($params);
    }

    /**
     * Get REST API link for handler, page, params handled elsewhere
     * @param string|null $handler
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function getHandlerLink($handler = null, $page = null, $params = [])
    {
        $link = Route::link($handler, $page, $params);
        return str_replace(Route::base() . Route::endpoint(), static::getBaseUrl(), $link);
    }

    /**
     * Get base URL for REST API links
     * @return string
     */
    public static function getBaseUrl()
    {
        if (!isset(static::$baseUrl)) {
            // Route::link(static::HANDLER) doesn't contain prefix anymore without route
            $link = static::getLink(['route' => 'ROUTE']);
            static::$baseUrl = str_replace('/ROUTE', '', $link);
        }
        return static::$baseUrl;
    }

    public function handle($request)
    {
        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');

        $path = $request->path();
        if (empty($path) || $path == '/restapi/') {
            return $this->getSwaggerUI();
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

    /**
     * Summary of getSwaggerUI
     * @return Response
     */
    public function getSwaggerUI()
    {
        $data = ['link' => static::getLink([static::RESOURCE => 'openapi'])];
        $template = dirname(__DIR__, 2) . '/templates/restapi.html';

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, $template));
    }
}
