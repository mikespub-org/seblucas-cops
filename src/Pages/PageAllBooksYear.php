<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Input\Route;

class PageAllBooksYear extends Page
{
    protected $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // this would be the year - override here
        $this->idGet = $this->request->getId('year');
        $this->getEntries();
        $this->idPage = Book::getEntryIdByYear($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByYear.year"), str_format(localize("bookword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("allbooks.title");
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = $this->getRoute(Book::ROUTE_ALL, $filterParams);
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
