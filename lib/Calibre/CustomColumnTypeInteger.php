<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use UnexpectedValueException;

class CustomColumnTypeInteger extends CustomColumnType
{
    public const GET_PATTERN = '/^(-?[0-9]+)-(-?[0-9]+)$/';

    /**
     * Summary of __construct
     * @param mixed $pcustomId
     * @param string $datatype
     * @param mixed $database
     * @throws \UnexpectedValueException
     */
    protected function __construct($pcustomId, $datatype = self::TYPE_INT, $database = null)
    {
        switch ($datatype) {
            case self::TYPE_INT:
                parent::__construct($pcustomId, self::TYPE_INT, $database);
                break;
            case self::TYPE_FLOAT:
                parent::__construct($pcustomId, self::TYPE_FLOAT, $database);
                break;
            default:
                throw new UnexpectedValueException();
        }
    }

    /**
     * Summary of getQuery
     * @param mixed $id
     * @return array{0: string, 1: array<mixed>}|null
     */
    public function getQuery($id)
    {
        if (empty($id) && strval($id) !== '0' && in_array("custom", Config::get('show_not_set_filter'))) {
            $query = str_format(self::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $query = str_format(self::SQL_BOOKLIST_VALUE, "{0}", "{1}", $this->getTableName());
        return [$query, [$id]];
    }

    /**
     * Summary of getQueryByRange
     * @param mixed $range
     * @throws \UnexpectedValueException
     * @return array{0: string, 1: array<mixed>}|null
     */
    public function getQueryByRange($range)
    {
        $matches = [];
        if (!preg_match(self::GET_PATTERN, $range, $matches)) {
            throw new UnexpectedValueException();
        }
        $lower = $matches[1];
        $upper = $matches[2];
        $query = str_format(self::SQL_BOOKLIST_RANGE, "{0}", "{1}", $this->getTableName());
        return [$query, [$lower, $upper]];
    }

    /**
     * Summary of getFilter
     * @param mixed $id
     * @param mixed $parentTable
     * @return array{0: string, 1: array<mixed>}|null
     */
    public function getFilter($id, $parentTable = null)
    {
        $linkTable = $this->getTableName();
        $linkColumn = "value";
        if (!empty($parentTable) && $parentTable != "books") {
            $filter = "exists (select null from {$linkTable}, books where {$parentTable}.book = books.id and {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        } else {
            $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        }
        return [$filter, [$id]];
    }

    /**
     * Summary of getCustom
     * @param mixed $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    /**
     * Summary of getAllCustomValuesFromDatabase
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    protected function getAllCustomValuesFromDatabase($n = -1, $sort = null)
    {
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} GROUP BY value";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, value';
        } else {
            $queryFormat .= ' ORDER BY value';
        }
        $query = str_format($queryFormat, $this->getTableName());

        $result = $this->getPaginatedResult($query, [], $n);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $name = $post->id;
            $customcolumn = new CustomColumn($post->id, $name, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of getCountByRange
     * @param mixed $page can be $columnType::PAGE_ALL or $columnType::PAGE_DETAIL
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getCountByRange($page, $sort = null)
    {
        $numtiles = Config::get('custom_integer_split_range');
        if ($numtiles <= 1) {
            $numtiles = Config::get('max_item_per_page');
        }
        if ($numtiles < 1) {
            $numtiles = 1;
        }
        // Equal height distribution using NTILE() has problem with overlapping range
        //$queryFormat = "SELECT groupid, MIN(value) AS min_value, MAX(value) AS max_value, COUNT(*) AS count FROM (SELECT value, NTILE({$numtiles}) OVER (ORDER BY value) AS groupid FROM {0}) x GROUP BY groupid";
        // Semi-equal height distribution using CUME_DIST()
        $queryFormat = "SELECT CAST(ROUND(dist * ({$numtiles} - 1), 0) AS INTEGER) AS groupid, MIN(value) AS min_value, MAX(value) AS max_value, COUNT(*) AS count FROM (SELECT value, CUME_DIST() OVER (ORDER BY value) dist FROM {0}) GROUP BY groupid";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, groupid';
        } else {
            $queryFormat .= ' ORDER BY groupid';
        }
        $query = str_format($queryFormat, $this->getTableName());
        $result = Database::query($query, [], $this->databaseId);

        $entryArray = [];
        $label = 'range';
        while ($post = $result->fetchObject()) {
            $range = $post->min_value . "-" . $post->max_value;
            array_push($entryArray, new Entry(
                $range,
                $this->getEntryId().':'.$label.':'.$range,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkNavigation("?page=" . $page . "&custom={$this->customId}&range=". rawurlencode($range), null, null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }

        return $entryArray;
    }

    /**
     * Summary of getCustomValuesByRange
     * @param mixed $range
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getCustomValuesByRange($range, $sort = null)
    {
        $matches = [];
        if (!preg_match(self::GET_PATTERN, $range, $matches)) {
            throw new UnexpectedValueException();
        }
        $lower = $matches[1];
        $upper = $matches[2];
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} WHERE value >= ? AND value <= ? GROUP BY value";
        if (!empty($sort) && $sort == 'count') {
            $queryFormat .= ' ORDER BY count desc, value';
        } else {
            $queryFormat .= ' ORDER BY value';
        }
        $query = str_format($queryFormat, $this->getTableName());
        $result = Database::query($query, [$lower, $upper], $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $name = $post->id;
            $customcolumn = new CustomColumn($post->id, $name, $this);
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
        $queryFormat = "SELECT {0}.value AS value FROM {0} WHERE {0}.book = ?";
        $query = str_format($queryFormat, $this->getTableName());

        $result = Database::query($query, [$book->id], $this->databaseId);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, $post->value, $this);
        }
        return new CustomColumn(null, localize("customcolumn.int.unknown"), $this);
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
