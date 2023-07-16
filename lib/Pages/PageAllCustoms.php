<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\CustomColumnType;

use function SebLucas\Cops\Request\getURLParam;

class PageAllCustoms extends Page
{
    public function InitializeContent()
    {
        $customId = getURLParam("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId);

        $this->title = $columnType->getTitle();
        $this->entryArray = $columnType->getAllCustomValues();
        $this->idPage = $columnType->getAllCustomsId();
    }
}
