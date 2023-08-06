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

class PageCustomDetail extends Page
{
    public function InitializeContent()
    {
        $customId = $this->request->get("custom", null);
        $custom = CustomColumn::createCustom($customId, $this->idGet, $this->getDatabaseId());
        $this->idPage = $custom->getEntryId();
        $this->title = $custom->getTitle();
        $this->getCustomEntries($custom->customColumnType);
        $this->parentTitle = $custom->customColumnType->getTitle();
        $this->parentUri = $custom->customColumnType->getUriAllCustoms();
    }

    /**
     * Summary of getCustomEntries
     * @param CustomColumnType $columnType
     * @return void
     */
    public function getCustomEntries($columnType)
    {
        $booklist = new BookList($this->request);
        if (empty($this->idGet)) {
            if ($columnType instanceof CustomColumnTypeDate) {
                // if we use $columnType::PAGE_DETAIL in PageAllCustoms, otherwise see PageAllCustoms
                $year = $this->request->get("year", null, $columnType::GET_PATTERN);
                if (!empty($year)) {
                    [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustomYear($columnType, $year, $this->n);
                    $this->title = $year;
                    $this->sorted = $booklist->orderBy ?? "value";
                    return;
                }
            }
            if ($columnType instanceof CustomColumnTypeInteger) {
                // if we use $columnType::PAGE_DETAIL in PageAllCustoms, otherwise see PageAllCustoms
                $range = $this->request->get("range", null, $columnType::GET_PATTERN);
                if (!empty($range)) {
                    [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustomRange($columnType, $range, $this->n);
                    $this->title = $range;
                    $this->sorted = $booklist->orderBy ?? "value";
                    return;
                }
            }
        }
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustom($columnType, $this->idGet, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
