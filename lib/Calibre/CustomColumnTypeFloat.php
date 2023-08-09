<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

class CustomColumnTypeFloat extends CustomColumnType
{
    protected function __construct($pcustomId, $database)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_FLOAT, $database);
    }

    public function getQuery($id)
    {
        global $config;
        if (empty($id) && strval($id) !== '0.0' && in_array("custom", $config['cops_show_not_set_filter'])) {
            $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_DIRECT, "{0}", "{1}", $this->getTableName());
        return [$query, [$id]];
    }

    public function getQueryByRange($range)
    {
        global $config;
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_RANGE, "{0}", "{1}", $this->getTableName());
        [$lower, $upper] = explode('-', $range);
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
        $queryFormat = "SELECT value AS id, count(*) AS count FROM {0} GROUP BY value";
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

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb($this->databaseId)->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, $post->value, $this);
        }
        return new CustomColumn(null, localize("customcolumn.float.unknown"), $this);
    }

    public function isSearchable()
    {
        return true;
    }
}
