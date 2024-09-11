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
     * @param ?Base $instance @todo investigate potential use as alternative to getEntry()
     */
    public function __construct($request = null, $instance = null)
    {
        $this->setRequest($request);
        $this->favicon = Config::get('icon');
        $this->authorName = Config::get('author_name') ?: 'Sébastien Lucas';
        $this->authorUri = Config::get('author_uri') ?: 'http://blog.slucas.fr';
        $this->authorEmail = Config::get('author_email') ?: 'sebastien@slucas.fr';

        // move to constructor as this is always called directly after PageId::getPage()
        if (empty($instance)) {
            $this->initializeContent();
        } else {
            // do not call getEntries() here
            $this->setInstance($instance);
        }
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
        $this->n = $this->request->get('n', 1, '/^\d+$/');  // use default here
        $this->numberPerPage = $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->databaseId = $this->request->database();
        $this->handler = $this->request->getHandler();
    }

    /**
     * Summary of setInstance
     * @param ?Base $instance
     * @return void
     */
    public function setInstance($instance)
    {
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->getTitle();
        // this is the unfiltered uri here, used in JsonRenderer - @todo do we want to use request->urlParams?
        $this->currentUri = $instance->getUri();
        $this->parentTitle = $instance->getParentTitle();
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = $instance->getParentUri($filterParams);
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
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
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
        // @todo get rid of extraParams in JsonRenderer and OpdsRenderer as filters should be included in navlink now
        $params = $instance->getExtraParams();
        $params['db'] = $this->getDatabaseId();
        $filtersTitle = localize("filters.title");
        if (!($instance instanceof Author) && in_array('author', $filterLinks)) {
            $title = localize(phrase: "authors.title");
            $href = Route::link($this->handler, Author::PAGE_ALL, $params);
            $relation = "authors";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['a'] ??= 1;
            $this->addEntries($instance->getAuthors($paging['a']));
        }
        if (!($instance instanceof Language) && in_array('language', $filterLinks)) {
            $title = localize("languages.title");
            $href = Route::link($this->handler, Language::PAGE_ALL, $params);
            $relation = "languages";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['l'] ??= 1;
            $this->addEntries($instance->getLanguages($paging['l']));
        }
        if (!($instance instanceof Publisher) && in_array('publisher', $filterLinks)) {
            $title = localize("publishers.title");
            $href = Route::link($this->handler, Publisher::PAGE_ALL, $params);
            $relation = "publishers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['p'] ??= 1;
            $this->addEntries($instance->getPublishers($paging['p']));
        }
        if (!($instance instanceof Rating) && in_array('rating', $filterLinks)) {
            $title = localize("ratings.title");
            $href = Route::link($this->handler, Rating::PAGE_ALL, $params);
            $relation = "ratings";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['r'] ??= 1;
            $this->addEntries($instance->getRatings($paging['r']));
        }
        if (!($instance instanceof Serie) && in_array('series', $filterLinks)) {
            $title = localize("series.title");
            $href = Route::link($this->handler, Serie::PAGE_ALL, $params);
            $relation = "series";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['s'] ??= 1;
            $this->addEntries($instance->getSeries($paging['s']));
        }
        if (in_array('tag', $filterLinks)) {
            $title = localize("tags.title");
            $href = Route::link($this->handler, Tag::PAGE_ALL, $params);
            $relation = "tags";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['t'] ??= 1;
            // special case if we want to find other tags applied to books where this tag applies
            if ($instance instanceof Tag) {
                $instance->limitSelf = false;
            }
            $this->addEntries($instance->getTags($paging['t']));
        }
        if (in_array('identifier', $filterLinks)) {
            $title = localize("identifiers.title");
            $href = Route::link($this->handler, Identifier::PAGE_ALL, $params);
            $relation = "identifiers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging['i'] ??= 1;
            // special case if we want to find other identifiers applied to books where this identifier applies
            if ($instance instanceof Identifier) {
                $instance->limitSelf = false;
            }
            $this->addEntries($instance->getIdentifiers($paging['i']));
        }
        /**
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        if (!($instance instanceof CustomColumn) && in_array('custom', $filterLinks)) {
            $columns = CustomColumnType::getAllCustomColumns($this->getDatabaseId());
            $paging['c'] ??= [];
            foreach ($columns as $label => $column) {
                $customType = CustomColumnType::createByCustomID($column["id"], $this->getDatabaseId());
                $title = $customType->getTitle();
                $href = $customType->getParentUri();
                $relation = $customType->getTitle();
                $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
                $paging['c'][$column['id']] ??= 1;
                $entries = $instance->getCustomValues($customType);
                // @todo
            }
        }
         */
    }

    /**
     * Summary of addEntries
     * @param array<Entry> $entries
     * @return void
     */
    public function addEntries($entries)
    {
        $this->entryArray = array_merge($this->entryArray, $entries);
    }

    /**
     * Summary of addHeaderEntry
     * @param string $title
     * @param string $content
     * @param ?string $href
     * @param ?string $relation
     * @return void
     */
    public function addHeaderEntry($title, $content, $href = null, $relation = null)
    {
        array_push($this->entryArray, $this->getHeaderEntry($title, $content, $href, $relation));
    }

    /**
     * Summary of getHeaderEntry
     * @param string $title
     * @param string $content
     * @param ?string $href
     * @param ?string $relation
     * @return Entry
     */
    public function getHeaderEntry($title, $content, $href = null, $relation = null)
    {
        if (empty($href)) {
            $linkArray = [];
        } else {
            $linkArray = [ new LinkNavigation($href, $relation) ];
        }
        return new Entry(
            $title,
            "",
            $content,
            "text",
            $linkArray,
            $this->getDatabaseId(),
            "",
            ""
        );
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
        if ($this->entryArray [0]::class == EntryBook::class) {
            return true;
        }
        return false;
    }
}
