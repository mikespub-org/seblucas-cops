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

class PageAllCustoms extends Page
{
    public function InitializeContent()
    {
        global $config;
        $customId = $this->request->get("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId, $this->getDatabaseId());

        $this->idPage = $columnType->getAllCustomsId();
        $this->title = $columnType->getTitle();
        // @todo paginate and/or split by year
        if ($config['cops_custom_date_split_year'] == 1 && $columnType instanceof CustomColumnTypeDate) {
            $year = $this->request->get("year", null);
            if (empty($year)) {
                $this->entryArray = $columnType->getCountByYear();
            } else {
                $this->entryArray = $columnType->getCustomValuesByYear($year);
            }
        } else {
            $this->entryArray = $columnType->getAllCustomValues();
            $this->totalNumber = $columnType->getDistinctValueCount();
        }
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("custom", $config['cops_show_not_set_filter'])) {
            $this->addCustomNotSetEntry($columnType);
        }
    }

    public function addCustomNotSetEntry($columnType)
    {
        $instance = new CustomColumn(null, localize("customcolumn.boolean.unknown"), $columnType);
        $booklist = new BookList($this->request);
        [$result,] = $booklist->getBooksWithoutCustom($columnType, -1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
