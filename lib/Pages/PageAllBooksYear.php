<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Input\Route;

class PageAllBooksYear extends Page
{
    protected string $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = Book::getEntryIdByYear($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByYear.year"), str_format(localize("bookword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("allbooks.title");
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = Route::link($this->handler, Book::PAGE_ALL, $filterParams);
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByPubYear($this->idGet, $this->n);
        $this->sorted = $booklist->orderBy ?? Book::SQL_SORT;
    }
}
