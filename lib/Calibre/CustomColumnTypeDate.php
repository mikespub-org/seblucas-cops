<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use DateTime;
use UnexpectedValueException;

class CustomColumnTypeDate extends CustomColumnType
{
    public const GET_PATTERN = '/^(\d+)$/';

    protected function __construct($pcustomId, $database)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_DATE, $database);
    }

    public function getQuery($id)
    {
        global $config;
        if (empty($id) && in_array("custom", $config['cops_show_not_set_filter'])) {
            $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $date = new DateTime($id);
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_DATE, "{0}", "{1}", $this->getTableName());
        return [$query, [$date->format("Y-m-d")]];
    }

    public function getQueryByYear($year)
    {
        if (!preg_match(self::GET_PATTERN, $year)) {
            throw new UnexpectedValueException();
        }
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_YEAR, "{0}", "{1}", $this->getTableName());
        return [$query, [$year]];
    }

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

    public function getCustom($id)
    {
        if (empty($id)) {
            return new CustomColumn(null, localize("customcolumn.date.unknown"), $this);
        }
        $date = new DateTime($id);

        return new CustomColumn($id, $date->format(localize("customcolumn.date.format")), $this);
    }

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

    public function getDistinctValueCount()
    {
        $queryFormat = "SELECT COUNT(DISTINCT date(value)) AS count FROM {0}";
        $query = str_format($queryFormat, $this->getTableName());
        return Database::querySingle($query, $this->databaseId);
    }

    /**
     * Summary of getCountByYear
     * @param mixed $page can be $columnType::PAGE_ALL or $columnType::PAGE_DETAIL
     * @param mixed $sort
     * @return Entry[]
     */
    public function getCountByYear($page, $sort = null)
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
        $label = 'year';
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->groupid,
                $this->getAllCustomsId().':'.$label.':'.$post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkNavigation("?page=" . $page . "&custom={$this->customId}&year=". rawurlencode($post->groupid), null, null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }

        return $entryArray;
    }

    /**
     * Summary of getCustomValuesByYear
     * @param mixed $year
     * @param mixed $sort
     * @return Entry[]
     */
    public function getCustomValuesByYear($year, $sort = null)
    {
        if (!preg_match(self::GET_PATTERN, $year)) {
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

    public function isSearchable()
    {
        return true;
    }
}
