<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Serie;

class PageAllSeries extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("series.title");
        $this->entryArray = Serie::getAllSeries($this->getDatabaseId());
        $this->idPage = Serie::PAGE_ID;
    }
}
