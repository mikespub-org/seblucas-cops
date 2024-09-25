<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;

class PageRecentBooks extends Page
{
    public const PAGE_ID = PageId::ALL_RECENT_BOOKS_ID;
    //protected string $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = static::PAGE_ID;
        $this->title = localize("recent.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request);
        $this->entryArray = $booklist->getAllRecentBooks();
        $this->sorted = $booklist->orderBy ?? "timestamp desc";
    }
}
