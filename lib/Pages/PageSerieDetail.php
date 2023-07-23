<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Serie;

class PageSerieDetail extends Page
{
    public function InitializeContent()
    {
        $serie = Serie::getSerieById($this->idGet, $this->getDatabaseId());
        $this->idPage = $serie->getEntryId();
        $this->title = $serie->getTitle();
        $this->parentTitle = localize("series.title");
        $this->parentUri = $serie->getParentUri();
        [$this->entryArray, $this->totalNumber] = Book::getBooksBySeries($this->idGet, $this->n, $this->getDatabaseId());
    }
}
