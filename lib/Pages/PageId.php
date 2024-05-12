<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class PageId
{
    public const INDEX = "index";
    public const ALL_AUTHORS = "1";
    public const AUTHORS_FIRST_LETTER = "2";
    public const AUTHOR_DETAIL = "3";
    public const ALL_BOOKS = "4";
    public const ALL_BOOKS_LETTER = "5";
    public const ALL_SERIES = "6";
    public const SERIE_DETAIL = "7";
    public const OPENSEARCH = "8";
    public const SEARCH = "search";
    public const OPENSEARCH_QUERY = "9";
    public const ALL_RECENT_BOOKS = "10";
    public const ALL_TAGS = "11";
    public const TAG_DETAIL = "12";
    public const BOOK_DETAIL = "13";
    public const ALL_CUSTOMS = "14";
    public const CUSTOM_DETAIL = "15";
    public const ABOUT = "16";
    public const ALL_LANGUAGES = "17";
    public const LANGUAGE_DETAIL = "18";
    public const CUSTOMIZE = "19";
    public const ALL_PUBLISHERS = "20";
    public const PUBLISHER_DETAIL = "21";
    public const ALL_RATINGS = "22";
    public const RATING_DETAIL = "23";
    public const ALL_IDENTIFIERS = "41";
    public const IDENTIFIER_DETAIL = "42";
    public const ALL_LIBRARIES = "43";
    public const LIBRARY_DETAIL = "44";
    public const ALL_NOTES = "45";
    public const ALL_NOTES_TYPE = "46";
    public const NOTE_DETAIL = "47";
    public const ALL_PREFERENCES = "48";
    public const PREFERENCE_DETAIL = "49";
    public const ALL_BOOKS_YEAR = "50";
    public const ALL_ANNOTATIONS = "61";
    public const ANNOTATIONS_BOOK = "62";
    public const ANNOTATION_DETAIL = "63";
    public const EPUBJS_ZIPFS = "95";
    public const CALIBRE_RESOURCE = "97";
    public const REST_API = "98";
    public const FILTER = "99";
    public const ERROR = "100";
    public const INDEX_ID = "cops:catalog";
    public const ABOUT_ID = "cops:about";
    public const FILTER_ID = "cops:filter";
    public const ERROR_ID = "cops:error";
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
    public const ALL_ANNOTATIONS_ID = "cops:annotations";

    /**
     * Summary of getPage
     * @param string|int|null $pageId
     * @param ?Request $request
     * @return Page|PageAbout|PageAllAuthors|PageAllAuthorsLetter|PageAllBooks|PageAllBooksLetter|PageAllBooksYear|PageAllCustoms|PageAllLanguages|PageAllPublishers|PageAllRating|PageAllSeries|PageAllTags|PageAuthorDetail|PageBookDetail|PageCustomDetail|PageCustomize|PageLanguageDetail|PagePublisherDetail|PageQueryResult|PageRatingDetail|PageRecentBooks|PageSerieDetail|PageTagDetail
     */
    public static function getPage($pageId, $request)
    {
        switch ($pageId) {
            case PageId::ALL_AUTHORS :
                return new PageAllAuthors($request);
            case PageId::AUTHORS_FIRST_LETTER :
                return new PageAllAuthorsLetter($request);
            case PageId::AUTHOR_DETAIL :
                return new PageAuthorDetail($request);
            case PageId::ALL_TAGS :
                return new PageAllTags($request);
            case PageId::TAG_DETAIL :
                return new PageTagDetail($request);
            case PageId::ALL_LANGUAGES :
                return new PageAllLanguages($request);
            case PageId::LANGUAGE_DETAIL :
                return new PageLanguageDetail($request);
            case PageId::ALL_CUSTOMS :
                return new PageAllCustoms($request);
            case PageId::CUSTOM_DETAIL :
                return new PageCustomDetail($request);
            case PageId::ALL_RATINGS :
                return new PageAllRating($request);
            case PageId::RATING_DETAIL :
                return new PageRatingDetail($request);
            case PageId::ALL_SERIES :
                return new PageAllSeries($request);
            case PageId::ALL_BOOKS :
                return new PageAllBooks($request);
            case PageId::ALL_BOOKS_LETTER:
                return new PageAllBooksLetter($request);
            case PageId::ALL_BOOKS_YEAR:
                return new PageAllBooksYear($request);
            case PageId::ALL_RECENT_BOOKS :
                return new PageRecentBooks($request);
            case PageId::SERIE_DETAIL :
                return new PageSerieDetail($request);
            case PageId::OPENSEARCH_QUERY :
                return new PageQueryResult($request);
            case PageId::BOOK_DETAIL :
                return new PageBookDetail($request);
            case PageId::ALL_PUBLISHERS:
                return new PageAllPublishers($request);
            case PageId::PUBLISHER_DETAIL :
                return new PagePublisherDetail($request);
            case PageId::ALL_IDENTIFIERS:
                return new PageAllIdentifiers($request);
            case PageId::IDENTIFIER_DETAIL :
                return new PageIdentifierDetail($request);
            case PageId::ALL_LIBRARIES:
                return new PageAllVirtualLibraries($request);
            //case PageId::LIBRARY_DETAIL :
            //    return new PageVirtualLibraryDetail($request);
            case PageId::ABOUT :
                return new PageAbout($request);
            case PageId::CUSTOMIZE :
                return new PageCustomize($request);
            default:
                return new PageIndex($request);
        }
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
