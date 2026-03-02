<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Routing\UriGenerator;

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
            "page-authors-letter" => ["/authors/letter/{letter}", ["page" => PageId::AUTHORS_FIRST_LETTER]],
            "page-authors-letters" => ["/authors/letter", ["page" => PageId::ALL_AUTHORS, "letter" => 1]],
            "page-author" => ["/authors/{id:\d+}/{title}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-author-id" => ["/authors/{id:\d+}", ["page" => PageId::AUTHOR_DETAIL]],
            "page-authors" => ["/authors", ["page" => PageId::ALL_AUTHORS]],
            "page-books-letter" => ["/books/letter/{letter}", ["page" => PageId::ALL_BOOKS_LETTER]],
            "page-books-letters" => ["/books/letter", ["page" => PageId::ALL_BOOKS, "letter" => 1]],
            "page-books-year" => ["/books/year/{year:\d+}", ["page" => PageId::ALL_BOOKS_YEAR]],
            "page-books-years" => ["/books/year", ["page" => PageId::ALL_BOOKS, "year" => 1]],
            "page-book" => ["/books/{id:\d+}/{author}/{title}", ["page" => PageId::BOOK_DETAIL]],
            "page-book-id" => ["/books/{id:\d+}", ["page" => PageId::BOOK_DETAIL]],
            "page-books" => ["/books", ["page" => PageId::ALL_BOOKS]],
            "page-series-letter" => ["/series/letter/{letter}", ["page" => PageId::SERIES_FIRST_LETTER]],
            "page-series-letters" => ["/series/letter", ["page" => PageId::ALL_SERIES, "letter" => 1]],
            "page-serie" => ["/series/{id:\d+}/{title}", ["page" => PageId::SERIE_DETAIL]],
            "page-serie-id" => ["/series/{id:\d+}", ["page" => PageId::SERIE_DETAIL]],
            "page-series" => ["/series", ["page" => PageId::ALL_SERIES]],
            // this is for type-ahead (with search param)
            "page-typeahead" => ["/typeahead", ["page" => PageId::OPENSEARCH_QUERY, "search" => 1]],
            // this is for the user (nicer looking)
            "page-query-scope" => ["/search/{query}/{scope}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-query" => ["/search/{query}", ["page" => PageId::OPENSEARCH_QUERY]],
            "page-search" => ["/search", ["page" => PageId::OPENSEARCH]],
            "page-recent" => ["/recent", ["page" => PageId::ALL_RECENT_BOOKS]],
            "page-tags-letter" => ["/tags/letter/{letter}", ["page" => PageId::TAGS_FIRST_LETTER]],
            "page-tags-letters" => ["/tags/letter", ["page" => PageId::ALL_TAGS, "letter" => 1]],
            "page-tag" => ["/tags/{id:\d+}/{title}", ["page" => PageId::TAG_DETAIL]],
            "page-tag-id" => ["/tags/{id:\d+}", ["page" => PageId::TAG_DETAIL]],
            "page-tags" => ["/tags", ["page" => PageId::ALL_TAGS]],
            "page-custom" => ["/custom/{custom:\d+}/{id}", ["page" => PageId::CUSTOM_DETAIL]],
            "page-customtype" => ["/custom/{custom:\d+}", ["page" => PageId::ALL_CUSTOMS]],
            "page-about" => ["/about", ["page" => PageId::ABOUT]],
            "page-language" => ["/languages/{id:\d+}/{title}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-language-id" => ["/languages/{id:\d+}", ["page" => PageId::LANGUAGE_DETAIL]],
            "page-languages" => ["/languages", ["page" => PageId::ALL_LANGUAGES]],
            "page-customize" => ["/customize", ["page" => PageId::CUSTOMIZE], ["GET", "POST"]],
            "page-publishers-letter" => ["/publishers/letter/{letter}", ["page" => PageId::PUBLISHERS_FIRST_LETTER]],
            "page-publishers-letters" => ["/publishers/letter", ["page" => PageId::ALL_PUBLISHERS, "letter" => 1]],
            "page-publisher" => ["/publishers/{id:\d+}/{title}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-publisher-id" => ["/publishers/{id:\d+}", ["page" => PageId::PUBLISHER_DETAIL]],
            "page-publishers" => ["/publishers", ["page" => PageId::ALL_PUBLISHERS]],
            "page-rating" => ["/ratings/{id:\d+}/{title}", ["page" => PageId::RATING_DETAIL]],
            "page-rating-id" => ["/ratings/{id:\d+}", ["page" => PageId::RATING_DETAIL]],
            "page-ratings" => ["/ratings", ["page" => PageId::ALL_RATINGS]],
            "page-identifier" => ["/identifiers/{id:\w+}/{title}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-identifier-id" => ["/identifiers/{id:\w+}", ["page" => PageId::IDENTIFIER_DETAIL]],
            "page-identifiers" => ["/identifiers", ["page" => PageId::ALL_IDENTIFIERS]],
            "page-format" => ["/formats/{id:\w+}", ["page" => PageId::FORMAT_DETAIL]],
            "page-formats" => ["/formats", ["page" => PageId::ALL_FORMATS]],
            "page-libraries" => ["/libraries", ["page" => PageId::ALL_LIBRARIES]],
            "page-filter" => ["/filter", ["page" => PageId::FILTER], ["GET", "POST"]],
        ];
    }

    /**
     * Get link for the default page handler with params (incl _route)
     * @param array<mixed> $params
     * @return string
     */
    public static function link($params = [])
    {
        // use default page handler to find the route for html and json
        unset($params[Request::HANDLER_PARAM]);
        return UriGenerator::process($params);
    }

    /**
     * Get page link for the default page handler with params (incl _route)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function page($page = null, $params = [])
    {
        if (!empty($page)) {
            $params['page'] = $page;
        }
        return static::link($params);
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
        return UriGenerator::absolute($path);
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

    public function handle($request)
    {
        // ...
    }
}
