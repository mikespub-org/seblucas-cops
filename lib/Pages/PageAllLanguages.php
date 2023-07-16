<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Language;

use function SebLucas\Cops\Language\localize;

class PageAllLanguages extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("languages.title");
        $this->entryArray = Language::getAllLanguages();
        $this->idPage = Language::PAGE_ID;
    }
}
