<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;

class PageAllBooks extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("allbooks.title");
        if ($this->request->option("titles_split_first_letter") == 1) {
            $this->entryArray = Book::getAllBooks($this->getDatabaseId(), $this->request);
        } else {
            [$this->entryArray, $this->totalNumber] = Book::getBooks($this->n, $this->getDatabaseId(), $this->getNumberPerPage(), $this->request);
        }
        $this->idPage = Book::PAGE_ID;
    }
}
