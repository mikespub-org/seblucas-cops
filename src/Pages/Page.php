<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;

class Page
{
    use HasRouteTrait;

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
    /** @var class-string */
    protected $className = Base::class;
    /** @var int */
    protected $numberPerPage = -1;
    /** @var array<string> */
    protected $ignoredCategories = [];
    /** @var ?int */
    protected $databaseId = null;

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
        $this->setConfig();
        $this->setRequest($request);

        // move to constructor as this is always called directly after PageId::getPage()
        $this->initializeContent();
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
        $this->setHandler($this->request->getHandler());
    }

    /**
     * Summary of setConfig
     * @param ?Config $config not used for now - see RequestContext
     * @return void
     */
    public function setConfig($config = null)
    {
        $config ??= new Config();
        $this->favicon = Config::get('icon');
        $this->authorName = Config::get('author_name') ?: 'Sébastien Lucas';
        $this->authorUri = Config::get('author_uri') ?: 'https://blog.slucas.fr';
        $this->authorEmail = Config::get('author_email') ?: 'sebastien@slucas.fr';
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
        $this->idPage = PageId::INDEX_ID;
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
            $href = fn() => $this->getLink($params);
            return new LinkNavigation(
                $href,
                "first",
                localize("paging.first.alternate")
            );
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
            $href = fn() => $this->getLink($params);
            return new LinkNavigation(
                $href,
                "last",
                localize("paging.last.alternate")
            );
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
            $href = fn() => $this->getLink($params);
            return new LinkNavigation(
                $href,
                "next",
                localize("paging.next.alternate")
            );
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
            $href = fn() => $this->getLink($params);
            return new LinkNavigation(
                $href,
                "previous",
                localize("paging.previous.alternate")
            );
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
     * @param string|\Closure|null $href
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
     * @param string|\Closure|null $href
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

    /**
     * Summary of canFilter
     * @return bool
     */
    public function canFilter()
    {
        return false;
    }
}
