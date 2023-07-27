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

class PageAllCustoms extends Page
{
    public function InitializeContent()
    {
        global $config;
        $customId = $this->request->get("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId, $this->getDatabaseId());

        $this->idPage = $columnType->getAllCustomsId();
        $this->title = $columnType->getTitle();
        $this->entryArray = $columnType->getAllCustomValues();
        if (in_array("custom", $config['cops_show_not_set_filter'])) {
            $instance = new CustomColumn(null, localize("customcolumn.boolean.unknown"), $columnType);
            $booklist = new BookList($this->request);
            [$result,] = $booklist->getBooksWithoutCustom($columnType, -1);
            array_push($this->entryArray, $instance->getEntry(count($result)));
        }
    }
}
