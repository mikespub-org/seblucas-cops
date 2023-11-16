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
use UnexpectedValueException;

class CustomColumnTypeText extends CustomColumnType
{
    /**
     * Summary of __construct
     * @param int $pcustomId
     * @param string $datatype
     * @param ?int $database
     * @param array<string, mixed> $displaySettings
     * @return void
     * @throws \UnexpectedValueException
     */
    protected function __construct($pcustomId, $datatype = self::TYPE_TEXT, $database = null, $displaySettings = [])
    {
        switch ($datatype) {
            case static::TYPE_TEXT:
                parent::__construct($pcustomId, static::TYPE_TEXT, $database, $displaySettings);
                return;
            case static::TYPE_CSV:
                parent::__construct($pcustomId, static::TYPE_CSV, $database, $displaySettings);
                return;
            case static::TYPE_ENUM:
                parent::__construct($pcustomId, static::TYPE_ENUM, $database, $displaySettings);
                return;
            case static::TYPE_SERIES:
                parent::__construct($pcustomId, static::TYPE_SERIES, $database, $displaySettings);
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
        if (empty($id) && in_array("custom", Config::get('show_not_set_filter'))) {
            $query = str_format(static::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableLinkName());
            return [$query, []];
        }
        $query = str_format(static::SQL_BOOKLIST_LINK, "{0}", "{1}", $this->getTableLinkName(), $this->getTableLinkColumn());
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
        return [$filter, [$id]];
    }

    /**
     * Summary of getCustom
     * @param string|int|null $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        $query = str_format("SELECT id, value AS name FROM {0} WHERE id = ?", $this->getTableName());
        $result = Database::query($query, [$id], $this->databaseId);
        if ($post = $result->fetchObject()) {
            return new CustomColumn($id, $post->name, $this);
        }
        return new CustomColumn(null, localize("customcolumn.boolean.unknown"), $this);
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
        switch ($this->datatype) {
            case static::TYPE_TEXT:
                $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ? ORDER BY {0}.value";
                break;
            case static::TYPE_CSV:
                $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ? ORDER BY {0}.value";
                break;
            case static::TYPE_ENUM:
                $queryFormat = "SELECT {0}.id AS id, {0}.{2} AS name FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ?";
                break;
            case static::TYPE_SERIES:
                $queryFormat = "SELECT {0}.id AS id, {1}.{2} AS name, {1}.extra AS extra FROM {0}, {1} WHERE {0}.id = {1}.{2} AND {1}.book = ?";
                break;
            default:
                throw new UnexpectedValueException();
        }
        $query = str_format($queryFormat, $this->getTableName(), $this->getTableLinkName(), $this->getTableLinkColumn());

        $result = Database::query($query, [$book->id], $this->databaseId);
        // handle case where we have several values, e.g. array of text for type 2 (csv)
        if ($this->datatype === static::TYPE_CSV) {
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
}
