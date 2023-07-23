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

class PageAllBooksLetter extends Page
{
    public function InitializeContent()
    {
        $this->idPage = Book::getEntryIdByLetter($this->idGet);
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByStartingLetter($this->idGet, $this->n);

        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }

        $this->title = str_format(localize("splitByLetter.letter"), str_format(localize("bookword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("allbooks.title");
        $this->parentUri = "?page=".Book::PAGE_ALL;
    }
}
