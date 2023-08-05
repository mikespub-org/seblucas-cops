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

class CustomColumnTypeDate extends CustomColumnType
{
    protected function __construct($pcustomId, $database)
    {
        parent::__construct($pcustomId, self::CUSTOM_TYPE_DATE, $database);
    }

    /**
     * Get the name of the sqlite table for this column
     *
     * @return string
     */
    private function getTableName()
    {
        return "custom_column_{$this->customId}";
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

    public function getFilter($id)
    {
        $date = new DateTime($id);
        $linkTable = $this->getTableName();
        $linkColumn = "value";
        $filter = "exists (select null from {$linkTable} where {$linkTable}.book = books.id and date({$linkTable}.{$linkColumn}) = ?)";
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

    protected function getAllCustomValuesFromDatabase()
    {
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} GROUP BY datevalue";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb($this->databaseId)->query($query);

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

    public function getCountByYear()
    {
        $queryFormat = "SELECT substr(date(value), 1, 4) AS groupid, count(*) AS count FROM {0} GROUP BY groupid";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb($this->databaseId)->query($query);

        $entryArray = [];
        $label = 'year';
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->groupid,
                $this->getAllCustomsId().':'.$label.':'.$post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkNavigation("?page=" . self::PAGE_ALL . "&custom={$this->customId}&year=". rawurlencode($post->groupid), null, null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }

        return $entryArray;
    }

    public function getCustomValuesByYear($year)
    {
        $queryFormat = "SELECT date(value) AS datevalue, count(*) AS count FROM {0} WHERE substr(date(value), 1, 4) = ? GROUP BY datevalue";
        $query = str_format($queryFormat, $this->getTableName());
        $result = $this->getDb($this->databaseId)->prepare($query);
        $params = [ $year ];
        $result->execute($params);

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
        $queryFormat = "SELECT date({0}.value) AS datevalue FROM {0} WHERE {0}.book = {1}";
        $query = str_format($queryFormat, $this->getTableName(), $book->id);

        $result = $this->getDb($this->databaseId)->query($query);
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
