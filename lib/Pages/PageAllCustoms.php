<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\CustomColumnType;

class PageAllCustoms extends Page
{
    public function InitializeContent()
    {
        $customId = $this->request->get("custom", null);
        $columnType = CustomColumnType::createByCustomID($customId, $this->getDatabaseId());

        $this->idPage = $columnType->getAllCustomsId();
        $this->title = $columnType->getTitle();
        $this->entryArray = $columnType->getAllCustomValues();
    }
}
