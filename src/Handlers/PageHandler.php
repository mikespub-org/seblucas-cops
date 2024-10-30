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
        // Format: route => [page => page], or route => [page => page, fixed => 1, ...] with fixed params
        return [
            "/index" => ["page" => PageId::INDEX],
            // @todo support unicode pattern \pL for first letter - but see https://github.com/nikic/FastRoute/issues/154
            "/authors/letter/{id}" => ["page" => PageId::AUTHORS_FIRST_LETTER],
            "/authors/letter" => ["page" => PageId::ALL_AUTHORS, "letter" => 1],
            "/authors/{id:\d+}/{title}" => ["page" => PageId::AUTHOR_DETAIL],
            "/authors/{id:\d+}" => ["page" => PageId::AUTHOR_DETAIL],
            "/authors" => ["page" => PageId::ALL_AUTHORS],
            "/books/letter/{id:\w}" => ["page" => PageId::ALL_BOOKS_LETTER],
            "/books/letter" => ["page" => PageId::ALL_BOOKS, "letter" => 1],
            "/books/year/{id:\d+}" => ["page" => PageId::ALL_BOOKS_YEAR],
            "/books/year" => ["page" => PageId::ALL_BOOKS, "year" => 1],
            "/books/{id:\d+}/{author}/{title}" => ["page" => PageId::BOOK_DETAIL],
            "/books/{id:\d+}" => ["page" => PageId::BOOK_DETAIL],
            "/books" => ["page" => PageId::ALL_BOOKS],
            "/series/{id:\d+}/{title}" => ["page" => PageId::SERIE_DETAIL],
            "/series/{id:\d+}" => ["page" => PageId::SERIE_DETAIL],
            "/series" => ["page" => PageId::ALL_SERIES],
            "/query/{query}/{scope}" => ["page" => PageId::OPENSEARCH_QUERY, "search" => 1],
            "/query/{query}" => ["page" => PageId::OPENSEARCH_QUERY, "search" => 1],
            "/search/{query}/{scope}" => ["page" => PageId::OPENSEARCH_QUERY],
            "/search/{query}" => ["page" => PageId::OPENSEARCH_QUERY],
            "/search" => ["page" => PageId::OPENSEARCH],
            "/recent" => ["page" => PageId::ALL_RECENT_BOOKS],
            "/tags/{id:\d+}/{title}" => ["page" => PageId::TAG_DETAIL],
            "/tags/{id:\d+}" => ["page" => PageId::TAG_DETAIL],
            "/tags" => ["page" => PageId::ALL_TAGS],
            "/custom/{custom:\d+}/{id}" => ["page" => PageId::CUSTOM_DETAIL],
            "/custom/{custom:\d+}" => ["page" => PageId::ALL_CUSTOMS],
            "/about" => ["page" => PageId::ABOUT],
            "/languages/{id:\d+}/{title}" => ["page" => PageId::LANGUAGE_DETAIL],
            "/languages/{id:\d+}" => ["page" => PageId::LANGUAGE_DETAIL],
            "/languages" => ["page" => PageId::ALL_LANGUAGES],
            "/customize" => ["page" => PageId::CUSTOMIZE],
            "/publishers/{id:\d+}/{title}" => ["page" => PageId::PUBLISHER_DETAIL],
            "/publishers/{id:\d+}" => ["page" => PageId::PUBLISHER_DETAIL],
            "/publishers" => ["page" => PageId::ALL_PUBLISHERS],
            "/ratings/{id:\d+}/{title}" => ["page" => PageId::RATING_DETAIL],
            "/ratings/{id:\d+}" => ["page" => PageId::RATING_DETAIL],
            "/ratings" => ["page" => PageId::ALL_RATINGS],
            "/identifiers/{id:\w+}/{title}" => ["page" => PageId::IDENTIFIER_DETAIL],
            "/identifiers/{id:\w+}" => ["page" => PageId::IDENTIFIER_DETAIL],
            "/identifiers" => ["page" => PageId::ALL_IDENTIFIERS],
            "/libraries" => ["page" => PageId::ALL_LIBRARIES],
        ];
    }

    /**
     * Summary of getPageLink
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function getPageLink($page = null, $params = [])
    {
        return Route::link(static::HANDLER, $page, $params);
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
        if (!empty($params['page']) && empty($params['_route'])) {
            $params['_route'] = self::HANDLER . '-' . $params['page'];
            if (!empty($params['id'])) {
                $params['_route'] .= '-id';
                if (!empty($params['title'])) {
                    $params['_route'] .= '-title';
                }
            }
        }
         */
        // @todo use _route later
        unset($params["_route"]);
        $match = $params["page"] ?? '';
        // filter routes by page before matching
        $group = array_filter($routes, function ($fixed) use ($match) {
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
        if (!empty($params["_route"])) {
            return $params["_route"];
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
