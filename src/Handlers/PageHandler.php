<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Pages\PageId;

/**
 * Generic page handler extended by HtmlHandler and JsonHandler
 * URL format: ...?page={page}&...
 */
class PageHandler extends BaseHandler
{
    public static function getRoutes()
    {
        // Format: route => page, or route => [page => page, fixed => 1, ...] with fixed params
        return [
            "/index" => PageId::INDEX,
            // @todo support unicode pattern \pL for first letter - but see https://github.com/nikic/FastRoute/issues/154
            "/authors/letter/{id}" => PageId::AUTHORS_FIRST_LETTER,
            "/authors/letter" => ["page" => PageId::ALL_AUTHORS, "letter" => 1],
            "/authors/{id:\d+}/{title}" => PageId::AUTHOR_DETAIL,
            "/authors/{id:\d+}" => PageId::AUTHOR_DETAIL,
            "/authors" => PageId::ALL_AUTHORS,
            "/books/letter/{id:\w}" => PageId::ALL_BOOKS_LETTER,
            "/books/letter" => ["page" => PageId::ALL_BOOKS, "letter" => 1],
            "/books/year/{id:\d+}" => PageId::ALL_BOOKS_YEAR,
            "/books/year" => ["page" => PageId::ALL_BOOKS, "year" => 1],
            "/books/{id:\d+}/{author}/{title}" => PageId::BOOK_DETAIL,
            "/books/{id:\d+}" => PageId::BOOK_DETAIL,
            "/books" => PageId::ALL_BOOKS,
            "/series/{id:\d+}/{title}" => PageId::SERIE_DETAIL,
            "/series/{id:\d+}" => PageId::SERIE_DETAIL,
            "/series" => PageId::ALL_SERIES,
            "/query/{query}/{scope}" => ["page" => PageId::OPENSEARCH_QUERY, "search" => 1],
            "/query/{query}" => ["page" => PageId::OPENSEARCH_QUERY, "search" => 1],
            "/search/{query}/{scope}" => PageId::OPENSEARCH_QUERY,
            "/search/{query}" => PageId::OPENSEARCH_QUERY,
            "/search" => PageId::OPENSEARCH,
            "/recent" => PageId::ALL_RECENT_BOOKS,
            "/tags/{id:\d+}/{title}" => PageId::TAG_DETAIL,
            "/tags/{id:\d+}" => PageId::TAG_DETAIL,
            "/tags" => PageId::ALL_TAGS,
            "/custom/{custom:\d+}/{id}" => PageId::CUSTOM_DETAIL,
            "/custom/{custom:\d+}" => PageId::ALL_CUSTOMS,
            "/about" => PageId::ABOUT,
            "/languages/{id:\d+}/{title}" => PageId::LANGUAGE_DETAIL,
            "/languages/{id:\d+}" => PageId::LANGUAGE_DETAIL,
            "/languages" => PageId::ALL_LANGUAGES,
            "/customize" => PageId::CUSTOMIZE,
            "/publishers/{id:\d+}/{title}" => PageId::PUBLISHER_DETAIL,
            "/publishers/{id:\d+}" => PageId::PUBLISHER_DETAIL,
            "/publishers" => PageId::ALL_PUBLISHERS,
            "/ratings/{id:\d+}/{title}" => PageId::RATING_DETAIL,
            "/ratings/{id:\d+}" => PageId::RATING_DETAIL,
            "/ratings" => PageId::ALL_RATINGS,
            "/identifiers/{id:\w+}/{title}" => PageId::IDENTIFIER_DETAIL,
            "/identifiers/{id:\w+}" => PageId::IDENTIFIER_DETAIL,
            "/identifiers" => PageId::ALL_IDENTIFIERS,
            "/libraries" => PageId::ALL_LIBRARIES,
        ];
    }

    public function handle($request)
    {
        // ...
    }
}
