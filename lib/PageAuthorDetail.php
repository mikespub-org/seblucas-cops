<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Book;

class PageAuthorDetail extends Page
{
    public function InitializeContent()
    {
        $author = Author::getAuthorById($this->idGet);
        $this->idPage = $author->getEntryId();
        $this->title = $author->name;
        [$this->entryArray, $this->totalNumber] = Book::getBooksByAuthor($this->idGet, $this->n);
    }
}
