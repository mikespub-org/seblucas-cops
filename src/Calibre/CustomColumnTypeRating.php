<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Model\Entry;

class CustomColumnTypeRating extends CustomColumnType
{
    public const SQL_BOOKLIST = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left join {2} on {2}.book = books.id
    left join {3} on {3}.id = {2}.{4}
    where {3}.value = ?  order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left join {2} on {2}.book = books.id
    left join {3} on {3}.id = {2}.{4}
    where ((books.id not in (select {2}.book from {2})) or ({3}.value = 0)) {1} order by books.sort';

    /**
     * Summary of __construct
     * @param int $customId
     * @param ?int $database
     * @param array<string, mixed> $displaySettings
     */
    protected function __construct($customId, $database = null, $displaySettings = [])
    {
        parent::__construct($customId, self::TYPE_RATING, $database, $displaySettings);
    }

    /**
     * Get the name of the linking sqlite table for this column
     * (or NULL if there is no linktable)
     *
     * @return string
     */
    protected function getTableLinkName()
    {
        return "books_custom_column_{$this->customId}_link";
    }

    /**
     * Get the name of the linking column in the linktable
     *
     * @return string
     */
    protected function getTableLinkColumn()
    {
        return "value";
    }

    /**
     * Summary of getQuery
     * @param string|int|null $id
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getQuery($id)
    {
        if (empty($id)) {
            $query = str_format(self::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return [$query, []];
        } else {
            $query = str_format(self::SQL_BOOKLIST, "{0}", "{1}", $this->getTableLinkName(), $this->getTableName(), $this->getTableLinkColumn());
            return [$query, [$id]];
        }
    }

    /**
     * Summary of getFilter
     * @param string|int|null $id
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getFilter($id, $parentTable = null)
    {
        // @todo do we want to filter on ratings Id or Value here
        return ["", []];
    }

    /**
     * Summary of getCustom
     * @param string|int|null $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        $value = intval($id) / 2;
        return new CustomColumn($id, str_format(localize("customcolumn.stars", $value), $value), $this);
    }

    /**
     * Summary of getAllCustomValuesFromDatabase
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    protected function getAllCustomValuesFromDatabase($n = -1, $sort = null)
    {
        $queryFormat = "SELECT coalesce({0}.value, 0) AS value, count(*) AS count FROM books  LEFT JOIN {1} ON  books.id = {1}.book LEFT JOIN {0} ON {0}.id = {1}.value GROUP BY coalesce({0}.value, -1)";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName());
        $result = Database::query($query, [], $this->databaseId);

        $countArray = [0 => 0, 2 => 0, 4 => 0, 6 => 0, 8 => 0, 10 => 0];
        while ($row = $result->fetchObject()) {
            $countArray[$row->value] = $row->count;
        }

        $entryArray = [];

        // @todo align with other custom columns
        for ($i = 0; $i <= 5; $i++) {
            $id = $i * 2;
            $count = $countArray[$id];
            $name = str_format(localize("customcolumn.stars", $i), $i);
            $customcolumn = new CustomColumn($id, $name, $this);
            array_push($entryArray, $customcolumn->getEntry($count));
        }

        return $entryArray;
    }

    /**
     * Summary of getDistinctValueCount
     * @return int
     */
    public function getDistinctValueCount()
    {
        return count($this->getAllCustomValues());
    }

    /**
     * Summary of getContent
     * @param int $count
     * @return string
     */
    public function getContent($count = 0)
    {
        return localize("customcolumn.description.rating");
    }

    /**
     * Summary of getCustomByBook
     * @param mixed $book
     * @return CustomColumn
     */
    public function getCustomByBook($book)
    {
        $queryFormat = "SELECT {0}.value AS value FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ?";
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = Database::query($query, [$book->id], $this->databaseId);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->value, str_format(localize("customcolumn.stars", $post->value / 2), $post->value / 2), $this);
        }
        return new CustomColumn(null, localize("customcolumn.rating.unknown"), $this);
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
