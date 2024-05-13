<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
            "/authors/letter/{id}" => PageId::AUTHORS_FIRST_LETTER,
            "/authors/letter" => ["page" => PageId::ALL_AUTHORS, "letter" => 1],
            "/authors/{id}/{title}" => PageId::AUTHOR_DETAIL,
            "/authors/{id}" => PageId::AUTHOR_DETAIL,
            "/authors" => PageId::ALL_AUTHORS,
            "/books/letter/{id}" => PageId::ALL_BOOKS_LETTER,
            "/books/letter" => ["page" => PageId::ALL_BOOKS, "letter" => 1],
            "/books/year/{id}" => PageId::ALL_BOOKS_YEAR,
            "/books/year" => ["page" => PageId::ALL_BOOKS, "year" => 1],
            "/books/{id}/{author}/{title}" => PageId::BOOK_DETAIL,
            "/books/{id}" => PageId::BOOK_DETAIL,
            "/books" => PageId::ALL_BOOKS,
            "/series/{id}/{title}" => PageId::SERIE_DETAIL,
            "/series/{id}" => PageId::SERIE_DETAIL,
            "/series" => PageId::ALL_SERIES,
            "/search/{query}/{scope}" => PageId::OPENSEARCH_QUERY,
            "/search/{query}" => PageId::OPENSEARCH_QUERY,
            "/search" => PageId::OPENSEARCH,
            "/recent" => PageId::ALL_RECENT_BOOKS,
            "/tags/{id}/{title}" => PageId::TAG_DETAIL,
            "/tags/{id}" => PageId::TAG_DETAIL,
            "/tags" => PageId::ALL_TAGS,
            "/custom/{custom}/{id}" => PageId::CUSTOM_DETAIL,
            "/custom/{custom}" => PageId::ALL_CUSTOMS,
            "/about" => PageId::ABOUT,
            "/languages/{id}/{title}" => PageId::LANGUAGE_DETAIL,
            "/languages/{id}" => PageId::LANGUAGE_DETAIL,
            "/languages" => PageId::ALL_LANGUAGES,
            "/customize" => PageId::CUSTOMIZE,
            "/publishers/{id}/{title}" => PageId::PUBLISHER_DETAIL,
            "/publishers/{id}" => PageId::PUBLISHER_DETAIL,
            "/publishers" => PageId::ALL_PUBLISHERS,
            "/ratings/{id}/{title}" => PageId::RATING_DETAIL,
            "/ratings/{id}" => PageId::RATING_DETAIL,
            "/ratings" => PageId::ALL_RATINGS,
            "/identifiers/{id}/{title}" => PageId::IDENTIFIER_DETAIL,
            "/identifiers/{id}" => PageId::IDENTIFIER_DETAIL,
            "/identifiers" => PageId::ALL_IDENTIFIERS,
            "/libraries" => PageId::ALL_LIBRARIES,
        ];
    }

    public function handle($request)
    {
        // ...
    }
}
