<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Tag;

class PageTagDetail extends Page
{
    protected $className = Tag::class;

    public function InitializeContent()
    {
        $tag = Tag::getInstanceById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&t=' . $this->idGet;
            $this->getFilters($tag);
        } else {
            $this->getEntries($tag);
        }
        $this->idPage = $tag->getEntryId();
        $this->title = $tag->getTitle();
        $this->currentUri = $tag->getUri();
        $this->parentTitle = $tag->getParentTitle();
        $this->parentUri = $tag->getParentUri();
    }

    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
