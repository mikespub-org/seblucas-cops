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

class PageAllBooksLetter extends Page
{
    protected string $className = Book::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        // this would be the first letter - override here
        $this->idGet = $this->request->get('id', null, '/^\w$/');
        $this->getEntries();
        $this->idPage = Book::getEntryIdByLetter($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByLetter.letter"), str_format(localize("bookword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("allbooks.title");
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = Route::page(Book::PAGE_ALL, $filterParams);
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByFirstLetter($this->idGet, $this->n);
        $this->sorted = $booklist->orderBy ?? Book::SQL_SORT;
    }
}
