<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
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
    /** @var ?Base */
    public $instance;

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
     * @param string $title
     * @param string $id
     * @param string $content
     * @param string $contentType
     * @param array<LinkEntry|LinkFeed> $linkArray
     * @param string|int|null $database
     * @param string $className
     * @param string|int $count
     */
    public function __construct($title, $id, $content, $contentType = "text", $linkArray = [], $database = null, $className = "", $count = 0)
    {
        $this->title = $title;
        $this->id = $id;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->linkArray = $linkArray;
        $this->className = $className;
        $this->numberOfElement = $count;

        if (Config::get('show_icons') == 1) {
            foreach (static::$icons as $reg => $image) {
                if (preg_match("/" . $reg . "/", $id)) {
                    array_push($this->linkArray, new LinkEntry(
                        Route::path($image) . "?v=" . Config::VERSION,
                        "image/png",
                        LinkEntry::OPDS_THUMBNAIL_TYPE
                    ));
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
     * @param array<string, mixed> $extraParams
     * @return string
     */
    public function getNavLink($extraParams = [])
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkEntry|LinkFeed */

            if (!($link instanceof LinkFeed)) {
                continue;
            }

            $uri = $link->hrefXhtml();
            if (empty($extraParams)) {
                return $uri;
            }
            $query = parse_url($uri, PHP_URL_QUERY);
            if (is_null($query)) {
                return $uri . '?' . http_build_query($extraParams);
            }
            // replace current params with extraParams where needed
            parse_str($query, $params);
            $params = array_replace($params, $extraParams);
            return str_replace('?' . $query, '?' . http_build_query($params), $uri);
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
     * @return ?string
     */
    public function getThumbnail()
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_THUMBNAIL_TYPE) {
                return $link->hrefXhtml();
            }
        }
        return null;
    }

    /**
     * Summary of getImage
     * @return ?string
     */
    public function getImage()
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_IMAGE_TYPE) {
                return $link->hrefXhtml();
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
