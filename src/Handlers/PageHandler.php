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
    public const PREFIX = "";  // we have multiple prefixes here
    public const PARAMLIST = ["page", "id", "letter", "year", "author", "title", "query", "scope", "search", "custom"];
    public const GROUP_PARAM = "page";

    public static function getRoutes()
    {
        // Format: name => [path, [page => page, fixed => 1, ...], ['GET', ...], ['utf8' => true]] with page & fixed params, methods and options
        return [
            "page-index" => ["/index", ["page" => PageId::INDEX]],
            // @todo support unicode pattern \pL for first letter - but see https://github.com/nikic/FastRoute/issues/154
            "page-authors-letter" => ["/authors/letter/{id}", ["page" => PageId::AUTHORS_FIRST_LETTER]],
            "page-1-letter" => ["/authors/letter", ["page" => PageId::ALL_AUTHORS, "letter" => 1]],
            "page-author" => ["/authors/{id:\d+}/{title}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-3-id" => ["/authors/{id:\d+}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-authors" => ["/authors", ["page" => PageId::ALL_AUTHORS]],
            "page-books-letter" => ["/books/letter/{id:\w}", ["page" => PageId::ALL_BOOKS_LETTER]],
            "page-4-letter" => ["/books/letter", ["page" => PageId::ALL_BOOKS, "letter" => 1]],
            "page-books-year" => ["/books/year/{id:\d+}", ["page" => PageId::ALL_BOOKS_YEAR]],
            "page-4-year" => ["/books/year", ["page" => PageId::ALL_BOOKS, "year" => 1]],
            "page-book" => ["/books/{id:\d+}/{author}/{title}", ["page" => PageId::BOOK_DETAIL]],
            "page-13-id" => ["/books/{id:\d+}", ["page" => PageId::BOOK_DETAIL]],
            "page-books" => ["/books", ["page" => PageId::ALL_BOOKS]],
            "page-serie" => ["/series/{id:\d+}/{title}", ["page" => PageId::SERIE_DETAIL]],
            "page-7-id" => ["/series/{id:\d+}", ["page" => PageId::SERIE_DETAIL]],
            "page-series" => ["/series", ["page" => PageId::ALL_SERIES]],
            // this is for type-ahead (with search param)
            "page-typeahead" => ["/typeahead", ["page" => PageId::OPENSEARCH_QUERY, "search" => 1]],
            // this is for the user (nicer looking)
            "page-query-scope" => ["/search/{query}/{scope}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-query" => ["/search/{query}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-search" => ["/search", ["page" => PageId::OPENSEARCH]],
            "page-recent" => ["/recent", ["page" => PageId::ALL_RECENT_BOOKS]],
            "page-tag" => ["/tags/{id:\d+}/{title}", ["page" => PageId::TAG_DETAIL]],
            "page-12-id" => ["/tags/{id:\d+}", ["page" => PageId::TAG_DETAIL]],
            "page-tags" => ["/tags", ["page" => PageId::ALL_TAGS]],
            "page-custom" => ["/custom/{custom:\d+}/{id}", ["page" => PageId::CUSTOM_DETAIL]],
            "page-customtype" => ["/custom/{custom:\d+}", ["page" => PageId::ALL_CUSTOMS]],
            "page-about" => ["/about", ["page" => PageId::ABOUT]],
            "page-language" => ["/languages/{id:\d+}/{title}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-18-id" => ["/languages/{id:\d+}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-languages" => ["/languages", ["page" => PageId::ALL_LANGUAGES]],
            "page-customize" => ["/customize", ["page" => PageId::CUSTOMIZE]],
            "page-publisher" => ["/publishers/{id:\d+}/{title}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-21-id" => ["/publishers/{id:\d+}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-publishers" => ["/publishers", ["page" => PageId::ALL_PUBLISHERS]],
            "page-rating" => ["/ratings/{id:\d+}/{title}", ["page" => PageId::RATING_DETAIL]],
            "page-23-id" => ["/ratings/{id:\d+}", ["page" => PageId::RATING_DETAIL]],
            "page-ratings" => ["/ratings", ["page" => PageId::ALL_RATINGS]],
            "page-identifier" => ["/identifiers/{id:\w+}/{title}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-42-id" => ["/identifiers/{id:\w+}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-identifiers" => ["/identifiers", ["page" => PageId::ALL_IDENTIFIERS]],
            "page-libraries" => ["/libraries", ["page" => PageId::ALL_LIBRARIES]],
        ];
    }

    /**
     * Get link for the default page handler with params (incl _route)
     * @param array<mixed> $params
     * @return string
     */
    public static function link($params = [])
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
     * Get page link for the default page handler with params (incl _route)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function page($page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['pagePage'] += 1;
        }
        // use default page handler to find the route for html and json
        unset($params[Route::HANDLER_PARAM]);
        return Route::process(static::class, $page, $params);
    }

    /**
     * Get index page
     * @return string
     */
    public static function index()
    {
        $name = PageId::ROUTE_INDEX;
        $route = self::getRoutes()[$name] ?? ["/index"];
        $path = $route[0];
        return Route::absolute($path);
    }

    /**
     * Find all routes for matching - include parent routes in page handler
     * @return array<string, mixed>
     */
    public static function findRoutes()
    {
        $routes = static::getRoutes();
        // check parent class if needed, e.g. for JsonHandler
        if (empty($routes) && $parent = get_parent_class(static::class)) {
            $routes = $parent::getRoutes();
        }
        return $routes;
    }

    /**
     * Summary of findRouteName - @todo adapt to actual routes for each handler
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
