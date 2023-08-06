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

class PageAllSeries extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Serie::PAGE_ID;
        $this->title = localize("series.title");
    }

    public function getEntries()
    {
        global $config;
        /**
        if ($this->request->hasFilter()) {
            $this->entryArray = Serie::getEntriesByFilter($this->request, $this->n, $this->getDatabaseId());
        } else {
            $this->entryArray = Serie::getAllEntries($this->n, $this->getDatabaseId());
        }
         */
        $this->entryArray = Serie::getAllSeries($this->n, $this->getDatabaseId());
        $this->totalNumber = Serie::countAllEntries($this->getDatabaseId());
        $this->sorted = Serie::SQL_SORT;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("series", $config['cops_show_not_set_filter'])) {
            $this->addNotSetEntry();
        }
    }

    public function addNotSetEntry()
    {
        $instance = new Serie((object)['id' => null, 'name' => localize("seriesword.none")], $this->getDatabaseId());
        $booklist = new BookList($this->request);
        [$result,] = $booklist->getBooksWithoutSeries(-1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
