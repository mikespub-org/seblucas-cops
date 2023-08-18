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
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;

class Page
{
    public const PAGE_ID = "cops:catalog";

    /** @var mixed */
    public $title;
    public string $subtitle = "";
    public string $authorName = "";
    public string $authorUri = "";
    public string $authorEmail = "";
    public string $parentTitle = "";
    public string $currentUri = "";
    public string $parentUri = "";
    /** @var string|null */
    public $idPage;
    /** @var mixed */
    public $idGet;
    /** @var mixed */
    public $query;
    public string $favicon;
    /** @var mixed */
    public $n;
    /** @var Book|null */
    public $book;
    /** @var mixed */
    public $totalNumber = -1;
    /** @var mixed */
    public $sorted = "sort";
    public string $filterUri = "";
    /** @var array<string, mixed>|false */
    public $hierarchy = false;

    /** @var Entry[] */
    public $entryArray = [];

    /** @var Request */
    protected $request = null;
    protected string $className = Base::class;
    /** @var mixed */
    protected $numberPerPage = -1;
    /** @var array<string> */
    protected $ignoredCategories = [];
    /** @var mixed */
    protected $databaseId = null;

    /**
     * Summary of getPage
     * @param mixed $pageId
     * @param mixed $request
     * @return Page|PageAbout|PageAllAuthors|PageAllAuthorsLetter|PageAllBooks|PageAllBooksLetter|PageAllBooksYear|PageAllCustoms|PageAllLanguages|PageAllPublishers|PageAllRating|PageAllSeries|PageAllTags|PageAuthorDetail|PageBookDetail|PageCustomDetail|PageCustomize|PageLanguageDetail|PagePublisherDetail|PageQueryResult|PageRatingDetail|PageRecentBooks|PageSerieDetail|PageTagDetail
     */
    public static function getPage($pageId, $request)
    {
        return PageId::getPage($pageId, $request);
    }

    /**
     * Summary of __construct
     * @param Request|null $request
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
     * @param Request|null $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request ?? new Request();
        $this->idGet = $this->request->get('id');
        $this->query = $this->request->get('query');
        $this->n = $this->request->get('n', '1');  // use default here
        $this->numberPerPage = $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->databaseId = $this->request->get('db');
    }

    /**
     * Summary of getNumberPerPage
     * @return mixed
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
     * @return mixed
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
        $this->idPage = self::PAGE_ID;
        $this->title = Config::get('title_default');
        $this->subtitle = Config::get('subtitle_default');
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        if (Database::noDatabaseSelected($this->databaseId)) {
            $this->getDatabaseEntries();
        } else {
            $this->getTopCountEntries();
        }
    }

    /**
     * Summary of getDatabaseEntries
     * @return void
     */
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

    /**
     * Summary of getTopCountEntries
     * @return void
     */
    public function getTopCountEntries()
    {
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
        if (!in_array(PageQueryResult::SCOPE_LANGUAGE, $this->ignoredCategories)) {
            $languages = Language::getCount($this->databaseId);
            if (!is_null($languages)) {
                array_push($this->entryArray, $languages);
            }
        }
        $customColumnList = CustomColumnType::checkCustomColumnList(Config::get('calibre_custom_column'));
        foreach ($customColumnList as $lookup) {
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
     * @return string
     */
    public function getCleanQuery()
    {
        return preg_replace("/\&n=.*?$/", "", preg_replace("/\&_=\d+/", "", $this->request->query()));
    }

    /**
     * Summary of getNextLink
     * @return LinkNavigation|null
     */
    public function getNextLink()
    {
        $currentUrl = "?" . $this->getCleanQuery();
        if (($this->n) * $this->getNumberPerPage() < $this->totalNumber) {
            return new LinkNavigation($currentUrl . "&n=" . ($this->n + 1), "next", localize("paging.next.alternate"));
        }
        return null;
    }

    /**
     * Summary of getPrevLink
     * @return LinkNavigation|null
     */
    public function getPrevLink()
    {
        $currentUrl = "?" . $this->getCleanQuery();
        if ($this->n > 1) {
            return new LinkNavigation($currentUrl . "&n=" . ($this->n - 1), "previous", localize("paging.previous.alternate"));
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

    /**
     * Summary of getFilters
     * @param Author|Language|Publisher|Rating|Serie|Tag|CustomColumn $instance
     * @return void
     */
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
        /** @phpstan-ignore-next-line */
        if (true) {
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
            // special case if we want to find other tags applied to books where this tag applies
            if ($instance instanceof Tag) {
                $instance->limitSelf = false;
            }
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
