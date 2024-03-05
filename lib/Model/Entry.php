<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\PageId;

class Entry
{
    public string $title;
    public string $id;
    public string $content;
    /** @var string|int */
    public $numberOfElement;
    public string $contentType;
    /** @var array<LinkEntry|LinkFeed> */
    public $linkArray;
    /** @var ?int */
    public $localUpdated;
    public string $className;
    /** @var ?int */
    protected static $updated = null;
    /** @var ?int */
    protected $databaseId;

    /** @var array<string, string> */
    public static $icons = [
        PageId::ALL_AUTHORS_ID             => 'images/author.png',
        PageId::ALL_SERIES_ID              => 'images/serie.png',
        PageId::ALL_RECENT_BOOKS_ID        => 'images/recent.png',
        PageId::ALL_TAGS_ID                => 'images/tag.png',
        PageId::ALL_LANGUAGES_ID           => 'images/language.png',
        PageId::ALL_CUSTOMS_ID             => 'images/custom.png',
        PageId::ALL_RATING_ID              => 'images/rating.png',
        "cops:books$"                    => 'images/allbook.png',
        "cops:books:letter"              => 'images/allbook.png',
        PageId::ALL_PUBLISHERS_ID          => 'images/publisher.png',
    ];

    /**
     * Summary of __construct
     * @param string $ptitle
     * @param string $pid
     * @param string $pcontent
     * @param string $pcontentType
     * @param array<LinkEntry|LinkFeed> $plinkArray
     * @param string|int|null $database
     * @param string $pclass
     * @param string|int $pcount
     */
    public function __construct($ptitle, $pid, $pcontent, $pcontentType = "text", $plinkArray = [], $database = null, $pclass = "", $pcount = 0)
    {
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
        $this->className = $pclass;
        $this->numberOfElement = $pcount;

        if (Config::get('show_icons') == 1) {
            foreach (static::$icons as $reg => $image) {
                if (preg_match("/" . $reg . "/", $pid)) {
                    array_push($this->linkArray, new LinkEntry(Route::url($image, null, ["v" => Config::VERSION]), "image/png", LinkEntry::OPDS_THUMBNAIL_TYPE));
                    break;
                }
            }
        }

        if (!is_null($database)) {
            $this->id = str_replace("cops:", "cops:" . strval($database) . ":", $this->id);
        }
    }

    /**
     * Summary of getUpdatedTime
     * @return string
     */
    public function getUpdatedTime()
    {
        if (!is_null($this->localUpdated)) {
            return date(DATE_ATOM, $this->localUpdated);
        }
        if (is_null(static::$updated)) {
            static::$updated = time();
        }
        return date(DATE_ATOM, static::$updated);
    }

    /**
     * Summary of getNavLink
     * @param string $endpoint
     * @param array<string, mixed> $extraParams
     * @return string
     */
    public function getNavLink($endpoint = "", $extraParams = [])
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkEntry|LinkFeed */

            if (!($link instanceof LinkFeed)) {
                continue;
            }

            $uri = $link->hrefXhtml($endpoint);
            if (empty($extraParams)) {
                return $uri;
            }
            $separator = '&';
            if (strpos($uri, '?') === false) {
                $separator = '?';
            }
            return $uri . $separator . http_build_query($extraParams);
        }
        return "#";
    }

    /**
     * Summary of getRelation
     * @return ?string
     */
    public function getRelation()
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkEntry|LinkFeed */

            if (!($link instanceof LinkFeed)) {
                continue;
            }

            return $link->rel;
        }
        return null;
    }

    /**
     * Summary of getThumbnail
     * @param string $endpoint
     * @return ?string
     */
    public function getThumbnail($endpoint = '')
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_THUMBNAIL_TYPE) {
                return $link->hrefXhtml($endpoint);
            }
        }
        return null;
    }

    /**
     * Summary of getImage
     * @param string $endpoint
     * @return ?string
     */
    public function getImage($endpoint = '')
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_IMAGE_TYPE) {
                return $link->hrefXhtml($endpoint);
            }
        }
        return null;
    }

    /**
     * Summary of isValidForOPDS
     * @return bool
     */
    public function isValidForOPDS()
    {
        return true;
    }
}
