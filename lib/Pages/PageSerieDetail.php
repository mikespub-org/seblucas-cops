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
        $instance = Serie::getInstanceById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&s=' . $this->idGet;
            $this->getFilters($instance);
            // @todo needs title_sort function in sqlite for series
            //} elseif ($this->request->get('tree')) {
            //    $this->getHierarchy($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->getTitle();
        $this->currentUri = $instance->getUri();
        $this->parentTitle = $instance->getParentTitle();
        $this->parentUri = $instance->getParentUri();
    }

    public function getHierarchy($instance)
    {
        $this->entryArray = $instance->getChildCategories();
        $this->hierarchy = true;
    }

    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "series_index";
    }
}
