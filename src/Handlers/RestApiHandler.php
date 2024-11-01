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
    public const DEFINITION_FILE = 'resources/openapi.json';
    public const PREFIX = "/restapi";
    public const RESOURCE = "_resource";
    public const PARAMLIST = [
        // @todo support paramlist by resource here?
        "Database" => ["db", "name"],
        "Note" => ["type", "id", "title"],
        "Preference" => ["key"],
        "Annotation" => ["bookId", "id"],
        "Metadata" => ["bookId", "element", "name"],
        "" => ["route"],
    ];
    public const GROUP_PARAM = "_resource";

    /** @var ?string */
    protected static $baseUrl = null;

    public static function getRoutes()
    {
        // Note: this supports all other routes with /restapi prefix
        // extra routes supported by REST API
        return [
            "restapi-customtypes" => [static::PREFIX . "/custom", [static::RESOURCE => "CustomColumnType"]],
            "restapi-database-table" => [static::PREFIX . "/databases/{db}/{name}", [static::RESOURCE => "Database"]],
            "restapi-database" => [static::PREFIX . "/databases/{db}", [static::RESOURCE => "Database"]],
            "restapi-databases" => [static::PREFIX . "/databases", [static::RESOURCE => "Database"]],
            "restapi-openapi" => [static::PREFIX . "/openapi", [static::RESOURCE => "openapi"]],
            "restapi-route" => [static::PREFIX . "/routes", [static::RESOURCE => "route"]],
            "restapi-handler" => [static::PREFIX . "/handlers", [static::RESOURCE => "handler"]],
            "restapi-note" => [static::PREFIX . "/notes/{type}/{id}/{title}", [static::RESOURCE => "Note"]],
            "restapi-notes-type-id" => [static::PREFIX . "/notes/{type}/{id}", [static::RESOURCE => "Note"]],
            "restapi-notes-type" => [static::PREFIX . "/notes/{type}", [static::RESOURCE => "Note"]],
            "restapi-notes" => [static::PREFIX . "/notes", [static::RESOURCE => "Note"]],
            "restapi-preference" => [static::PREFIX . "/preferences/{key}", [static::RESOURCE => "Preference"]],
            "restapi-preferences" => [static::PREFIX . "/preferences", [static::RESOURCE => "Preference"]],
            "restapi-annotation" => [static::PREFIX . "/annotations/{bookId}/{id}", [static::RESOURCE => "Annotation"]],
            "restapi-annotations-book" => [static::PREFIX . "/annotations/{bookId}", [static::RESOURCE => "Annotation"]],
            "restapi-annotations" => [static::PREFIX . "/annotations", [static::RESOURCE => "Annotation"]],
            "restapi-metadata-element-name" => [static::PREFIX . "/metadata/{bookId}/{element}/{name}", [static::RESOURCE => "Metadata"]],
            "restapi-metadata-element" => [static::PREFIX . "/metadata/{bookId}/{element}", [static::RESOURCE => "Metadata"]],
            "restapi-metadata" => [static::PREFIX . "/metadata/{bookId}", [static::RESOURCE => "Metadata"]],
            "restapi-user-details" => [static::PREFIX . "/user/details", [static::RESOURCE => "User"]],
            "restapi-user" => [static::PREFIX . "/user", [static::RESOURCE => "User"]],
            // add default routes for handler to generate links
            "restapi-path" => [static::PREFIX . "/{path:.*}"],  // [static::RESOURCE => "path"]
            //"restapi-none" => [static::PREFIX . ""],
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
     * Get REST API link for resource handled by RestApiHandler
     * @param string $className
     * @param array<mixed> $params
     * @return string
     */
    public static function resource($className, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['resource'] += 1;
        }
        $params = static::addResourceParam($className, $params);
        return static::link($params);
    }

    /**
     * Get REST API link for handler, page, params handled elsewhere
     * @param class-string|null $handler
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function getHandlerLink($handler = null, $page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['handler'] += 1;
        }
        // use page route with /restapi prefix instead
        $handler ??= Route::getHandler('html');
        $params[Route::HANDLER_PARAM] = static::class;
        $link = Route::process($handler, $page, $params);
        return $link;
        //return str_replace(Route::base() . Route::endpoint(), static::getBaseUrl(), $link);
    }

    /**
     * Get base URL for REST API links
     * @return string
     */
    public static function getBaseUrl()
    {
        if (!isset(static::$baseUrl)) {
            // Route::link(static::class) doesn't contain prefix anymore without route
            $link = static::link(['path' => 'PATH']);
            static::$baseUrl = str_replace('/PATH', '', $link);
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
        $data = ['link' => static::link([static::RESOURCE => 'openapi'])];
        $template = dirname(__DIR__, 2) . '/templates/restapi.html';

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, $template));
    }
}
