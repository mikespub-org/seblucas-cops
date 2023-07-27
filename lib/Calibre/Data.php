<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Link;
use SebLucas\Cops\Output\Format;

class Data
{
    public static $endpoint = Config::ENDPOINT["fetch"];
    public $id;
    public $name;
    public $format;
    public $realFormat;
    public $extension;
    public $book;
    protected $databaseId;
    public $updateForKepub = false;

    public static $mimetypes = [
        'aac'   => 'audio/aac',
        'azw'   => 'application/x-mobipocket-ebook',
        'azw1'  => 'application/x-topaz-ebook',
        'azw2'  => 'application/x-kindle-application',
        'azw3'  => 'application/x-mobi8-ebook',
        'cbz'   => 'application/x-cbz',
        'cbr'   => 'application/x-cbr',
        'djv'   => 'image/vnd.djvu',
        'djvu'  => 'image/vnd.djvu',
        'doc'   => 'application/msword',
        'epub'  => 'application/epub+zip',
        'fb2'   => 'text/fb2+xml',
        'ibooks'=> 'application/x-ibooks+zip',
        'kepub' => 'application/epub+zip',
        'kobo'  => 'application/x-koboreader-ebook',
        'm4a'   => 'audio/mp4',
        'mobi'  => 'application/x-mobipocket-ebook',
        'mp3'   => 'audio/mpeg',
        'lit'   => 'application/x-ms-reader',
        'lrs'   => 'text/x-sony-bbeb+xml',
        'lrf'   => 'application/x-sony-bbeb',
        'lrx'   => 'application/x-sony-bbeb',
        'ncx'   => 'application/x-dtbncx+xml',
        'opf'   => 'application/oebps-package+xml',
        'otf'   => 'application/x-font-opentype',
        'pdb'   => 'application/vnd.palm',
        'pdf'   => 'application/pdf',
        'prc'   => 'application/x-mobipocket-ebook',
        'rtf'   => 'application/rtf',
        'svg'   => 'image/svg+xml',
        'ttf'   => 'application/x-font-truetype',
        'tpz'   => 'application/x-topaz-ebook',
        'wav'   => 'audio/wav',
        'wmf'   => 'image/wmf',
        'xhtml' => 'application/xhtml+xml',
        'xpgt'  => 'application/adobe-page-template+xml',
        'zip'   => 'application/zip',
    ];

    public function __construct($post, $book = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->format = $post->format;
        $this->realFormat = str_replace("ORIGINAL_", "", $post->format);
        $this->extension = strtolower($this->realFormat);
        $this->book = $book;
        $this->databaseId = $book?->getDatabaseId();
        // this is set on book in JSONRenderer now
        if ($book->updateForKepub && $this->isEpubValidOnKobo()) {
            $this->updateForKepub = true;
        }
    }

    public function isKnownType()
    {
        return array_key_exists($this->extension, self::$mimetypes);
    }

    public function getMimeType()
    {
        $result = "application/octet-stream";
        if ($this->isKnownType()) {
            return self::$mimetypes [$this->extension];
        } elseif (function_exists('finfo_open') === true) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $result = finfo_file($finfo, $this->getLocalPath());
                finfo_close($finfo);
            }
        }
        return $result;
    }

    public function isEpubValidOnKobo()
    {
        return $this->format == "EPUB" || $this->format == "KEPUB";
    }

    public function getFilename()
    {
        return $this->name . "." . strtolower($this->format);
    }

    public function getUpdatedFilename()
    {
        return $this->book->getAuthorsSort() . " - " . $this->book->title;
    }

    public function getUpdatedFilenameEpub()
    {
        return $this->getUpdatedFilename() . ".epub";
    }

    public function getUpdatedFilenameKepub()
    {
        $str = $this->getUpdatedFilename() . ".kepub.epub";
        return str_replace(
            [':', '#', '&'],
            ['-', '-', ' '],
            $str
        );
    }

    public function getDataLink($rel, $title = null, $view = false)
    {
        global $config;

        if ($rel == Link::OPDS_ACQUISITION_TYPE && $config['cops_use_url_rewriting'] == "1") {
            return $this->getHtmlLinkWithRewriting($title, $view);
        }

        return self::getLink($this->book, $this->extension, $this->getMimeType(), $rel, $this->getFilename(), $this->id, $title, null, $view);
    }

    public function getHtmlLink()
    {
        return $this->getDataLink(Link::OPDS_ACQUISITION_TYPE)->href;
    }

    public function getViewHtmlLink()
    {
        return $this->getDataLink(Link::OPDS_ACQUISITION_TYPE, null, true)->href;
    }

    public function getLocalPath()
    {
        return $this->book->path . "/" . $this->getFilename();
    }

    public function getHtmlLinkWithRewriting($title = null, $view = false)
    {
        global $config;

        $database = "";
        if (!is_null($this->databaseId)) {
            $database = $this->databaseId . "/";
        }

        $prefix = "download";
        if ($view) {
            $prefix = "view";
        }
        $href = $prefix . "/" . $this->id . "/" . $database;

        // this is set on book in JSONRenderer now
        if ($this->updateForKepub) {
            $href .= rawurlencode($this->getUpdatedFilenameKepub());
        } else {
            $href .= rawurlencode($this->getFilename());
        }
        return new Link($href, $this->getMimeType(), Link::OPDS_ACQUISITION_TYPE, $title);
    }

    public static function getDataByBook($book)
    {
        return Book::getDataByBook($book);
    }

    public static function handleThumbnailLink($urlParam, $height)
    {
        global $config;

        if (is_null($height)) {
            if (preg_match('/' . Config::ENDPOINT["feed"] . '/', $_SERVER["SCRIPT_NAME"])) {
                $height = $config['cops_opds_thumbnail_height'];
            } else {
                $height = $config['cops_html_thumbnail_height'];
            }
        }
        if ($config['cops_thumbnail_handling'] != "1") {
            $urlParam = Format::addURLParam($urlParam, "height", $height);
        }

        return $urlParam;
    }

    public static function getLink($book, $type, $mime, $rel, $filename, $idData, $title = null, $height = null, $view = false)
    {
        global $config;
        /** @var Book $book */

        $urlParam = Format::addURLParam("", "data", $idData);
        if ($view) {
            $urlParam = Format::addURLParam($urlParam, "view", 1);
        }

        if (Base::useAbsolutePath($book->getDatabaseId()) ||
            $rel == Link::OPDS_THUMBNAIL_TYPE ||
            ($type == "epub" && $config['cops_update_epub-metadata'])) {
            if ($type != "jpg") {
                $urlParam = Format::addURLParam($urlParam, "type", $type);
            }
            if ($rel == Link::OPDS_THUMBNAIL_TYPE) {
                $urlParam = self::handleThumbnailLink($urlParam, $height);
            }
            $urlParam = Format::addURLParam($urlParam, "id", $book->id);
            $urlParam = Format::addDatabaseParam($urlParam, $book->getDatabaseId());
            if ($config['cops_thumbnail_handling'] != "1" &&
                !empty($config['cops_thumbnail_handling']) &&
                $rel == Link::OPDS_THUMBNAIL_TYPE) {
                return new Link($config['cops_thumbnail_handling'], $mime, $rel, $title);
            } else {
                return new Link(self::$endpoint . '?' . $urlParam, $mime, $rel, $title);
            }
        } else {
            return new Link(str_replace('%2F', '/', rawurlencode($book->path."/".$filename)), $mime, $rel, $title);
        }
    }
}
