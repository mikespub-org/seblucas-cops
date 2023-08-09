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
        $this->entryArray = Serie::getRequestEntries($this->request, $this->n, $this->getDatabaseId());
        $this->totalNumber = Serie::countRequestEntries($this->request, $this->getDatabaseId());
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
