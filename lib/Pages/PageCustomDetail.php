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

class PageCustomDetail extends Page
{
    public function InitializeContent()
    {
        $customId = $this->request->get("custom", null);
        $custom = CustomColumn::createCustom($customId, $this->idGet, $this->getDatabaseId());
        $this->getCustomEntries($custom);
        $this->idPage = $custom->getEntryId();
        $this->title = $custom->getTitle();
        $this->parentTitle = $custom->customColumnType->getTitle();
        $this->parentUri = $custom->customColumnType->getUriAllCustoms();
    }

    public function getCustomEntries($custom)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustom($custom, $this->idGet, $this->n);
    }
}
