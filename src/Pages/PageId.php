<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class PageId
{
    public const INDEX = "index";
    public const ALL_AUTHORS = "authors";
    public const AUTHORS_FIRST_LETTER = "authors_letter";
    public const AUTHOR_DETAIL = "author";
    public const ALL_BOOKS = "books";
    public const ALL_BOOKS_LETTER = "books_letter";
    public const ALL_SERIES = "series";
    public const SERIE_DETAIL = "serie";
    public const OPENSEARCH = "opensearch";
    public const SEARCH = "search";
    public const OPENSEARCH_QUERY = "query";
    public const ALL_RECENT_BOOKS = "recent";
    public const ALL_TAGS = "tags";
    public const TAG_DETAIL = "tag";
    public const BOOK_DETAIL = "book";
    public const ALL_CUSTOMS = "customtype";
    public const CUSTOM_DETAIL = "custom";
    public const ABOUT = "about";
    public const ALL_LANGUAGES = "languages";
    public const LANGUAGE_DETAIL = "language";
    public const CUSTOMIZE = "customize";
    public const ALL_PUBLISHERS = "publishers";
    public const PUBLISHER_DETAIL = "publisher";
    public const ALL_RATINGS = "ratings";
    public const RATING_DETAIL = "rating";
    public const ALL_IDENTIFIERS = "identifiers";
    public const IDENTIFIER_DETAIL = "identifier";
    public const ALL_LIBRARIES = "libraries";
    public const LIBRARY_DETAIL = "library";
    public const ALL_NOTES = "notes";
    public const ALL_NOTES_TYPE = "notes_type";
    public const NOTE_DETAIL = "note";
    public const ALL_PREFERENCES = "preferences";
    public const PREFERENCE_DETAIL = "preference";
    public const ALL_BOOKS_YEAR = "books_year";
    public const ALL_FORMATS = "formats";
    public const FORMAT_DETAIL = "format";
    public const ALL_ANNOTATIONS = "annotations";
    public const ANNOTATIONS_BOOK = "annotations_book";
    public const ANNOTATION_DETAIL = "annotation";
    public const REST_API = "restapi";
    public const FILTER = "filter";
    public const ERROR = "error";
    public const INDEX_ID = "cops:catalog";
    public const ABOUT_ID = "cops:about";
    public const FILTER_ID = "cops:filter";
    public const ERROR_ID = "cops:error";
    public const SEARCH_ID = "cops:search";
    public const ALL_AUTHORS_ID = "cops:authors";
    public const ALL_BASES_ID = "cops:bases";
    public const ALL_BOOKS_UUID = 'urn:uuid';
    public const ALL_BOOKS_ID = 'cops:books';
    public const ALL_RECENT_BOOKS_ID = 'cops:recentbooks';
    public const ALL_CUSTOMS_ID       = "cops:custom";
    public const ALL_LANGUAGES_ID = "cops:languages";
    public const ALL_PUBLISHERS_ID = "cops:publishers";
    public const ALL_RATING_ID = "cops:rating";
    public const ALL_SERIES_ID = "cops:series";
    public const ALL_TAGS_ID = "cops:tags";
    public const ALL_IDENTIFIERS_ID = "cops:identifiers";
    public const ALL_LIBRARIES_ID = "cops:libraries";
    public const ALL_NOTES_ID = "cops:notes";
    public const ALL_PREFERENCES_ID = "cops:preferences";
    public const ALL_FORMATS_ID = "cops:formats";
    public const ALL_ANNOTATIONS_ID = "cops:annotations";
    // @todo move elsewhere
    public const ROUTE_INDEX = "page-index";
    public const ROUTE_ABOUT = "page-about";
    public const ROUTE_CUSTOMIZE = "page-customize";

    /**
     * Summary of getPage
     * @param string|int|null $pageId
     * @param ?Request $request
     * @param ?Base $instance @todo investigate potential use as alternative to getEntry()
     * @return Page|PageAbout|PageAllAuthors|PageAllAuthorsLetter|PageAllBooks|PageAllBooksLetter|PageAllBooksYear|PageAllCustoms|PageAllLanguages|PageAllPublishers|PageAllRating|PageAllSeries|PageAllTags|PageAuthorDetail|PageBookDetail|PageCustomDetail|PageCustomize|PageLanguageDetail|PagePublisherDetail|PageQueryResult|PageRatingDetail|PageRecentBooks|PageSerieDetail|PageTagDetail
     */
    public static function getPage($pageId, $request, $instance = null)
    {
        $pageId ??= PageId::getHomePage();

        // @see https://www.php.net/manual/en/control-structures.match.php
        // Unlike switch, the comparison is an identity check (===) rather than a weak equality check (==)
        return match ((string) $pageId) {
            '' => new PageIndex($request),
            PageId::INDEX => new PageIndex($request),
            PageId::ALL_AUTHORS => new PageAllAuthors($request),
            PageId::AUTHORS_FIRST_LETTER => new PageAllAuthorsLetter($request),
            PageId::AUTHOR_DETAIL => new PageAuthorDetail($request, $instance),
            PageId::ALL_TAGS => new PageAllTags($request),
            PageId::TAG_DETAIL => new PageTagDetail($request, $instance),
            PageId::ALL_LANGUAGES => new PageAllLanguages($request),
            PageId::LANGUAGE_DETAIL => new PageLanguageDetail($request, $instance),
            PageId::ALL_CUSTOMS => new PageAllCustoms($request),
            PageId::CUSTOM_DETAIL => new PageCustomDetail($request, $instance),
            PageId::ALL_RATINGS => new PageAllRating($request),
            PageId::RATING_DETAIL => new PageRatingDetail($request, $instance),
            PageId::ALL_SERIES => new PageAllSeries($request),
            PageId::ALL_BOOKS => new PageAllBooks($request),
            PageId::ALL_BOOKS_LETTER => new PageAllBooksLetter($request),
            PageId::ALL_BOOKS_YEAR => new PageAllBooksYear($request),
            PageId::ALL_RECENT_BOOKS => new PageRecentBooks($request),
            PageId::SERIE_DETAIL => new PageSerieDetail($request, $instance),
            PageId::OPENSEARCH_QUERY => new PageQueryResult($request),
            // support ?query=... URL param by default for opensearch
            PageId::OPENSEARCH => !empty($request->get('query')) ? new PageQueryResult($request) : new PageIndex($request),
            PageId::BOOK_DETAIL => new PageBookDetail($request),
            PageId::ALL_PUBLISHERS => new PageAllPublishers($request),
            PageId::PUBLISHER_DETAIL => new PagePublisherDetail($request, $instance),
            PageId::ALL_IDENTIFIERS => new PageAllIdentifiers($request),
            PageId::IDENTIFIER_DETAIL => new PageIdentifierDetail($request, $instance),
            PageId::ALL_FORMATS => new PageAllFormats($request),
            PageId::FORMAT_DETAIL => new PageFormatDetail($request, $instance),
            PageId::ALL_LIBRARIES => new PageAllVirtualLibraries($request),
            PageId::ABOUT => new PageAbout($request),
            PageId::CUSTOMIZE => new PageCustomize($request),
            PageId::FILTER => new PageFilter($request),
            // @todo return error for unknown page
            default => new PageIndex($request),
        };
    }

    /**
     * Summary of getHomePage
     * @return string|int
     */
    public static function getHomePage()
    {
        // Use the configured home page if needed
        $page = PageId::INDEX;
        if (!empty(Config::get('home_page')) && defined('SebLucas\Cops\Pages\PageId::' . Config::get('home_page'))) {
            $page = constant('SebLucas\Cops\Pages\PageId::' . Config::get('home_page'));
        }
        return $page;
    }
}
