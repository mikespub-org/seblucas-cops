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
use SebLucas\Cops\Output\RestApiProvider;
use SebLucas\Cops\Routing\UriGenerator;
use Exception;

/**
 * Handle REST API
 * URL format: index.php/restapi{/path}?db={db} etc.
 */
class RestApiHandler extends BaseHandler
{
    public const HANDLER = "restapi";
    public const PREFIX = "/restapi";
    public const RESOURCE = "_resource";
    public const PARAMLIST = [
        // @todo support paramlist by resource here?
        "Database" => ["db", "name"],
        "Note" => ["type", "item", "title"],
        "Preference" => ["key"],
        "Annotation" => ["bookId", "id"],
        "Metadata" => ["bookId", "element", "name"],
        "" => ["path"],
    ];
    public const GROUP_PARAM = "_resource";

    /** @var ?string */
    protected static $baseUrl = null;

    public static function getRoutes()
    {
        // Note: this supports all other routes with /restapi prefix
        // extra routes supported by REST API
        return [
            "restapi-customtypes" => [self::PREFIX . "/custom", [self::RESOURCE => "CustomColumnType"]],
            "restapi-database-table" => [self::PREFIX . "/databases/{db}/{name}", [self::RESOURCE => "Database"]],
            "restapi-database" => [self::PREFIX . "/databases/{db}", [self::RESOURCE => "Database"]],
            "restapi-databases" => [self::PREFIX . "/databases", [self::RESOURCE => "Database"]],
            "restapi-openapi" => [self::PREFIX . "/openapi", [self::RESOURCE => "openapi"]],
            "restapi-route" => [self::PREFIX . "/routes", [self::RESOURCE => "route"]],
            "restapi-handler" => [self::PREFIX . "/handlers", [self::RESOURCE => "handler"]],
            "restapi-note" => [self::PREFIX . "/notes/{type}/{item}/{title}", [self::RESOURCE => "Note"]],
            "restapi-notes-type-id" => [self::PREFIX . "/notes/{type}/{item}", [self::RESOURCE => "Note"]],
            "restapi-notes-type" => [self::PREFIX . "/notes/{type}", [self::RESOURCE => "Note"]],
            "restapi-notes" => [self::PREFIX . "/notes", [self::RESOURCE => "Note"]],
            "restapi-preference" => [self::PREFIX . "/preferences/{key}", [self::RESOURCE => "Preference"]],
            "restapi-preferences" => [self::PREFIX . "/preferences", [self::RESOURCE => "Preference"]],
            "restapi-annotation" => [self::PREFIX . "/annotations/{bookId}/{id}", [self::RESOURCE => "Annotation"]],
            "restapi-annotations-book" => [self::PREFIX . "/annotations/{bookId}", [self::RESOURCE => "Annotation"]],
            "restapi-annotations" => [self::PREFIX . "/annotations", [self::RESOURCE => "Annotation"]],
            "restapi-metadata-element-name" => [self::PREFIX . "/metadata/{bookId}/{element}/{name}", [self::RESOURCE => "Metadata"]],
            "restapi-metadata-element" => [self::PREFIX . "/metadata/{bookId}/{element}", [self::RESOURCE => "Metadata"]],
            "restapi-metadata" => [self::PREFIX . "/metadata/{bookId}", [self::RESOURCE => "Metadata"]],
            "restapi-user-details" => [self::PREFIX . "/user/details", [self::RESOURCE => "User"]],
            "restapi-user" => [self::PREFIX . "/user", [self::RESOURCE => "User"]],
            // add default routes for handler to generate links
            "restapi-path" => [self::PREFIX . "/{path:.*}"],  // [self::RESOURCE => "path"]
            //"restapi-none" => [self::PREFIX . ""],
        ];
    }

    /**
     * Summary of addResourceParam
     * @param class-string $className
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public static function addResourceParam($className, $params = [])
    {
        $classParts = explode('\\', $className);
        $params[self::RESOURCE] ??= end($classParts);
        return $params;
    }

    /**
     * Get REST API link for resource handled by RestApiHandler
     * @param class-string $className
     * @param array<mixed> $params
     * @return string
     */
    public static function resource($className, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['resource'] += 1;
        }
        $params = self::addResourceParam($className, $params);
        return self::link($params);
    }

    /**
     * Get REST API link for handler, page, params handled elsewhere
     * @param class-string|null $handler
     * @param string|int|null $page
     * @param array<mixed> $params
     * @deprecated 3.5.0 use handler::route(), handler::page() or handler:link()
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
        $params[Route::HANDLER_PARAM] = self::class;
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $link = UriGenerator::process($handler, $params);
        return $link;
    }

    /**
     * Get base URL for REST API links
     * @return string
     */
    public static function getBaseUrl()
    {
        if (!isset(self::$baseUrl)) {
            $link = self::link(['path' => 'PATH']);
            self::$baseUrl = str_replace('/PATH', '', $link);
        }
        return self::$baseUrl;
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

        $apiProvider = new RestApiProvider($request, $response);

        try {
            $output = $apiProvider->getOutput();
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
        $data = ['link' => self::link([self::RESOURCE => 'openapi'])];
        $template = dirname(__DIR__, 2) . '/templates/restapi.html';

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, $template));
    }
}
