<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BookList;

class PageAuthorDetail extends PageWithDetail
{
    protected string $className = Author::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        /** @var Author $instance */
        $instance = Author::getInstanceById($this->idGet, $this->getDatabaseId());
        $instance->setHandler($this->handler);
        if ($this->request->get('filter')) {
            $this->filterParams = [Author::URL_PARAM => $this->idGet];
            $this->getFilters($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
        $this->title = $instance->name;  // not by getTitle() = $instance->sort here
    }

    /**
     * Summary of getEntries
     * @param Author $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "series desc";
        $this->getExtra($instance);
        // add author series as extra info
        $this->addExtraSeries($instance);
    }

    /**
     * Summary of addSeries
     * @param Author $instance
     * @return void
     */
    public function addExtraSeries($instance = null)
    {
        $series = $instance->getSeries();
        if (empty($series)) {
            return;
        }
        if (empty($this->extra)) {
            $this->extra = [];
        }
        $this->extra["series"] = $series;
    }
}
