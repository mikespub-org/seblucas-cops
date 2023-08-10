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
use SebLucas\Cops\Model\Entry;

class PageAllCustoms extends Page
{
    public function InitializeContent()
    {
        global $config;
        $customId = $this->request->get("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId, $this->getDatabaseId());

        $this->idPage = $columnType->getAllCustomsId();
        $this->title = $columnType->getTitle();
        $this->getCustomEntries($columnType);
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("custom", $config['cops_show_not_set_filter'])) {
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
        global $config;
        // @todo paginate and/or split by year
        if ($config['cops_custom_date_split_year'] == 1 && $columnType instanceof CustomColumnTypeDate) {
            $this->getCustomEntriesByYear($columnType);
        } elseif ($config['cops_custom_integer_split_range'] > 0 && $columnType instanceof CustomColumnTypeInteger) {
            $this->getCustomEntriesByRange($columnType);
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
        $this->parentUri = $columnType->getUriAllCustoms();
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
        $this->parentUri = $columnType->getUriAllCustoms();
    }

    public function addCustomNotSetEntry($columnType)
    {
        $instance = new CustomColumn(null, localize("customcolumn.boolean.unknown"), $columnType);
        $booklist = new BookList($this->request);
        $booklist->orderBy = null;
        [$result,] = $booklist->getBooksWithoutCustom($columnType, -1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
