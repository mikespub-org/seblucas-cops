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

class PageTagDetail extends PageWithDetail
{
    protected string $className = Tag::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        /** @var Tag $instance */
        $instance = Tag::getInstanceById($this->idGet, $this->getDatabaseId());
        if ($this->request->get('filter')) {
            $this->filterUri = '&t=' . $this->idGet;
            $this->getFilters($instance);
        } elseif ($this->request->get('tree')) {
            $this->getHierarchy($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->getTitle();
        $this->currentUri = $instance->getUri();
        $this->parentTitle = $instance->getParentTitle();
        $this->parentUri = $instance->getParentUri();
        if ($instance->hasChildCategories()) {
            $this->hierarchy = [
                "parent" => $instance->getParentEntry(),
                "current" => $instance->getEntry(),
                "children" => $instance->getChildEntries($this->request->get('tree')),
            ];
        }
    }

    /**
     * Summary of getHierarchy
     * @param Tag $instance
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
     * @param ?Tag $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
