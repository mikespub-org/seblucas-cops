<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;
use UnexpectedValueException;

class CustomColumnTypeFloat extends CustomColumnType
{
    public const GET_PATTERN = '/^(-?[0-9.]+)-(-?[0-9.]+)$/';

    /**
     * Summary of __construct
     * @param int $customId
     * @param ?int $database
     * @param array<string, mixed> $displaySettings
     */
    protected function __construct($customId, $database = null, $displaySettings = [])
    {
        parent::__construct($customId, self::TYPE_FLOAT, $database, $displaySettings);
    }

    /**
     * Summary of getQuery
     * @param string|int|null $id
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getQuery($id)
    {
        if (empty($id) && strval($id) !== '0.0' && in_array("custom", Config::get('show_not_set_filter'))) {
            $query = str_format(self::SQL_BOOKLIST_NULL, "{0}", "{1}", $this->getTableName());
            return [$query, []];
        }
        $query = str_format(self::SQL_BOOKLIST_VALUE, "{0}", "{1}", $this->getTableName());
        return [$query, [$id]];
    }

    /**
     * Summary of getQueryByRange
     * @param string $range
     * @throws \UnexpectedValueException
     * @return ?array{0: string, 1: array<mixed>}
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
     * @param string|int|null $id
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
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
     * @param string|int|null $id
     * @return CustomColumn
     */
    public function getCustom($id)
    {
        return new CustomColumn($id, $id, $this);
    }

    /**
     * Summary of getAllCustomValuesFromDatabase
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    protected function getAllCustomValuesFromDatabase($n = -1, $sort = null)
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
        return new CustomColumn(null, localize("customcolumn.float.unknown"), $this);
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
