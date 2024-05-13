<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;

class Page
{
    public const PAGE_ID = "cops:catalog";

    /** @var string */
    public $title;
    public string $subtitle = "";
    public string $authorName = "";
    public string $authorUri = "";
    public string $authorEmail = "";
    public string $parentTitle = "";
    public string $currentUri = "";
    public string $parentUri = "";
    /** @var ?string */
    public $idPage;
    /** @var string|int|null */
    public $idGet;
    /** @var ?string */
    public $query;
    public string $favicon;
    /** @var int */
    public $n;
    /** @var ?Book */
    public $book;
    /** @var int */
    public $totalNumber = -1;
    /** @var ?string */
    public $sorted = "sort";
    /** @var array<string, mixed> */
    public $filterParams = [];
    /** @var array<string, mixed>|false */
    public $hierarchy = false;
    /** @var array<string, mixed>|false */
    public $extra = false;

    /** @var Entry[] */
    public $entryArray = [];

    /** @var Request */
    protected $request = null;
    protected string $className = Base::class;
    /** @var int */
    protected $numberPerPage = -1;
    /** @var array<string> */
    protected $ignoredCategories = [];
    /** @var ?int */
    protected $databaseId = null;
    protected string $handler = '';

    /**
     * Summary of getPage
     * @param string|int|null $pageId
     * @param ?Request $request
     * @return Page|PageAbout|PageAllAuthors|PageAllAuthorsLetter|PageAllBooks|PageAllBooksLetter|PageAllBooksYear|PageAllCustoms|PageAllIdentifiers|PageAllLanguages|PageAllPublishers|PageAllRating|PageAllSeries|PageAllTags|PageAuthorDetail|PageBookDetail|PageCustomDetail|PageCustomize|PageIdentifierDetail|PageLanguageDetail|PagePublisherDetail|PageQueryResult|PageRatingDetail|PageRecentBooks|PageSerieDetail|PageTagDetail
     */
    public static function getPage($pageId, $request)
    {
        return PageId::getPage($pageId, $request);
    }

    /**
     * Summary of __construct
     * @param ?Request $request
     */
    public function __construct($request = null)
    {
        $this->setRequest($request);
        $this->favicon = Config::get('icon');
        $this->authorName = Config::get('author_name') ?: 'Sébastien Lucas';
        $this->authorUri = Config::get('author_uri') ?: 'http://blog.slucas.fr';
        $this->authorEmail = Config::get('author_email') ?: 'sebastien@slucas.fr';
    }

    /**
     * Summary of setRequest
     * @param ?Request $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request ?? new Request();
        // this could be string for first letter, identifier or custom columns - override there
        $this->idGet = $this->request->getId();
        $this->query = $this->request->get('query');
        $this->n = $this->request->get('n', 1, '/^\d+$/');  // use default here
        $this->numberPerPage = $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->databaseId = $this->request->database();
        $this->handler = $this->request->getHandler();
    }

    /**
     * Summary of getNumberPerPage
     * @return int
     */
    public function getNumberPerPage()
    {
        return $this->numberPerPage;
    }

