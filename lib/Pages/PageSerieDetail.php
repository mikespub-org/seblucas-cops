<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Serie;

class PageSerieDetail extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $serie = Serie::getSerieById($this->idGet, $this->getDatabaseId());
        $this->idPage = $serie->getEntryId();
        $this->title = $serie->getTitle();
        $this->parentTitle = localize("series.title");
        $this->parentUri = $serie->getParentUri();
    }

    public function getEntries()
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksBySeries($this->idGet, $this->n);
    }
}
