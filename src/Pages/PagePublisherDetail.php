<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Publisher;

class PagePublisherDetail extends PageWithDetail
{
    protected $className = Publisher::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        /** @var Publisher $instance */
        $instance = Publisher::getInstanceById($this->idGet, $this->getDatabaseId());
        $instance->setHandler($this->handler);
        if ($this->request->get('filter')) {
            $this->filterParams = [Publisher::URL_PARAM => $this->idGet];
            $this->getFilters($instance);
        } elseif ($this->request->get('extra')) {
            // show extra information without books
            $this->getExtra($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
    }

    /**
     * Summary of getEntries
     * @param Publisher $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
        $this->getExtra($instance);
    }
}
