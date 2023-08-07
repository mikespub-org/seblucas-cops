<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;

class PageAllAuthors extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Author::PAGE_ID;
        $this->title = localize("authors.title");
    }

    public function getEntries()
    {
        if ($this->request->option("author_split_first_letter") == 1) {
            $this->entryArray = Author::getCountByFirstLetter($this->request, $this->getDatabaseId());
            $this->sorted = "letter";
        } else {
            $this->entryArray = Author::getRequestEntries($this->request, $this->n, $this->getDatabaseId());
            $this->totalNumber = Author::countRequestEntries($this->request, $this->getDatabaseId());
            $this->sorted = Author::SQL_SORT;
        }
    }
}
