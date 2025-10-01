<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use DateTime;
use UnexpectedValueException;

class CustomColumnTypeDate extends CustomColumnType
{
    public const SQL_BOOKLIST = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and date({2}.value) = ? {1} order by books.sort';
    public const SQL_BOOKLIST_YEAR = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and substr(date({2}.value), 1, 4) = ? {1} order by {2}.value';
    public const GET_PATTERN = '/^(\d+)$/';

    /**
     * Summary of __construct
     * @param int $customId
     * @param ?int $database
     * @param array<string, mixed> $displaySettings
     */
    protected function __construct($customId, $database = null, $displaySettings = [])
    {
        parent::__construct($customId, self::TYPE_DATE, $database, $displaySettings);
    }

    /**
     * Summary of getQuery
     * @param string|int|null $id
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getQuery($id)
    {
        if (empty($id) && in_array("custom", Config::get('show_not_set_filter'))) {
            $query = str_format(self::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $date = new DateTime($id);
        $query = str_format(self::SQL_BOOKLIST, "{0}", "{1}", $this->getTableName());
        return [$query, [$date->format("Y-m-d")]];
    }

    /**
     * Summary of getQueryByYear
     * @param mixed $year
     * @throws \UnexpectedValueException
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getQueryByYear($year)
    {
        if (!preg_match(self::GET_PATTERN, (string) $year)) {
            throw new UnexpectedValueException();
        }
        $query = str_format(self::SQL_BOOKLIST_YEAR, "{0}", "{1}", $this->getTableName());
        return [$query, [$year]];
    }

    /**
     * Summary of getFilter
     * @param string|int|null $id
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getFilter($id, $parentTable = null)
    {
        $date = new DateTime($id);
        $linkTable = $this->getTableName();
        $linkColumn = "value";
        if (!empty($parentTable) && $parentTable != "books") {
            $filter = "exists (select null from {$linkTable}, books where {$parentTable}.book = books.id and {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        } else {
            $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and date({$linkTable}.{$linkColumn}) = ?)";
        }
        return [$filter, [$date->format("Y-m-d")]];
    }

    /**
     * Summary of getCustom
     * @param string|int|null $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        if (empty($id)) {
            return new CustomColumn(null, localize("customcolumn.date.unknown"), $this);
        }
        $date = new DateTime($id);

        return new CustomColumn($id, $date->format(localize("customcolumn.date.format")), $this);
    }

    /**
     * Summary of getAllCustomValuesFromDatabase
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    protected function getAllCustomValuesFromDatabase($n = -1, $sort = null)
    {
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} GROUP BY datevalue";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, datevalue';
        } else {
            $queryFormat .= ' ORDER BY datevalue';
        }
        $query = str_format($queryFormat, $this->getTableName());

        $result = $this->getPaginatedResult($query, [], $n);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);
            $id = $date->format("Y-m-d");
            $name = $date->format(localize("customcolumn.date.format"));

            $customcolumn = new CustomColumn($id, $name, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }

        return $entryArray;
    }

    /**
     * Summary of getDistinctValueCount
     * @return mixed
     */
    public function getDistinctValueCount()
    {
        $queryFormat = "SELECT COUNT(DISTINCT date(value)) AS count FROM {0}";
        $query = str_format($queryFormat, $this->getTableName());
        return Database::querySingle($query, $this->databaseId);
    }

    /**
     * Summary of getCountByYear
     * @param mixed $routeName can be $columnType::ROUTE_ALL or $columnType::ROUTE_DETAIL
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getCountByYear($routeName, $sort = null)
    {
        $queryFormat = "SELECT substr(date(value), 1, 4) AS groupid, count(*) AS count FROM {0} GROUP BY groupid";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, groupid';
        } else {
            $queryFormat .= ' ORDER BY groupid';
        }
        $query = str_format($queryFormat, $this->getTableName());
        $result = Database::query($query, [], $this->databaseId);

        $entryArray = [];
        $param = 'year';
        while ($post = $result->fetchObject()) {
            $params = ['custom' => $this->customId, $param => $post->groupid, 'db' => $this->databaseId];
            // @todo if we want to use ROUTE_DETAIL we need to add id= here
            $params['id'] = '0';
            $href = fn() => $this->getRoute($routeName, $params);
            array_push($entryArray, new Entry(
                $post->groupid,
                $this->getEntryId() . ':' . $param . ':' . $post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [ new LinkNavigation($href, null, null) ],
                $this->databaseId,
                ucfirst($param),
                $post->count
            ));
        }

        return $entryArray;
    }

    /**
     * Summary of getCustomValuesByYear
     * @param mixed $year
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getCustomValuesByYear($year, $sort = null)
    {
        if (!preg_match(self::GET_PATTERN, (string) $year)) {
            throw new UnexpectedValueException();
        }
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} WHERE substr(date(value), 1, 4) = ? GROUP BY datevalue";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, datevalue';
        } else {
            $queryFormat .= ' ORDER BY datevalue';
        }
        $query = str_format($queryFormat, $this->getTableName());
        $params = [ $year ];
        $result = Database::query($query, $params, $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);
            $id = $date->format("Y-m-d");
            $name = $date->format(localize("customcolumn.date.format"));

            $customcolumn = new CustomColumn($id, $name, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }

        return $entryArray;
    }

    /**
     * Summary of getCustomByBook
     * @param Book $book
     * @return CustomColumn
     */
    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT date({0}.value) AS datevalue FROM {0} WHERE {0}.book = ?";
        $query = str_format($queryFormat, $this->getTableName());

        $result = Database::query($query, [$book->id], $this->databaseId);
        if ($post = $result->fetchObject()) {
            $date = new DateTime($post->datevalue);

            return new CustomColumn($date->format("Y-m-d"), $date->format(localize("customcolumn.date.format")), $this);
        }
        return new CustomColumn(null, localize("customcolumn.date.unknown"), $this);
    }

    /**
     * Summary of isSearchable
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
