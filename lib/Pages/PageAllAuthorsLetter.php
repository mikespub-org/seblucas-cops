<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;

class PageAllAuthorsLetter extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Author::getEntryIdByLetter($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByLetter.letter"), str_format(localize("authorword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("authors.title");
        $this->parentUri = "?page=".Author::PAGE_ALL;
    }

    public function getEntries()
    {
        $this->entryArray = Author::getEntriesByFirstLetter($this->request, $this->idGet, $this->n, $this->getDatabaseId());
        $this->totalNumber = Author::countEntriesByFirstLetter($this->request, $this->idGet, $this->getDatabaseId());
        $this->sorted = $this->request->getSorted(Author::SQL_SORT);
    }
}
