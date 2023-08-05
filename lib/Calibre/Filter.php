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
use SebLucas\Cops\Pages\Page;

class Filter
{
    public const PAGE_ID = Page::FILTER_ID;
    public const PAGE_DETAIL = Page::FILTER;
    public const URL_PARAMS = [
        'a' => Author::class,
        'l' => Language::class,
        'p' => Publisher::class,
        'r' => Rating::class,
        's' => Serie::class,
        't' => Tag::class,
        'c' => CustomColumnType::class,
        'f' => BookList::class,
        'y' => BookList::class,
    ];
    protected Request $request;
    protected array $params = [];
    protected string $parentTable = "books";
    protected string $queryString = "";
    protected mixed $databaseId;

    /**
     * Summary of __construct
     * @param Request|array $request current request or urlParams array
     * @param array $params initial query params
     * @param string $parent optional parent link table if we need to link books, e.g. books_series_link
     * @param mixed $database current database in multiple database setup
     */
    public function __construct(Request|array $request, array $params = [], string $parent = "books", mixed $database = null)
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
     * @return array updated query params including filters
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

        $authorId = $this->request->get('a', null, '/^!?\d+$/');
        if (!empty($authorId)) {
            $this->addAuthorIdFilter($authorId);
        }

        $languageId = $this->request->get('l', null, '/^!?\d+$/');
        if (!empty($languageId)) {
            $this->addLanguageIdFilter($languageId);
        }

        $publisherId = $this->request->get('p', null, '/^!?\d+$/');
        if (!empty($publisherId)) {
            $this->addPublisherIdFilter($publisherId);
        }

        $ratingId = $this->request->get('r', null, '/^!?\d+$/');
        if (!empty($ratingId)) {
            $this->addRatingIdFilter($ratingId);
        }

        $seriesId = $this->request->get('s', null, '/^!?\d+$/');
        if (!empty($seriesId)) {
            $this->addSeriesIdFilter($seriesId);
        }

        $tagId = $this->request->get('t', null, '/^!?\d+$/');
        if (!empty($tagId)) {
            $this->addTagIdFilter($tagId);
        }

        $letter = $this->request->get('f', null, '/^\w$/');
        if (!empty($letter)) {
            $this->addFirstLetterFilter($letter);
        }

        $year = $this->request->get('y', null, '/^\d+$/');
        if (!empty($year)) {
            $this->addPubYearFilter($year);
        }

        // URL format: ...&c[2]=3&c[3]=other to filter on column 2 = 3 and column 3 = other
        $customIdArray = $this->request->get('c', null);
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
     * @param array $customIdArray
     * @return void
     */
    public function addCustomIdArrayFilters($customIdArray)
    {
        foreach ($customIdArray as $customId => $valueId) {
            if (!preg_match('/^\d+$/', $customId)) {
                continue;
            }
            $this->addCustomIdFilter($customId, $valueId);
        }
    }

    /**
     * Summary of addCustomIdFilter
     * @param mixed $customId
     * @param mixed $valueId
     * @return void
     */
    public function addCustomIdFilter($customId, $valueId)
    {
        $customType = CustomColumnType::createByCustomID($customId, $this->databaseId);
        [$filter, $params] = $customType->getFilter($valueId);
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
     * @return void
     */
    public function addLinkedIdFilter($linkId, $linkTable, $linkColumn)
    {
        $exists = true;
        if (preg_match("/^!(.*)$/", $linkId, $matches)) {
            $exists = false;
            $linkId = $matches[1];
        }

        if ($this->parentTable == $linkTable) {
            $filter = "{$linkTable}.{$linkColumn} = ?";
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

    public static function getEntryArray($request, $database = null)
    {
        $entryArray = [];
        foreach (self::URL_PARAMS as $paramName => $className) {
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
                    $entryArray = array_merge($entryArray, [ $custom->getCount() ]);
                }
                continue;
            }
            $entries = $className::getEntriesByFilter([$paramName => $paramValue], -1, $database);
            $entryArray = array_merge($entryArray, $entries);
        }
        return $entryArray;
    }
}
