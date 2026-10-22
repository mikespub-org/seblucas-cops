<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\PageId;

/**
 * Generic book shelf containing list of book ids per database (WIP)
 */
class BookShelf extends Base
{
    public const PAGE_ID = PageId::ALL_SHELVES_ID;
    public const PAGE_ALL = PageId::ALL_SHELVES;
    public const PAGE_DETAIL = PageId::SHELF_DETAIL;
    public const ROUTE_ALL = "page-shelves";
    public const ROUTE_DETAIL = "page-shelf";
    public const URL_PARAM = "shelf";

    /** @var ?array<int, array<int>> */
    public $bookIdList = null;

    /**
     * Summary of getIdListForShelf
     * @param string $shelfId
     * @return ?array<int, array<int>>
     */
    public static function getIdListForShelf($shelfId)
    {
        // @todo get idlist for shelfId
        return [];
    }
}
