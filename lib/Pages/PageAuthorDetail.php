<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BookList;

class PageAuthorDetail extends Page
{
    public function InitializeContent()
    {
        $author = Author::getAuthorById($this->idGet, $this->getDatabaseId());
        $this->idPage = $author->getEntryId();
        $this->title = $author->name;  // not by getTitle() = $author->sort here
        $this->parentTitle = localize("authors.title");
        $this->parentUri = $author->getParentUri();
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByAuthor($this->idGet, $this->n);
    }
}
