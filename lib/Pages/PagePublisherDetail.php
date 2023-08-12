<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Publisher;

class PagePublisherDetail extends Page
{
    protected $className = Publisher::class;

    public function InitializeContent()
    {
        $publisher = Publisher::getInstanceById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&p=' . $this->idGet;
            $this->getFilters($publisher);
        } else {
            $this->getEntries($publisher);
        }
        $this->idPage = $publisher->getEntryId();
        $this->title = $publisher->getTitle();
        $this->currentUri = $publisher->getUri();
        $this->parentTitle = $publisher->getParentTitle();
        $this->parentUri = $publisher->getParentUri();
    }

    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
