<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Pages\PageId;

class Filter
{
    public const PAGE_ID = PageId::FILTER_ID;
    public const PAGE_DETAIL = PageId::FILTER;
    public const URL_PARAMS = [
        Author::URL_PARAM => Author::class,
        Language::URL_PARAM => Language::class,
        Publisher::URL_PARAM => Publisher::class,
        Rating::URL_PARAM => Rating::class,
        Serie::URL_PARAM => Serie::class,
        Tag::URL_PARAM => Tag::class,
        Identifier::URL_PARAM => Identifier::class,
        CustomColumnType::URL_PARAM => CustomColumnType::class,
        BookList::URL_PARAM_FIRST => BookList::class,
        BookList::URL_PARAM_YEAR => BookList::class,
    ];

    protected Request $request;
    /** @var array<mixed> */
    protected $params = [];
    protected string $parentTable = "books";
    protected string $queryString = "";
    /** @var mixed */
    protected $databaseId;

    /**
     * Summary of __construct
     * @param Request|array<mixed> $request current request or urlParams array
     * @param array<mixed> $params initial query params
     * @param string $parent optional parent link table if we need to link books, e.g. books_series_link
     * @param mixed $database current database in multiple database setup
     */
    public function __construct($request, array $params = [], string $parent = "books", $database = null)
    {
        if (is_array($request)) {
            $request = Request::build($request);
        }
        $this->request = $request;
        $this->params = $params;
        $this->parentTable = $parent;
        $this->queryString = "";
        $this->databaseId = $database;

        $this->checkForFilters();
    }

    /**
     * Summary of getFilterString
     * @return string filters to append to query string
     */
    public function getFilterString()
    {
        return $this->queryString;
    }

    /**
     * Summary of getQueryParams
     * @return array<mixed> updated query params including filters
     */
    public function getQueryParams()
    {
        return $this->params;
    }

    /**
     * Summary of checkForFilters
     * @return void
     */
    public function checkForFilters()
    {
        if (empty($this->request->urlParams)) {
            return;
        }

        $tagName = $this->request->get('tag', null);
        if (!empty($tagName)) {
            $this->addTagNameFilter($tagName);
        }

        $authorId = $this->request->get(Author::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($authorId)) {
            $this->addAuthorIdFilter($authorId);
        }

        $languageId = $this->request->get(Language::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($languageId)) {
            $this->addLanguageIdFilter($languageId);
        }

        $publisherId = $this->request->get(Publisher::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($publisherId)) {
            $this->addPublisherIdFilter($publisherId);
        }

        $ratingId = $this->request->get(Rating::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($ratingId)) {
            $this->addRatingIdFilter($ratingId);
        }

        $seriesId = $this->request->get(Serie::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($seriesId)) {
            $this->addSeriesIdFilter($seriesId);
        }

        $tagId = $this->request->get(Tag::URL_PARAM, null, '/^!?\d+$/');
        if (!empty($tagId)) {
            $this->addTagIdFilter($tagId);
        }

        $identifierType = $this->request->get(Identifier::URL_PARAM, null, '/^!?\w+$/');
        if (!empty($identifierType)) {
            $this->addIdentifierTypeFilter($identifierType);
        }

        $letter = $this->request->get(BookList::URL_PARAM_FIRST, null, '/^\w$/');
        if (!empty($letter)) {
            $this->addFirstLetterFilter($letter);
        }

        $year = $this->request->get(BookList::URL_PARAM_YEAR, null, '/^\d+$/');
        if (!empty($year)) {
            $this->addPubYearFilter($year);
        }

        // URL format: ...&c[2]=3&c[3]=other to filter on column 2 = 3 and column 3 = other
        $customIdArray = $this->request->get(CustomColumnType::URL_PARAM, null);
        if (!empty($customIdArray) && is_array($customIdArray)) {
            $this->addCustomIdArrayFilters($customIdArray);
        }
    }

    /**
     * Summary of addFilter
     * @param mixed $filter
     * @param mixed $param
     * @return void
     */
    public function addFilter($filter, $param)
    {
        $this->queryString .= 'and (' . $filter . ')';
        array_push($this->params, $param);
    }

    /**
     * Summary of addTagNameFilter
     * @param mixed $tagName
     * @return void
     */
    public function addTagNameFilter($tagName)
    {
        $exists = true;
        if (preg_match("/^!(.*)$/", $tagName, $matches)) {
            $exists = false;
            $tagName = $matches[1];
        }

        $filter = 'exists (select null from books_tags_link, tags where books_tags_link.book = books.id and books_tags_link.tag = tags.id and tags.name = ?)';

        if (!$exists) {
            $filter = 'not ' . $filter;
        }

        $this->addFilter($filter, $tagName);
    }

    /**
     * Summary of addInstanceFilter
     * @param Base|Author|Language|Publisher|Rating|Serie|Tag|CustomColumn $instance
     * @return void
     */
    public function addInstanceFilter($instance)
    {
        if ($instance instanceof CustomColumn) {
            $this->addCustomIdFilter($instance->customColumnType, $instance->id);
            return;
        }
        $this->addLinkedIdFilter($instance->id, $instance->getLinkTable(), $instance->getLinkColumn(), $instance->limitSelf);
    }

