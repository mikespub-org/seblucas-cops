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
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Routing\UriGenerator;

class Entry
{
    public string $title;
    public string $id;
    public string $content;
    /** @var string|int */
    public $numberOfElement;
    public string $contentType;
    /** @var array<LinkFeed|LinkResource> */
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
    /** @var array<string, LinkImage> */
    public static $images = [];

    /**
     * Summary of getIcon
     * @param string $reg
     * @param string $image
     * @return LinkImage
     */
    public static function getIcon($reg, $image)
    {
        if (isset(self::$images[$reg])) {
            return self::$images[$reg];
        }
        $href = fn() => UriGenerator::path($image, ["v" => Config::VERSION]);
        self::$images[$reg] = new LinkImage(
            $href,
            "image/png",
            LinkImage::OPDS_THUMBNAIL_TYPE,
            "icon",
            $image
        );
        return self::$images[$reg];
    }

    /**
     * Summary of __construct
     * @param string $title
     * @param string $id
     * @param string $content
     * @param string $contentType
     * @param array<LinkFeed|LinkResource> $linkArray
     * @param string|int|null $database
     * @param string $classShortName
     * @param string|int $count
     */
    public function __construct($title, $id, $content, $contentType = "text", $linkArray = [], $database = null, $classShortName = "", $count = 0)
    {
        $this->title = $title;
        $this->id = $id;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->linkArray = $linkArray;
        $this->className = $classShortName;
        $this->numberOfElement = $count;

        if (Config::get('show_icons') == 1) {
            foreach (static::$icons as $reg => $image) {
                if (preg_match("/" . $reg . "/", $id)) {
                    array_push($this->linkArray, self::getIcon($reg, $image));
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
            /** @var $link LinkFeed|LinkResource */

            if (!($link instanceof LinkFeed)) {
                continue;
            }

            $uri = $link->getUri();
            if (empty($extraParams)) {
                return $uri;
            }
            return UriGenerator::mergeUriWithParams($uri, $extraParams);
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
            /** @var $link LinkFeed|LinkResource */

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
            /** @var $link LinkFeed|LinkResource */

            if ($link instanceof LinkImage && $link->rel == LinkImage::OPDS_THUMBNAIL_TYPE) {
                return $link->getUri();
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
            /** @var $link LinkFeed|LinkResource */

            if ($link instanceof LinkImage && $link->rel == LinkImage::OPDS_IMAGE_TYPE) {
                return $link->getUri();
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
