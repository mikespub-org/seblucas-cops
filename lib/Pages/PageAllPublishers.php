<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\BaseList;

class PageAllPublishers extends Page
{
    protected string $className = Publisher::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Publisher::PAGE_ID;
        $this->title = localize("publishers.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        $this->entryArray = $baselist->getRequestEntries($this->n);
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
    }
}
