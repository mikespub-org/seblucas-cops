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
    protected $className = Serie::class;

    public function InitializeContent()
    {
        $serie = Serie::getSerieById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&s=' . $this->idGet;
            $this->getFilters($serie);
        } else {
            $this->getEntries($serie);
        }
        $this->idPage = $serie->getEntryId();
        $this->title = $serie->getTitle();
        $this->currentUri = $serie->getUri();
        $this->parentTitle = $serie->getParentTitle();
        $this->parentUri = $serie->getParentUri();
    }

    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "series_index";
    }
}
