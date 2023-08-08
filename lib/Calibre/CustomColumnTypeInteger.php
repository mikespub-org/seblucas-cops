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
use UnexpectedValueException;

class CustomColumnTypeInteger extends CustomColumnType
{
    public const GET_PATTERN = '/^(-?[0-9]+)-(-?[0-9]+)$/';

    protected function __construct($pcustomId, $datatype = self::CUSTOM_TYPE_INT, $database = null)
    {
        switch ($datatype) {
            case self::CUSTOM_TYPE_INT:
                parent::__construct($pcustomId, self::CUSTOM_TYPE_INT, $database);
                break;
            case self::CUSTOM_TYPE_FLOAT:
                parent::__construct($pcustomId, self::CUSTOM_TYPE_FLOAT, $database);
                break;
            default:
                throw new UnexpectedValueException();
        }
    }

    public function getQuery($id)
    {
        global $config;
        if (empty($id) && strval($id) !== '0' && in_array("custom", $config['cops_show_not_set_filter'])) {
            $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return [$query, [$id]];
    }

    public function getQueryByRange($range)
    {
        $matches = [];
        if (!preg_match(self::GET_PATTERN, $range, $matches)) {
            throw new UnexpectedValueException();
        }
        $lower = $matches[1];
        $upper = $matches[2];
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_RANGE, "{0}", "{1}", $this->getTableName());
        return [$query, [$lower, $upper]];
    }

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

    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    protected function getAllCustomValuesFromDatabase($n = -1)
    {
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} GROUP BY value ORDER BY value";
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
     * @return Entry[]
     */
    public function getCountByRange($page)
    {
        global $config;
        $numtiles = $config['cops_custom_integer_split_range'];
        if ($numtiles <= 1) {
            $numtiles = $config['cops_max_item_per_page'];
        }
        if ($numtiles < 1) {
            $numtiles = 1;
        }
        // Equal height distribution using NTILE() has problem with overlapping range
        //$queryFormat = "SELECT groupid, MIN(value) AS min_value, MAX(value) AS max_value, COUNT(*) AS count FROM (SELECT value, NTILE({$numtiles}) OVER (ORDER BY value) AS groupid FROM {0}) x GROUP BY groupid";
        // Semi-equal height distribution using CUME_DIST()
        $queryFormat = "SELECT CAST(ROUND(dist * ({$numtiles} - 1), 0) AS INTEGER) AS groupid, MIN(value) AS min_value, MAX(value) AS max_value, COUNT(*) AS count FROM (SELECT value, CUME_DIST() OVER (ORDER BY value) dist FROM {0}) GROUP BY groupid";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb($this->databaseId)->query($query);

        $entryArray = [];
        $label = 'range';
        while ($post = $result->fetchObject()) {
            $range = $post->min_value . "-" . $post->max_value;
            array_push($entryArray, new Entry(
                $range,
                $this->getAllCustomsId().':'.$label.':'.$range,
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
     * @return Entry[]
     */
    public function getCustomValuesByRange($range)
    {
        $matches = [];
        if (!preg_match(self::GET_PATTERN, $range, $matches)) {
            throw new UnexpectedValueException();
        }
        $lower = $matches[1];
        $upper = $matches[2];
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} WHERE value >= ? AND value <= ? GROUP BY value ORDER BY value";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb($this->databaseId)->prepare($query);
        $result->execute([$lower, $upper]);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $name = $post->id;
            $customcolumn = new CustomColumn($post->id, $name, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }

        return $entryArray;
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb($this->databaseId)->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, $post->value, $this);
        }
        return new CustomColumn(null, localize("customcolumn.int.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}
