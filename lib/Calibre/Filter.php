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
use UnexpectedValueException;

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
        CustomColumn::URL_PARAM => CustomColumn::class,
        BookList::URL_PARAM_FIRST => BookList::class,
        BookList::URL_PARAM_YEAR => BookList::class,
        VirtualLibrary::URL_PARAM => VirtualLibrary::class,
    ];
    public const SEARCH_FIELDS = [
        'authors' => Author::class,
        //'formats' => Format::class,
        'languages' => Language::class,
        'publishers' => Publisher::class,
        'ratings' => Rating::class,
        'series' => Serie::class,
        'tags' => Tag::class,
    ];

    protected Request $request;
    /** @var array<mixed> */
    protected $params = [];
    protected string $parentTable = "books";
    protected string $queryString = "";
    /** @var ?int */
    protected $databaseId;

    /**
     * Summary of __construct
     * @param Request|array<mixed> $request current request or urlParams array
     * @param array<mixed> $params initial query params
     * @param string $parent optional parent link table if we need to link books, e.g. books_series_link
     * @param ?int $database current database in multiple database setup
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

        // See $config['cops_books_filter']
        $tagName = $this->request->get('tag', null);
        if (!empty($tagName)) {
            $this->addTagNameFilter($tagName);
        }

        // See $config['cops_calibre_virtual_libraries']
        $libraryId = $this->request->get(VirtualLibrary::URL_PARAM, null);
        if (!empty($libraryId)) {
            $this->addVirtualLibraryFilter($libraryId);
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
        $customIdArray = $this->request->get(CustomColumn::URL_PARAM, null);
        if (!empty($customIdArray) && is_array($customIdArray)) {
            $this->addCustomIdArrayFilters($customIdArray);
        }
    }

    /**
     * Summary of addFilter
     * @param string $filter
     * @param mixed $param
     * @return void
     */
    public function addFilter($filter, $param)
    {
        $this->queryString .= ' and (' . $filter . ')';
        array_push($this->params, $param);
    }

    /**
     * Summary of addTagNameFilter
     * @param string $tagName
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
     * Summary of addVirtualLibraryFilter
     * @param string|int $libraryId
     * @throws \UnexpectedValueException
     * @return void
     */
    public function addVirtualLibraryFilter($libraryId)
    {
        // URL format: ...&vl=2.Short_Stories_in_English
        if (str_contains($libraryId, '.')) {
            [$libraryId, $slug] = explode('.', $libraryId);
        }
        /** @var VirtualLibrary $instance */
        $instance = VirtualLibrary::getInstanceById($libraryId);
        if (empty($instance->id)) {
            return;
        }

        $search = $instance->value;
        $replace = $search;
        $params = [];
        $matches = [];
        // See https://github.com/seblucas/cops/pull/233 by @Broele
        preg_match_all('/(?P<attr>#?\w+)\:(?P<value>\w+|"(?P<quoted>[^"]*)")/', $search, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            // get search field
            if (!array_key_exists($match['attr'], static::SEARCH_FIELDS)) {
                $match['attr'] .= 's';
                if (!array_key_exists($match['attr'], static::SEARCH_FIELDS)) {
                    throw new UnexpectedValueException('Unsupported search field: ' . $match['attr']);
                }
            }
            // find exact match
            if (isset($match['quoted']) && str_starts_with($match['quoted'], '=')) {
                $value = substr($match['quoted'], 1);
                $className = static::SEARCH_FIELDS[$match['attr']];
                $instance = $className::getInstanceByName($value, $this->databaseId);
                $filterString = $this->getLinkedIdFilter($instance->getLinkTable(), $instance->getLinkColumn(), $instance->limitSelf);
                $replace = str_replace($match[0], $filterString, $replace);
                array_push($params, $instance->id);
            } else {
                throw new UnexpectedValueException('Unsupported search criteria: ' . $match['attr'] . '=' . $match['value']);
            }
        }

        if (!empty($replace)) {
            $this->queryString .= ' and (' . $replace . ')';
            foreach ($params as $param) {
                array_push($this->params, $param);
            }
        }
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
     * @param string|int $authorId
     * @return void
     */
    public function addAuthorIdFilter($authorId)
    {
        $this->addLinkedIdFilter($authorId, Author::SQL_LINK_TABLE, Author::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addLanguageIdFilter
     * @param string|int $languageId
     * @return void
     */
    public function addLanguageIdFilter($languageId)
    {
        $this->addLinkedIdFilter($languageId, Language::SQL_LINK_TABLE, Language::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addPublisherIdFilter
     * @param string|int $publisherId
     * @return void
     */
    public function addPublisherIdFilter($publisherId)
    {
        $this->addLinkedIdFilter($publisherId, Publisher::SQL_LINK_TABLE, Publisher::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addRatingIdFilter
     * @param string|int $ratingId
     * @return void
     */
    public function addRatingIdFilter($ratingId)
    {
        $this->addLinkedIdFilter($ratingId, Rating::SQL_LINK_TABLE, Rating::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addSeriesIdFilter
     * @param string|int $seriesId
     * @return void
     */
    public function addSeriesIdFilter($seriesId)
    {
        $this->addLinkedIdFilter($seriesId, Serie::SQL_LINK_TABLE, Serie::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addTagIdFilter
     * @param string|int $tagId
     * @return void
     */
    public function addTagIdFilter($tagId)
    {
        $this->addLinkedIdFilter($tagId, Tag::SQL_LINK_TABLE, Tag::SQL_LINK_COLUMN);
    }

    /**
     * Summary of addIdentifierTypeFilter
     * @param string $identifierType
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
            $this->queryString .= ' and (' . $filter . ')';
            foreach ($params as $param) {
                array_push($this->params, $param);
            }
        }
    }

    /**
     * Summary of addLinkedIdFilter
     * @param string|int $linkId
     * @param string $linkTable
     * @param string $linkColumn
     * @param bool $limitSelf if filtering on the same table as the parent, limit results to self (or not for tags)
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

        $filter = $this->getLinkedIdFilter($linkTable, $linkColumn, $limitSelf);

        if (!$exists) {
            $filter = 'not ' . $filter;
        }

        $this->addFilter($filter, $linkId);
    }

    /**
     * Summary of getLinkedIdFilter
     * @param string $linkTable
     * @param string $linkColumn
     * @param bool $limitSelf if filtering on the same table as the parent, limit results to self (or not for tags)
     * @return string
     */
    public function getLinkedIdFilter($linkTable, $linkColumn, $limitSelf = true)
    {
        if ($this->parentTable == $linkTable) {
            if ($limitSelf) {
                $filter = "{$linkTable}.{$linkColumn} = ?";
            } else {
                // find other tags/identifiers applied to books where this tag/identifier applies
                $filter = "exists (select null from {$linkTable} as filterself, books where {$this->parentTable}.book = books.id and {$this->parentTable}.{$linkColumn} != filterself.{$linkColumn} and filterself.book = books.id and filterself.{$linkColumn} = ?)";
            }
        } elseif ($this->parentTable == "books") {
            $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        } else {
            $filter = "exists (select null from {$linkTable}, books where {$this->parentTable}.book = books.id and {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        }

        return $filter;
    }

    /**
     * Summary of getEntryArray
     * @param Request $request
     * @param ?int $database
     * @return array<Entry>
     */
    public static function getEntryArray($request, $database = null)
    {
        $libraryId = $request->getVirtualLibrary();
        $entryArray = [];
        foreach (static::URL_PARAMS as $paramName => $className) {
            if ($className == VirtualLibrary::class) {
                continue;
            }
            $paramValue = $request->get($paramName, null);
            if (!isset($paramValue)) {
                continue;
            }
            // @todo do we want to filter by virtual library etc. here?
            if ($className == BookList::class) {
                $booklist = new BookList(Request::build([$paramName => $paramValue]), $database);
                $groupFunc = ($paramName == 'f') ? 'getCountByFirstLetter' : 'getCountByPubYear';
                $entryArray = array_merge($entryArray, $booklist->$groupFunc());
                continue;
            }
            if ($className == CustomColumn::class) {
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
            if (!empty($libraryId)) {
                $req = Request::build([$paramName => $paramValue, VirtualLibrary::URL_PARAM => $libraryId]);
            } else {
                $req = Request::build([$paramName => $paramValue]);
            }
            $baselist = new BaseList($className, $req, $database);
            $entries = $baselist->getEntriesByFilter();
            $entryArray = array_merge($entryArray, $entries);
        }
        return $entryArray;
    }
}
