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
        if ($this->request->get('filter')) {
            $this->filterUri = '&a=' . $this->idGet;
            $this->getFilters($author);
        } else {
            $this->getEntries();
        }
        $this->idPage = $author->getEntryId();
        $this->title = $author->name;  // not by getTitle() = $author->sort here
        $this->currentUri = $author->getUri();
        $this->parentTitle = $author->getParentTitle();
        $this->parentUri = $author->getParentUri();
        //$seriesArray = $author->getSeries();
    }

    public function getEntries()
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByAuthor($this->idGet, $this->n);
        $this->sorted = $booklist->orderBy ?? "series desc";
    }
}
