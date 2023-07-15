<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;

class PageRecentBooks extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("recent.title");
        $this->entryArray = Book::getAllRecentBooks();
        $this->idPage = parent::ALL_RECENT_BOOKS_ID;
    }
}
