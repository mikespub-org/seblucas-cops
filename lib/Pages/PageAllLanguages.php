<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\BaseList;

class PageAllLanguages extends Page
{
    protected $className = Language::class;

    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Language::PAGE_ID;
        $this->title = localize("languages.title");
    }

    public function getEntries()
    {
        $baselist = new BaseList($this->request, $this->className);
        $this->entryArray = $baselist->getRequestEntries($this->n);
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
    }
}
