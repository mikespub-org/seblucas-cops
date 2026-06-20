<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Model\Entry;
use UnexpectedValueException;

class CustomColumnTypeText extends CustomColumnType
{
    public const SQL_BOOKLIST_CSV = 'select distinct {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and books.id in (select book from {2} where {3} = ? group by book having count(*) = {4}) {1} order by books.sort';

    /**
     * Summary of __construct
     * @param int $customId
     * @param string $datatype
     * @param ?DatabaseContext $dbContext
     * @param array<string, mixed> $displaySettings
     * @return void
     * @throws \UnexpectedValueException
     */
    protected function __construct($customId, $datatype = self::TYPE_TEXT, $dbContext = null, $displaySettings = [])
    {
        switch ($datatype) {
            case self::TYPE_TEXT:
                parent::__construct($customId, self::TYPE_TEXT, $dbContext, $displaySettings);
                return;
            case self::TYPE_CSV:
                parent::__construct($customId, self::TYPE_CSV, $dbContext, $displaySettings);
                return;
            case self::TYPE_ENUM:
                parent::__construct($customId, self::TYPE_ENUM, $dbContext, $displaySettings);
                return;
            case self::TYPE_SERIES:
                parent::__construct($customId, self::TYPE_SERIES, $dbContext, $displaySettings);
                return;
            default:
                throw new UnexpectedValueException();
        }
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
        if (empty($id) && in_array("custom", $this->config('show_not_set_filter'))) {
            $query = str_format(self::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableLinkName());
            return [$query, []];
        }
        // handle case where we have several values, e.g. array of text for type 2 (csv)
        if ($this->datatype == self::TYPE_CSV && str_contains((string) $id, ',')) {
            $params = array_map(trim(...), explode(',', $id));
            $query = str_format(self::SQL_BOOKLIST_CSV, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn(), (string) count($params));
            $query = str_replace(' = ? ', ' IN (' . str_repeat('?,', count($params) - 1) . '?) ', $query);
            return [$query, $params];
        }
        $query = str_format(self::SQL_BOOKLIST_LINK, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
        return [$query, [$id]];
    }

    /**
     * Summary of getFilter
     * @param string|int|null $id
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getFilter($id, $parentTable = null)
    {
        $linkTable = $this->getTableLinkName();
        $linkColumn = $this->getTableLinkColumn();
        if (!empty($parentTable) && $parentTable != "books") {
            $filter = "exists (select null from {$linkTable}, books where {$parentTable}.book = books.id and {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        } else {
            $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and {$linkTable}.{$linkColumn} = ?)";
        }
        // @todo handle case where we have several values, e.g. array of text for type 2 (csv)
        /**
        if ($this->datatype == self::TYPE_CSV && str_contains((string) $id, ',')) {
            $params = array_map(trim(...), explode(',', $id));
            $filter = str_replace(' = ?', ' IN (' . str_repeat('?,', count($params) - 1) . '?)', $filter);
            return [$filter, $params];
        }
         */
        return [$filter, [$id]];
    }

    /**
     * Summary of getCustom
     * @param string|int|null $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        // handle case where we have several values, e.g. array of text for type 2 (csv)
        if ($this->datatype == self::TYPE_CSV && str_contains((string) $id, ',')) {
            $params = array_map(trim(...), explode(',', $id));
            $query = str_format("SELECT id, value AS name FROM {0}", $this->getTableName());
            $query .= ' WHERE id IN (' . str_repeat('?,', count($params) - 1) . '?)';
            $result = $this->getDbContext()->query($query, $params);
            $idArray = [];
            $nameArray = [];
            while ($post = $result->fetchObject()) {
                array_push($idArray, $post->id);
                array_push($nameArray, $post->name);
            }
            return new CustomColumn(implode(",", $idArray), implode(",", $nameArray), $this);
        }
        $query = str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName());
        $result = $this->getDbContext()->query($query, [$id]);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($id, $post->name, $this);
        }
        $default = static::getDefaultName();
        return new CustomColumn(null, $this->localize($default), $this);
    }

    /**
     * Summary of getAllCustomValuesFromDatabase
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    protected function getAllCustomValuesFromDatabase($n = -1, $sort = null)
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

    /**
     * Summary of getCustomByBook
     * @param mixed $book
     * @throws \UnexpectedValueException
     * @return CustomColumn
     */
    public function getCustomByBook($book)
    {
        $queryFormat = match ($this->datatype) {
            self::TYPE_TEXT => "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ? ORDER BY {0}.value",
            self::TYPE_CSV => "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ? ORDER BY {0}.value",
            self::TYPE_ENUM => "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ?",
            self::TYPE_SERIES => "SELECT {0}.id AS id, {1}.{2} AS name, {1}.extra AS extra FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ?",
            default => throw new UnexpectedValueException(),
        };
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = $this->getDbContext()->query($query, [$book->id]);
        // handle case where we have several values, e.g. array of text for type 2 (csv)
        if ($this->datatype === self::TYPE_CSV) {
            $idArray = [];
            $nameArray = [];
            while ($post = $result->fetchObject()) {
                array_push($idArray, $post->id);
                array_push($nameArray, $post->name);
            }
            return new CustomColumn(implode(",", $idArray), implode(",", $nameArray), $this);
        }
        if ($post = $result->fetchObject()) {
            return new CustomColumn($post->id, $post->name, $this);
        }
        return new CustomColumn(null, "", $this);
    }

    /**
     * Summary of isSearchable
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return "customcolumn.boolean.unknown";
    }
}
