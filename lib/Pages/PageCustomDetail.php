<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Request;

class PageCustomDetail extends Page
{
    public function InitializeContent()
    {
        $customId = $this->request->get("custom", null);
        $custom = CustomColumn::createCustom($customId, $this->idGet, $this->getDatabaseId());
        $this->idPage = $custom->getEntryId();
        $this->title = $custom->getTitle();
        [$this->entryArray, $this->totalNumber] = Book::getBooksByCustom($custom, $this->idGet, $this->n, $this->getDatabaseId());
    }
}
