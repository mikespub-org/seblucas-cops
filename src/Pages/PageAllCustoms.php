<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\CustomColumnTypeDate;
use SebLucas\Cops\Calibre\CustomColumnTypeInteger;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;

class PageAllCustoms extends Page
{
    protected string $className = CustomColumnType::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $customId = $this->request->get("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId, $this->getDatabaseId());
        $columnType->setHandler($this->handler);

        $this->idPage = $columnType->getEntryId();
        $this->title = $columnType->getTitle();
        $this->getCustomEntries($columnType);
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("custom", Config::get('show_not_set_filter'))) {
            $this->addCustomNotSetEntry($columnType);
        }
    }

    /**
     * Summary of getCustomEntries
     * @param CustomColumnType $columnType
     * @return void
     */
    public function getCustomEntries($columnType)
    {
        // @todo do we want to filter by virtual library etc. here?
        if (Config::get('custom_date_split_year') == 1 && $columnType instanceof CustomColumnTypeDate) {
            $this->getCustomEntriesByYear($columnType);
        } elseif (Config::get('custom_integer_split_range') > 0 && $columnType instanceof CustomColumnTypeInteger) {
            $this->getCustomEntriesByRange($columnType);
        } elseif ($columnType->hasChildCategories()) {
            $this->sorted = $this->request->getSorted("sort");
            // use tag_browser_custom_column_X view here, to get the full hierarchy?
            $this->entryArray = $columnType->browseAllCustomValues($this->n, $this->sorted);
            $this->totalNumber = $columnType->getDistinctValueCount();
        } else {
            $this->sorted = $this->request->getSorted("value");
            $this->entryArray = $columnType->getAllCustomValues($this->n, $this->sorted);
            $this->totalNumber = $columnType->getDistinctValueCount();
        }
    }

    /**
     * Summary of getCustomEntriesByYear
     * @param CustomColumnTypeDate $columnType
     * @return void
     */
    public function getCustomEntriesByYear($columnType)
    {
        $year = $this->request->get("year", null, $columnType::GET_PATTERN);
        if (empty($year)) {
            // can be $columnType::PAGE_ALL or $columnType::PAGE_DETAIL
            $this->sorted = $this->request->getSorted("year");
            $this->entryArray = $columnType->getCountByYear($columnType::PAGE_DETAIL, $this->sorted);
            return;
        }
        // if we use $columnType::PAGE_ALL in PageAllCustoms, otherwise see PageCustomDetail
        $this->sorted = $this->request->getSorted("value");
        $this->entryArray = $columnType->getCustomValuesByYear($year, $this->sorted);
        $count = 0;
        foreach ($this->entryArray as $entry) {
            /** @var Entry $entry */
            $count += $entry->numberOfElement;
        }
        $this->title = str_format(localize("splitByYear.year"), str_format(localize("bookword", $count), $count), $year);
        $this->parentTitle = $columnType->getTitle();
        $this->parentUri = $columnType->getUri();
    }

    /**
     * Summary of getCustomEntriesByRange
     * @param CustomColumnTypeInteger $columnType
     * @return void
     */
    public function getCustomEntriesByRange($columnType)
    {
        $range = $this->request->get("range", null, $columnType::GET_PATTERN);
        if (empty($range)) {
            // can be $columnType::PAGE_ALL or $columnType::PAGE_DETAIL
            $this->sorted = $this->request->getSorted("range");
            $this->entryArray = $columnType->getCountByRange($columnType::PAGE_DETAIL, $this->sorted);
            return;
        }
        // if we use $columnType::PAGE_ALL in PageAllCustoms, otherwise see PageCustomDetail
        $this->sorted = $this->request->getSorted("value");
        $this->entryArray = $columnType->getCustomValuesByRange($range, $this->sorted);
        $count = 0;
        foreach ($this->entryArray as $entry) {
            /** @var Entry $entry */
            $count += $entry->numberOfElement;
        }
        $this->title = str_format(localize("splitByRange.range"), str_format(localize("bookword", $count), $count), $range);
        $this->parentTitle = $columnType->getTitle();
        $this->parentUri = $columnType->getUri();
    }

    /**
     * Summary of addCustomNotSetEntry
     * @param CustomColumnType $columnType
     * @return void
     */
    public function addCustomNotSetEntry($columnType)
    {
        $instance = new CustomColumn(null, localize("customcolumn.boolean.unknown"), $columnType);
        $instance->setHandler($this->handler);
        // @todo support countWithoutEntries() for CustomColumn
        $booklist = new BookList($this->request);
        $booklist->orderBy = null;
        [$result,] = $booklist->getBooksWithoutCustom($columnType, -1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
