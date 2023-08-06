<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

class CustomColumnTypeSeries extends CustomColumnType
{
    protected function __construct($pcustomId, $database = null)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_SERIES, $database);
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string
     */
    private function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string
     */
    private function getTableLinkColumn()
    {
        return "value";
    }

    public function getQuery($id)
    {
        global $config;
        if (empty($id) && in_array("custom", $config['cops_show_not_set_filter'])) {
            $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", $this->getTableLinkName());
            return [$query, []];
        }
        $query = str_format(BookList::SQL_BOOKS_BY_CUSTOM, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return [$query, [$id]];
    }

    public function getFilter($id)
    {
        $linkTable = $this->getTableLinkName();
        $linkColumn = $this->getTableLinkColumn();
        $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        return [$filter, [$id]];
    }

    public function getCustom($id)
    {
        $result = $this->getDb($this->databaseId)->prepare(str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName()));
        $result->execute([$id]);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($id, $post->name, $this);
        }
        return new CustomColumn(null, localize("customcolumn.boolean.unknown"), $this);
    }

    protected function getAllCustomValuesFromDatabase($n = -1)
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.value AS name, count(*) AS count FROM {0}, {1} WHERE {0}.id = {1}.{2} GROUP BY {0}.id, {0}.value ORDER BY {0}.value";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = $this->getPaginatedResult($query, [], $n);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $customcolumn = new CustomColumn($post->id, $post->name, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }
        return $entryArray;
    }

    public function getDescription()
    {
        $count = $this->getDistinctValueCount();
        return str_format(localize("customcolumn.description.series", $count), $count);
    }

    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS value, {1}.{2} AS name, {1}.extra AS extra FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = {3}";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn(), $book->id);

        $result = $this->getDb($this->databaseId)->query($query);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->value . " [" . $post->extra . "]", $this);
        }
        return new CustomColumn(null, "", $this);
    }

    public function isSearchable()
    {
        return true;
    }
}
