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

class PageAllBooks extends Page
{
    public function InitializeContent()
    {
        $this->idPage = Book::PAGE_ID;
        $this->title = localize("allbooks.title");
        $booklist = new BookList($this->request);
        if ($this->request->option("titles_split_first_letter") == 1) {
            $this->entryArray = $booklist->getAllBooks();
        } else {
            [$this->entryArray, $this->totalNumber] = $booklist->getBooks($this->n);
        }
    }
}