    /**
     * Summary of getIgnoredCategories
     * @return array<string>
     */
    public function getIgnoredCategories()
    {
        return $this->ignoredCategories;
    }

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = static::PAGE_ID;
        $this->title = Config::get('title_default');
        $this->subtitle = Config::get('subtitle_default');
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $this->getExtra();
    }

    /**
     * Summary of getExtra
     * @return void
     */
    public function getExtra()
    {
        $this->extra = false;
    }

    /**
     * Summary of isPaginated
     * @return bool
     */
    public function isPaginated()
    {
        return ($this->getNumberPerPage() != -1 &&
                $this->totalNumber != -1 &&
                $this->totalNumber > $this->getNumberPerPage());
    }

    /**
     * Summary of getCleanQuery
     * @deprecated 2.7.0 use $this->request->getCleanParams() instead
     * @return string
     */
    public function getCleanQuery()
    {
        $query = preg_replace("/(^|\&)n=.*?$/", "", preg_replace("/(^|\&)_=\d+/", "", $this->request->query()));
        if (!empty(Config::get('use_route_urls'))) {
            $path = $this->request->path();
            if (!empty($query)) {
                return $path . '?' . $query;
            }
            // Route::query() expects a query string as input
            return $path . '?';
        }
        return $query;
    }

    /**
     * Summary of getFirstLink
     * @return ?LinkNavigation
     */
    public function getFirstLink()
    {
        if ($this->n > 1) {
            $params = $this->request->getCleanParams();
            return new LinkNavigation(Route::link($this->handler, null, $params), "first", localize("paging.first.alternate"));
        }
        return null;
    }

    /**
     * Summary of getLastLink
     * @return ?LinkNavigation
     */
    public function getLastLink()
    {
        if ($this->n < $this->getMaxPage()) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->getMaxPage());
            return new LinkNavigation(Route::link($this->handler, null, $params), "last", localize("paging.last.alternate"));
        }
        return null;
    }

    /**
     * Summary of getNextLink
     * @return ?LinkNavigation
     */
    public function getNextLink()
    {
        if ($this->n < $this->getMaxPage()) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->n + 1);
            return new LinkNavigation(Route::link($this->handler, null, $params), "next", localize("paging.next.alternate"));
        }
        return null;
    }

    /**
     * Summary of getPrevLink
     * @return ?LinkNavigation
     */
    public function getPrevLink()
    {
        if ($this->n > 1) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->n - 1);
            return new LinkNavigation(Route::link($this->handler, null, $params), "previous", localize("paging.previous.alternate"));
        }
        return null;
    }

    /**
     * Summary of getMaxPage
     * @return float
     */
    public function getMaxPage()
    {
        return ceil($this->totalNumber / $this->numberPerPage);
    }

    /**
     * Summary of getSortOptions
     * @return array<string, string>
     */
    public function getSortOptions()
    {
        if ($this->request->isFeed()) {
            $sortLinks = Config::get('opds_sort_links');
        } else {
            $sortLinks = Config::get('html_sort_links');
        }
        $allowed = array_flip($sortLinks);
        $sortOptions = [
            //'title' => localize("bookword.title"),
            'title' => localize("sort.titles"),
            'author' => localize("authors.title"),
            'pubdate' => localize("pubdate.title"),
            'rating' => localize("ratings.title"),
            'timestamp' => localize("recent.title"),
            //'series' => localize("series.title"),
            //'language' => localize("languages.title"),
            //'publisher' => localize("publishers.title"),
        ];
        return array_intersect_key($sortOptions, $allowed);
    }

    /**
     * Summary of getFilters
     * @param Author|Language|Publisher|Rating|Serie|Tag|Identifier|CustomColumn $instance
     * @return void
     */
    public function getFilters($instance)
    {
        if ($this->request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
            $instance->setFilterLimit(Config::get('opds_filter_limit'));
        } else {
            $filterLinks = Config::get('html_filter_links');
            $instance->setFilterLimit(Config::get('html_filter_limit'));
        }
        $this->entryArray = [];
        if (empty($filterLinks)) {
            return;
        }
        // we use g[a]=2 to indicate we want to paginate in facetgroup Authors
        $paging = $this->request->get('g');
        if (!is_array($paging)) {
            $paging = [];
        }
        // if we want to filter by virtual library etc.
        $libraryId = $this->request->getVirtualLibrary();
        if (!empty($libraryId)) {
            $instance->setFilterParams([VirtualLibrary::URL_PARAM => $libraryId]);
        }
        if (!($instance instanceof Author) && in_array('author', $filterLinks)) {
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
            $paging['a'] ??= 1;
            $this->entryArray = array_merge($this->entryArray, $instance->getAuthors($paging['a']));
        }
        if (!($instance instanceof Language) && in_array('language', $filterLinks)) {
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
            $paging['l'] ??= 1;
            $this->entryArray = array_merge($this->entryArray, $instance->getLanguages($paging['l']));
        }
        if (!($instance instanceof Publisher) && in_array('publisher', $filterLinks)) {
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
            $paging['p'] ??= 1;
            $this->entryArray = array_merge($this->entryArray, $instance->getPublishers($paging['p']));
        }
        if (!($instance instanceof Rating) && in_array('rating', $filterLinks)) {
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
            $paging['r'] ??= 1;
            $this->entryArray = array_merge($this->entryArray, $instance->getRatings($paging['r']));
        }
        if (!($instance instanceof Serie) && in_array('series', $filterLinks)) {
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
            $paging['s'] ??= 1;
            $this->entryArray = array_merge($this->entryArray, $instance->getSeries($paging['s']));
        }
        if (in_array('tag', $filterLinks)) {
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
            $paging['t'] ??= 1;
            // special case if we want to find other tags applied to books where this tag applies
            if ($instance instanceof Tag) {
                $instance->limitSelf = false;
            }
            $this->entryArray = array_merge($this->entryArray, $instance->getTags($paging['t']));
        }
        if (in_array('identifier', $filterLinks)) {
            array_push($this->entryArray, new Entry(
                localize("identifiers.title"),
                "",
                "TODO",
                "text",
                [],
                $this->getDatabaseId(),
                "",
                ""
            ));
            $paging['i'] ??= 1;
            // special case if we want to find other identifiers applied to books where this identifier applies
            if ($instance instanceof Identifier) {
                $instance->limitSelf = false;
            }
            $this->entryArray = array_merge($this->entryArray, $instance->getIdentifiers($paging['i']));
        }
        /**
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        if (!($instance instanceof CustomColumn) && in_array('custom', $filterLinks)) {
            $columns = CustomColumnType::getAllCustomColumns($this->getDatabaseId());
            $paging['c'] ??= [];
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
                $paging['c'][$column['id']] ??= 1;
                $entries = $instance->getCustomValues($customType);
            }
        }
         */
    }

    /**
     * Summary of containsBook
     * @return bool
     */
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
