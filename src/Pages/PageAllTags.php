<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Config;

class PageAllTags extends Page
{
    protected string $className = Tag::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = Tag::PAGE_ID;
        $this->title = localize("tags.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        $this->sorted = $this->request->getSorted("sort");
        if ($baselist->hasChildCategories()) {
            // use tag_browser_tags view here, to get the full hierarchy?
            $this->entryArray = $baselist->browseAllEntries($this->n);
        } else {
            $this->entryArray = $baselist->getRequestEntries($this->n);
        }
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("tag", Config::get('show_not_set_filter'))) {
            array_push($this->entryArray, $baselist->getWithoutEntry());
        }
    }
}
