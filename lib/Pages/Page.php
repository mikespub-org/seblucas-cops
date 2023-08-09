<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;

class Page
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
    public const ALL_BOOKS_YEAR = "50";
    public const FILTER = "99";
    public const ERROR = "100";
    public const PAGE_ID = "cops:catalog";
    public const ABOUT_ID = "cops:about";
    public const FILTER_ID = "cops:filter";
    public const ERROR_ID = "cops:error";
    public const ALL_AUTHORS_ID = "cops:authors";
    public const ALL_BOOKS_UUID = 'urn:uuid';
    public const ALL_BOOKS_ID = 'cops:books';
    public const ALL_RECENT_BOOKS_ID = 'cops:recentbooks';
    public const ALL_CUSTOMS_ID       = "cops:custom";
    public const ALL_LANGUAGES_ID = "cops:languages";
    public const ALL_PUBLISHERS_ID = "cops:publishers";
    public const ALL_RATING_ID = "cops:rating";
    public const ALL_SERIES_ID = "cops:series";
    public const ALL_TAGS_ID = "cops:tags";

    public $title;
    public $subtitle = "";
    public $authorName = "";
    public $authorUri = "";
    public $authorEmail = "";
    public $parentTitle = "";
    public $currentUri = "";
    public $parentUri = "";
    public $idPage;
    public $idGet;
    public $query;
    public $favicon;
    public $n;
    public $book;
    public $totalNumber = -1;
    public $sorted = "sort";
    public $filterUri = "";

    /** @var Entry[] */
    public $entryArray = [];

    /** @var Request */
    protected $request = null;
    protected $numberPerPage = -1;
    protected $ignoredCategories = [];
    protected $databaseId = null;

    public static function getPageForRequest($request)
    {
        $page = $request->get("page", Page::INDEX);
        $query = $request->get("query");
        $id = $request->get("id");
        $n = $request->get("n", "1");
        return self::getPage($page, $id, $query, $n);
    }

    public static function getPage($pageId, $id, $query, $n, $request = null)
    {
        switch ($pageId) {
            case Page::ALL_AUTHORS :
                return new PageAllAuthors($id, $query, $n, $request);
            case Page::AUTHORS_FIRST_LETTER :
                return new PageAllAuthorsLetter($id, $query, $n, $request);
            case Page::AUTHOR_DETAIL :
                return new PageAuthorDetail($id, $query, $n, $request);
            case Page::ALL_TAGS :
                return new PageAllTags($id, $query, $n, $request);
            case Page::TAG_DETAIL :
                return new PageTagDetail($id, $query, $n, $request);
            case Page::ALL_LANGUAGES :
                return new PageAllLanguages($id, $query, $n, $request);
            case Page::LANGUAGE_DETAIL :
                return new PageLanguageDetail($id, $query, $n, $request);
            case Page::ALL_CUSTOMS :
                return new PageAllCustoms($id, $query, $n, $request);
            case Page::CUSTOM_DETAIL :
                return new PageCustomDetail($id, $query, $n, $request);
            case Page::ALL_RATINGS :
                return new PageAllRating($id, $query, $n, $request);
            case Page::RATING_DETAIL :
                return new PageRatingDetail($id, $query, $n, $request);
            case Page::ALL_SERIES :
                return new PageAllSeries($id, $query, $n, $request);
            case Page::ALL_BOOKS :
                return new PageAllBooks($id, $query, $n, $request);
            case Page::ALL_BOOKS_LETTER:
                return new PageAllBooksLetter($id, $query, $n, $request);
            case Page::ALL_BOOKS_YEAR:
                return new PageAllBooksYear($id, $query, $n, $request);
            case Page::ALL_RECENT_BOOKS :
                return new PageRecentBooks($id, $query, $n, $request);
            case Page::SERIE_DETAIL :
                return new PageSerieDetail($id, $query, $n, $request);
            case Page::OPENSEARCH_QUERY :
                return new PageQueryResult($id, $query, $n, $request);
            case Page::BOOK_DETAIL :
                return new PageBookDetail($id, $query, $n, $request);
            case Page::ALL_PUBLISHERS:
                return new PageAllPublishers($id, $query, $n, $request);
            case Page::PUBLISHER_DETAIL :
                return new PagePublisherDetail($id, $query, $n, $request);
            case Page::ABOUT :
                return new PageAbout($id, $query, $n, $request);
            case Page::CUSTOMIZE :
                return new PageCustomize($id, $query, $n, $request);
            default:
                return new Page($id, $query, $n, $request);
        }
    }

    public function __construct($pid, $pquery, $pn, $request = null)
    {
        global $config;

        $this->idGet = $pid;
        $this->query = $pquery;
        $this->n = $pn;
        $this->setRequest($request ?? new Request());
        $this->favicon = $config['cops_icon'];
        $this->authorName = $config['cops_author_name'] ?: 'Sébastien Lucas';
        $this->authorUri = $config['cops_author_uri'] ?: 'http://blog.slucas.fr';
        $this->authorEmail = $config['cops_author_email'] ?: 'sebastien@slucas.fr';
    }

    public function setRequest($request)
    {
        $this->request = $request;
        $this->numberPerPage = $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->databaseId = $this->request->get('db');
    }

    public function getNumberPerPage()
    {
        return $this->numberPerPage;
    }

    public function getIgnoredCategories()
    {
        return $this->ignoredCategories;
    }

    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    public function InitializeContent()
    {
        global $config;
        $this->getEntries();
        $this->idPage = self::PAGE_ID;
        $this->title = $config['cops_title_default'];
        $this->subtitle = $config['cops_subtitle_default'];
    }

    public function getEntries()
    {
        if (Database::noDatabaseSelected($this->databaseId)) {
            $this->getDatabaseEntries();
        } else {
            $this->getTopCountEntries();
        }
    }

    public function getDatabaseEntries()
    {
        $i = 0;
        foreach (Database::getDbNameList() as $key) {
            $booklist = new BookList($this->request, $i);
            $nBooks = $booklist->getBookCount();
            array_push($this->entryArray, new Entry(
                $key,
                "cops:{$i}:catalog",
                str_format(localize("bookword", $nBooks), $nBooks),
                "text",
                [ new LinkNavigation("?db={$i}")],
                null,
                "",
                $nBooks
            ));
            $i++;
            Database::clearDb();
        }
    }

    public function getTopCountEntries()
    {
        global $config;
        if (!in_array(PageQueryResult::SCOPE_AUTHOR, $this->ignoredCategories)) {
            array_push($this->entryArray, Author::getCount($this->databaseId));
        }
        if (!in_array(PageQueryResult::SCOPE_SERIES, $this->ignoredCategories)) {
            $series = Serie::getCount($this->databaseId);
            if (!is_null($series)) {
                array_push($this->entryArray, $series);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_PUBLISHER, $this->ignoredCategories)) {
            $publisher = Publisher::getCount($this->databaseId);
            if (!is_null($publisher)) {
                array_push($this->entryArray, $publisher);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_TAG, $this->ignoredCategories)) {
            $tags = Tag::getCount($this->databaseId);
            if (!is_null($tags)) {
                array_push($this->entryArray, $tags);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_RATING, $this->ignoredCategories)) {
            $rating = Rating::getCount($this->databaseId);
            if (!is_null($rating)) {
                array_push($this->entryArray, $rating);
            }
        }
        if (!in_array("language", $this->ignoredCategories)) {
            $languages = Language::getCount($this->databaseId);
            if (!is_null($languages)) {
                array_push($this->entryArray, $languages);
            }
        }
        $config['cops_calibre_custom_column'] = CustomColumnType::checkCustomColumnList($config['cops_calibre_custom_column']);
        foreach ($config['cops_calibre_custom_column'] as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDatabaseId());
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
                array_push($this->entryArray, $customColumn->getCount());
            }
        }
        $booklist = new BookList($this->request);
        $this->entryArray = array_merge($this->entryArray, $booklist->getCount());

        if (Database::isMultipleDatabaseEnabled()) {
            $this->title =  Database::getDbName($this->getDatabaseId());
        }
    }

    public function isPaginated()
    {
        return ($this->getNumberPerPage() != -1 &&
                $this->totalNumber != -1 &&
                $this->totalNumber > $this->getNumberPerPage());
    }

    public function getCleanQuery()
    {
        return preg_replace("/\&n=.*?$/", "", preg_replace("/\&_=\d+/", "", $this->request->query()));
    }

    public function getNextLink()
    {
        $currentUrl = "?" . $this->getCleanQuery();
        if (($this->n) * $this->getNumberPerPage() < $this->totalNumber) {
            return new LinkNavigation($currentUrl . "&n=" . ($this->n + 1), "next", localize("paging.next.alternate"));
        }
        return null;
    }

    public function getPrevLink()
    {
        $currentUrl = "?" . $this->getCleanQuery();
        if ($this->n > 1) {
            return new LinkNavigation($currentUrl . "&n=" . ($this->n - 1), "previous", localize("paging.previous.alternate"));
        }
        return null;
    }

    public function getMaxPage()
    {
        return ceil($this->totalNumber / $this->numberPerPage);
    }

    public function getSortOptions()
    {
        return [
            'title' => localize("bookword.title"),
            'timestamp' => localize("recent.title"),
            'author' => localize("authors.title"),
            'pubdate' => localize("pubdate.title"),
            'rating' => localize("ratings.title"),
            //'series' => localize("series.title"),
            //'language' => localize("languages.title"),
            //'publisher' => localize("publishers.title"),
        ];
    }

    public function getFilters($instance)
    {
        $this->entryArray = [];
        if (!($instance instanceof Author)) {
            array_push($this->entryArray, new Entry(
                localize("authors.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getAuthors());
        }
        if (!($instance instanceof Language)) {
            array_push($this->entryArray, new Entry(
                localize("languages.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getLanguages());
        }
        if (!($instance instanceof Publisher)) {
            array_push($this->entryArray, new Entry(
                localize("publishers.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getPublishers());
        }
        if (!($instance instanceof Rating)) {
            array_push($this->entryArray, new Entry(
                localize("ratings.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getRatings());
        }
        if (!($instance instanceof Serie)) {
            array_push($this->entryArray, new Entry(
                localize("series.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getSeries());
        }
        if (!($instance instanceof Tag)) {
            array_push($this->entryArray, new Entry(
                localize("tags.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $this->entryArray = array_merge($this->entryArray, $instance->getTags());
        }
        /**
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        if (!($instance instanceof CustomColumn)) {
            $columns = CustomColumnType::getAllCustomColumns($this->getDatabaseId());
            foreach ($columns as $label => $column) {
                $customType = CustomColumnType::createByCustomID($column["id"], $this->getDatabaseId());
                array_push($this->entryArray, new Entry(
                    $customType->getTitle(),
                    "",
                    "TODO",
                    "text",
                    [],
                    $this->getDatabaseId(),
                    "",
                    ""
                ));
                $entries = $instance->getCustomValues($customType);
            }
        }
         */
    }

    public function containsBook()
    {
        if (count($this->entryArray) == 0) {
            return false;
        }
        if (get_class($this->entryArray [0]) == EntryBook::class) {
            return true;
        }
        return false;
    }
}
