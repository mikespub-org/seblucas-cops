<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;

/**
 * Generic page handler extended by HtmlHandler and JsonHandler
 * URL format: ...?page={page}&...
 */
class PageHandler extends BaseHandler
{
    public const HANDLER = "page";
    public const PARAMLIST = ["page", "id", "letter", "year", "author", "title", "query", "scope", "search", "custom"];

    public static function getRoutes()
    {
        // Format: name => [path, [page => page, fixed => 1, ...], ['GET', ...], ['utf8' => true]] with page & fixed params, methods and options
        return [
            "page-index" => ["/index", ["page" => PageId::INDEX]],
            // @todo support unicode pattern \pL for first letter - but see https://github.com/nikic/FastRoute/issues/154
            "page-2-id" => ["/authors/letter/{id}", ["page" => PageId::AUTHORS_FIRST_LETTER]],
            "page-1-letter" => ["/authors/letter", ["page" => PageId::ALL_AUTHORS, "letter" => 1]],
            "page-3-id-title" => ["/authors/{id:\d+}/{title}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-3-id" => ["/authors/{id:\d+}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-1" => ["/authors", ["page" => PageId::ALL_AUTHORS]],
            "page-5-id" => ["/books/letter/{id:\w}", ["page" => PageId::ALL_BOOKS_LETTER]],
            "page-4-letter" => ["/books/letter", ["page" => PageId::ALL_BOOKS, "letter" => 1]],
            "page-50-id" => ["/books/year/{id:\d+}", ["page" => PageId::ALL_BOOKS_YEAR]],
            "page-4-year" => ["/books/year", ["page" => PageId::ALL_BOOKS, "year" => 1]],
            "page-13-id-author-title" => ["/books/{id:\d+}/{author}/{title}", ["page" => PageId::BOOK_DETAIL]],
            "page-13-id" => ["/books/{id:\d+}", ["page" => PageId::BOOK_DETAIL]],
            "page-4" => ["/books", ["page" => PageId::ALL_BOOKS]],
            "page-7-id-title" => ["/series/{id:\d+}/{title}", ["page" => PageId::SERIE_DETAIL]],
            "page-7-id" => ["/series/{id:\d+}", ["page" => PageId::SERIE_DETAIL]],
            "page-6" => ["/series", ["page" => PageId::ALL_SERIES]],
            // this is for type-ahead (with search param)
            "page-9-query-scope" => ["/query/{query}/{scope}", ["page" => PageId::OPENSEARCH_QUERY, "search" => 1]],
            "page-9-query" => ["/query/{query}", ["page" => PageId::OPENSEARCH_QUERY, "search" => 1]],
            // this is for the user (nicer looking)
            "page-9-search-scope" => ["/search/{query}/{scope}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-9-search" => ["/search/{query}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-8" => ["/search", ["page" => PageId::OPENSEARCH]],
            "page-10" => ["/recent", ["page" => PageId::ALL_RECENT_BOOKS]],
            "page-12-id-title" => ["/tags/{id:\d+}/{title}", ["page" => PageId::TAG_DETAIL]],
            "page-12-id" => ["/tags/{id:\d+}", ["page" => PageId::TAG_DETAIL]],
            "page-11" => ["/tags", ["page" => PageId::ALL_TAGS]],
            "page-15-custom-id" => ["/custom/{custom:\d+}/{id}", ["page" => PageId::CUSTOM_DETAIL]],
            "page-14-custom" => ["/custom/{custom:\d+}", ["page" => PageId::ALL_CUSTOMS]],
            "page-16" => ["/about", ["page" => PageId::ABOUT]],
            "page-18-id-title" => ["/languages/{id:\d+}/{title}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-18-id" => ["/languages/{id:\d+}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-17" => ["/languages", ["page" => PageId::ALL_LANGUAGES]],
            "page-19" => ["/customize", ["page" => PageId::CUSTOMIZE]],
            "page-21-id-title" => ["/publishers/{id:\d+}/{title}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-21-id" => ["/publishers/{id:\d+}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-20" => ["/publishers", ["page" => PageId::ALL_PUBLISHERS]],
            "page-23-id-title" => ["/ratings/{id:\d+}/{title}", ["page" => PageId::RATING_DETAIL]],
            "page-23-id" => ["/ratings/{id:\d+}", ["page" => PageId::RATING_DETAIL]],
            "page-22" => ["/ratings", ["page" => PageId::ALL_RATINGS]],
            "page-42-id-title" => ["/identifiers/{id:\w+}/{title}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-42-id" => ["/identifiers/{id:\w+}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-41" => ["/identifiers", ["page" => PageId::ALL_IDENTIFIERS]],
            "page-43" => ["/libraries", ["page" => PageId::ALL_LIBRARIES]],
        ];
    }

    /**
     * Get link for the default page handler with params (incl _route)
     * @param array<mixed> $params
     * @return string
     */
    public static function getLink($params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['pageLink'] += 1;
        }
        // use default page handler to find the route for html and json
        unset($params[Route::HANDLER_PARAM]);
        return Route::process(static::class, null, $params);
    }

    /**
     * Summary of getPageLink - currently unused (all calls set page in params)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function getPageLink($page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['pageLink'] += 1;
        }
        // use default page handler to find the route for html and json
        unset($params[Route::HANDLER_PARAM]);
        return Route::process(static::class, $page, $params);
    }

    /**
     * Summary of findRoute
     * @param array<mixed> $params
     * @return string|null
     */
    public static function findRoute($params = [])
    {
        $routes = static::getRoutes();
        // check parent class if needed, e.g. for JsonHandler
        if (empty($routes) && $parent = get_parent_class(static::class)) {
            $routes = $parent::getRoutes();
        }
        /**
        // @todo use _route later
        if (!empty($params['page']) && empty($params[Route::ROUTE_PARAM])) {
            $params[Route::ROUTE_PARAM] = self::HANDLER . '-' . $params['page'];
            if (!empty($params['id'])) {
                $params[Route::ROUTE_PARAM] .= '-id';
                if (!empty($params['title'])) {
                    $params[Route::ROUTE_PARAM] .= '-title';
                }
            }
        }
         */
        // use _route if available
        if (isset($params[Route::ROUTE_PARAM])) {
            $name = $params[Route::ROUTE_PARAM];
            unset($params[Route::ROUTE_PARAM]);
            if (!empty($name) && !empty($routes[$name])) {
                return Route::findMatchingRoute([$name => $routes[$name]], $params);
            }
        }
        $match = $params["page"] ?? '';
        // filter routes by page before matching
        $group = array_filter($routes, function ($route) use ($match) {
            // Add fixed if needed
            $route[] = [];
            [$path, $fixed] = $route;
            return $match == ($fixed["page"] ?? '');
        });
        if (count($group) < 1) {
            return null;
        }
        return Route::findMatchingRoute($group, $params);
    }

    /**
     * Summary of findRouteName
     * @param array<mixed> $params
     * @return string
     */
    public static function findRouteName($params)
    {
        if (!empty($params[Route::ROUTE_PARAM])) {
            return $params[Route::ROUTE_PARAM];
        }
        $name = self::HANDLER;
        $name .= '-' . ($params["page"] ?? '');
        unset($params["page"]);
        if (count(static::getRoutes()) > 1) {
            $accept = array_intersect(array_keys($params), static::PARAMLIST);
            if (!empty($accept)) {
                $name = $name . '-' . implode('-', $accept);
            }
        }
        return $name;
    }

    public function handle($request)
    {
        // ...
    }
}
