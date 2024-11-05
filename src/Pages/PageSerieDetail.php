<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Serie;

class PageSerieDetail extends PageWithDetail
{
    protected $className = Serie::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        /** @var Serie $instance */
        $instance = Serie::getInstanceById($this->idGet, $this->getDatabaseId());
        $instance->setHandler($this->handler);
        if ($this->request->get('filter')) {
            $this->filterParams = [Serie::URL_PARAM => $this->idGet];
            $this->getFilters($instance);
            // @todo needs title_sort function in sqlite for series
            //} elseif ($this->request->get('tree')) {
            //    $this->getHierarchy($instance);
        } elseif ($this->request->get('extra')) {
            // show extra information without books
            $this->getExtra($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
        //if ($instance->hasChildCategories()) {
        //    $this->hierarchy = [
        //        "parent" => $instance->getParentEntry(),
        //        "current" => $instance->getEntry(),
        //        "children" => $instance->getChildEntries($this->request->get('tree')),
        //    ];
        //}
    }

    /**
     * Summary of getHierarchy
     * @param Serie $instance
     * @return void
     */
    public function getHierarchy($instance)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstanceOrChildren($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }

    /**
     * Summary of getEntries
     * @param ?Serie $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "series_index";
        $this->getExtra($instance);
    }
}