    /**
     * Summary of addAuthorIdFilter
     * @param mixed $authorId
     * @return void
     */
    public function addAuthorIdFilter($authorId)
    {
        $this->addLinkedIdFilter($authorId, Author::SQL_LINK_TABLE, Author::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addLanguageIdFilter
     * @param mixed $languageId
     * @return void
     */
    public function addLanguageIdFilter($languageId)
    {
        $this->addLinkedIdFilter($languageId, Language::SQL_LINK_TABLE, Language::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addPublisherIdFilter
     * @param mixed $publisherId
     * @return void
     */
    public function addPublisherIdFilter($publisherId)
    {
        $this->addLinkedIdFilter($publisherId, Publisher::SQL_LINK_TABLE, Publisher::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addRatingIdFilter
     * @param mixed $ratingId
     * @return void
     */
    public function addRatingIdFilter($ratingId)
    {
        $this->addLinkedIdFilter($ratingId, Rating::SQL_LINK_TABLE, Rating::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addSeriesIdFilter
     * @param mixed $seriesId
     * @return void
     */
    public function addSeriesIdFilter($seriesId)
    {
        $this->addLinkedIdFilter($seriesId, Serie::SQL_LINK_TABLE, Serie::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addTagIdFilter
     * @param mixed $tagId
     * @return void
     */
    public function addTagIdFilter($tagId)
    {
        $this->addLinkedIdFilter($tagId, Tag::SQL_LINK_TABLE, Tag::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addIdentifierTypeFilter
     * @param mixed $identifierType
     * @return void
     */
    public function addIdentifierTypeFilter($identifierType)
    {
        $this->addLinkedIdFilter($identifierType, Identifier::SQL_LINK_TABLE, Identifier::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addFirstLetterFilter
     * @param mixed $letter
     * @return void
     */
    public function addFirstLetterFilter($letter)
    {
        $filter = 'substr(upper(books.sort), 1, 1) = ?';
        $this->addFilter($filter, $letter);
    }

    /**
     * Summary of addPubYearFilter
     * @param mixed $year
     * @return void
     */
    public function addPubYearFilter($year)
    {
        $filter = 'substr(date(books.pubdate), 1, 4) = ?';
        $this->addFilter($filter, $year);
    }

    /**
     * Summary of addCustomIdArrayFilters
     * @param array<mixed> $customIdArray
     * @return void
     */
    public function addCustomIdArrayFilters($customIdArray)
    {
        foreach ($customIdArray as $customId => $valueId) {
            if (!preg_match('/^\d+$/', $customId)) {
                continue;
            }
            $customType = CustomColumnType::createByCustomID($customId, $this->databaseId);
            $this->addCustomIdFilter($customType, $valueId);
        }
    }

    /**
     * Summary of addCustomIdFilter
     * @param CustomColumnType $customType
     * @param mixed $valueId
     * @return void
     */
    public function addCustomIdFilter($customType, $valueId)
    {
        [$filter, $params] = $customType->getFilter($valueId, $this->parentTable);
        if (!empty($filter)) {
            $this->queryString .= 'and (' . $filter . ')';
            foreach ($params as $param) {
                array_push($this->params, $param);
            }
        }
    }

    /**
     * Summary of addLinkedIdFilter
     * @param mixed $linkId
     * @param mixed $linkTable
     * @param mixed $linkColumn
     * @param mixed $limitSelf if filtering on the same table as the parent, limit results to self (or not for tags)
     * @return void
     */
    public function addLinkedIdFilter($linkId, $linkTable, $linkColumn, $limitSelf = true)
    {
        $exists = true;
        $matches = [];
        if (preg_match("/^!(.*)$/", $linkId, $matches)) {
            $exists = false;
            $linkId = $matches[1];
        }

        if ($this->parentTable == $linkTable) {
            if ($limitSelf) {
                $filter = "{$linkTable}.{$linkColumn} = ?";
            } else {
                // find other tags applied to books where this tag applies
                $filter = "exists (select null from {$linkTable} as filterself, books where {$this->parentTable}.book = books.id and {$this->parentTable}.{$linkColumn} != filterself.{$linkColumn} and filterself.book = books.id and filterself.{$linkColumn} = ?)";
            }
        } elseif ($this->parentTable == "books") {
            $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        } else {
            $filter = "exists (select null from {$linkTable}, books where {$this->parentTable}.book = books.id and {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        }

        if (!$exists) {
            $filter = 'not ' . $filter;
        }

        $this->addFilter($filter, $linkId);
    }

    /**
     * Summary of getEntryArray
     * @param Request $request
     * @param mixed $database
     * @return array<Entry>
     */
    public static function getEntryArray($request, $database = null)
    {
        $entryArray = [];
        foreach (static::URL_PARAMS as $paramName => $className) {
            $paramValue = $request->get($paramName, null);
            if (!isset($paramValue)) {
                continue;
            }
            if ($className == BookList::class) {
                $booklist = new BookList(Request::build([$paramName => $paramValue]), $database);
                $groupFunc = ($paramName == 'f') ? 'getCountByFirstLetter' : 'getCountByPubYear';
                $entryArray = array_merge($entryArray, $booklist->$groupFunc());
                continue;
            }
            if ($className == CustomColumnType::class) {
                foreach ($paramValue as $customId => $valueId) {
                    $custom = CustomColumn::createCustom($customId, $valueId, $database);
                    $entryArray = array_merge($entryArray, [ $custom->getCustomCount() ]);
                }
                continue;
            }
            // remove negative flag for filter entry here
            if (preg_match('/^!\d+$/', $paramValue)) {
                $paramValue = substr($paramValue, 1);
            }
            $req = Request::build([$paramName => $paramValue]);
            $baselist = new BaseList($className, $req, $database);
            $entries = $baselist->getEntriesByFilter();
            $entryArray = array_merge($entryArray, $entries);
        }
        return $entryArray;
    }
}
