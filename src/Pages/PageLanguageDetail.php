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
use SebLucas\Cops\Calibre\Language;

class PageLanguageDetail extends PageWithDetail
{
    protected $className = Language::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        /** @var Language $instance */
        $instance = Language::getInstanceById($this->idGet, $this->getDatabaseId());
        $instance->setHandler($this->handler);
        if ($this->request->get('filter')) {
            $this->filterParams = [Language::URL_PARAM => $this->idGet];
            $this->getFilters($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
    }

    /**
     * Summary of getEntries
     * @param Language $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
