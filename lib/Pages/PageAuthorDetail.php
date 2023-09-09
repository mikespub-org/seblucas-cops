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

class PageAuthorDetail extends Page
{
    protected string $className = Author::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        /** @var Author $instance */
        $instance = Author::getInstanceById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&a=' . $this->idGet;
            $this->getFilters($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->name;  // not by getTitle() = $instance->sort here
        $this->currentUri = $instance->getUri();
        $this->parentTitle = $instance->getParentTitle();
        $this->parentUri = $instance->getParentUri();
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
    }

    /**
     * Summary of getExtra
     * @param Author $instance
     * @return void
     */
    public function getExtra($instance = null)
    {
        if (!is_null($instance) && !empty($instance->link)) {
            $this->extra = [
                "title" => localize("extra.title"),
                "link" => $instance->link,
                "content" => null,
            ];
        }
    }
}
