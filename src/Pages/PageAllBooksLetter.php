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
use InvalidArgumentException;

class PageAllBooksLetter extends Page
{
    protected $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // this would be the first letter - override here + allow punctuation & symbol too for book titles
        $this->idGet = $this->request->get('letter', null, '/^[\p{L}\p{N}\p{P}\p{S}]$/u');
        if (is_null($this->idGet)) {
            throw new InvalidArgumentException('Invalid Letter');
        }
        $this->getEntries();
        $this->idPage = Book::getEntryIdByLetter($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByLetter.letter"), str_format(localize("bookword", $count), $count), $this->idGet);
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
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByFirstLetter($this->idGet, $this->n);
        $this->sorted = $booklist->orderBy ?? Book::SQL_SORT;
    }
}
