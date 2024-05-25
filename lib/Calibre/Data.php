<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\LinkEntry;

class Data
{
    public const SQL_TABLE = "data";
    public const SQL_COLUMNS = "id, name, format";
    public const SQL_LINK_TABLE = "data";
    public const SQL_LINK_COLUMN = "id";
    public const SQL_SORT = "name";
    public static string $handler = "fetch";
    /** @var int */
    public $id;
    public string $name;
    public string $format;
    public string $realFormat;
    public string $extension;
    /** @var ?Book */
    public $book;
    /** @var ?int */
    protected $databaseId;
    public bool $updateForKepub = false;

    /** @var array<string, string> */
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
        'ibooks' => 'application/x-ibooks+zip',
        'kepub' => 'application/epub+zip',
        'kobo'  => 'application/x-koboreader-ebook',
        'm4a'   => 'audio/mp4',
        'm4b'   => 'audio/mp4',
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
        'txt'   => 'text/plain',
        'wav'   => 'audio/wav',
        'wmf'   => 'image/wmf',
        'xhtml' => 'application/xhtml+xml',
        'xpgt'  => 'application/adobe-page-template+xml',
        'zip'   => 'application/zip',
    ];

    /**
     * Summary of __construct
     * @param object $post
     * @param ?Book $book
     */
    public function __construct($post, $book = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->format = $post->format;
        $this->realFormat = str_replace("ORIGINAL_", "", $post->format);
        $this->extension = strtolower($this->realFormat);
        $this->setBook($book);
    }

    /**
     * Summary of setBook
     * @param ?Book $book
     * @return void
     */
    public function setBook($book)
    {
        $this->book = $book;
        $this->databaseId = ($nullsafeVariable1 = $book) ? $nullsafeVariable1->getDatabaseId() : null;
        // this is set on book in JsonRenderer now
        if (!is_null($book) && $book->updateForKepub && $this->isEpubValidOnKobo()) {
            $this->updateForKepub = true;
        }
    }

    /**
     * Summary of isKnownType
     * @return bool
     */
    public function isKnownType()
    {
        return array_key_exists($this->extension, static::$mimetypes);
    }

    /**
     * Summary of getMimeType
     * @return bool|string
     */
    public function getMimeType()
    {
        $result = "application/octet-stream";
        if ($this->isKnownType()) {
            return static::$mimetypes [$this->extension];
        } elseif (function_exists('finfo_open') === true) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $result = finfo_file($finfo, $this->getLocalPath());
                finfo_close($finfo);
            }
        }
        return $result;
    }

    /**
     * Summary of isEpubValidOnKobo
     * @return bool
     */
    public function isEpubValidOnKobo()
    {
        return $this->format == "EPUB" || $this->format == "KEPUB";
    }

    /**
     * Summary of getFilename
     * @return string
     */
    public function getFilename()
    {
        return $this->name . "." . strtolower($this->format);
    }

    /**
     * Summary of getUpdatedFilename
     * @return string
     */
    public function getUpdatedFilename()
    {
        return $this->book->getAuthorsSort() . " - " . $this->book->title;
    }

    /**
     * Summary of getUpdatedFilenameEpub
     * @return string
     */
    public function getUpdatedFilenameEpub()
    {
        return $this->getUpdatedFilename() . ".epub";
    }

    /**
     * Summary of getUpdatedFilenameKepub
     * @return string
     */
    public function getUpdatedFilenameKepub()
    {
        $str = $this->getUpdatedFilename() . ".kepub.epub";
        return str_replace(
            [':', '#', '&'],
            ['-', '-', ' '],
            $str
        );
    }

    /**
     * Summary of getDataLink
     * @param string $rel
     * @param ?string $title
     * @param bool $view
     * @return LinkEntry
     */
    public function getDataLink($rel, $title = null, $view = false)
    {
        if ($rel == LinkEntry::OPDS_ACQUISITION_TYPE && Config::get('use_url_rewriting') == "1") {
            return $this->getHtmlLinkWithRewriting($title, $view);
        }

        return static::getLink($this->book, $this->extension, $this->getMimeType(), $rel, $this->getFilename(), $this->id, $title, $view);
    }

    /**
     * Summary of getHtmlLink
     * @return string
     */
    public function getHtmlLink()
    {
        return $this->getDataLink(LinkEntry::OPDS_ACQUISITION_TYPE)->href;
    }

    /**
     * Summary of getViewHtmlLink
     * @return string
     */
    public function getViewHtmlLink()
    {
        return $this->getDataLink(LinkEntry::OPDS_ACQUISITION_TYPE, null, true)->href;
    }

    /**
     * Summary of getLocalPath
     * @return string
     */
    public function getLocalPath()
    {
        return $this->book->path . "/" . $this->getFilename();
    }

    /**
     * Summary of getHtmlLinkWithRewriting
     * @param ?string $title
     * @param bool $view
     * @return LinkEntry
     */
    public function getHtmlLinkWithRewriting($title = null, $view = false)
    {
        $database = "";
        if (!is_null($this->databaseId)) {
            $database = $this->databaseId . "/";
        }

        $prefix = "download";
        if ($view) {
            $prefix = "view";
        }
        $href = $prefix . "/" . $this->id . "/" . $database;

        // this is set on book in JsonRenderer now
        if ($this->updateForKepub) {
            $href .= rawurlencode($this->getUpdatedFilenameKepub());
        } else {
            $href .= rawurlencode($this->getFilename());
        }
        return new LinkEntry(Route::url($href), $this->getMimeType(), LinkEntry::OPDS_ACQUISITION_TYPE, $title);
    }

    /**
     * Summary of getDataByBook
     * @param mixed $book
     * @return array<Data>
     */
    public static function getDataByBook($book)
    {
        return Book::getDataByBook($book);
    }

    /**
     * Summary of getLink
     * @param Book $book
     * @param string $type
     * @param string $mime
     * @param string $rel
     * @param string $filename
     * @param ?int $idData
     * @param ?string $title
     * @param bool $view
     * @return LinkEntry
     */
    public static function getLink($book, $type, $mime, $rel, $filename, $idData, $title = null, $view = false)
    {
        if (!empty(Config::get('calibre_external_storage')) && str_starts_with($book->path, Config::get('calibre_external_storage'))) {
            return new LinkEntry($book->path . "/" . rawurlencode($filename), $mime, $rel, $title);
        }
        // moved image-specific code from Data to Cover
        if (Database::useAbsolutePath($book->getDatabaseId()) ||
            ($type == "epub" && Config::get('update_epub-metadata'))) {
            $params = ['id' => $book->id, 'db' => $book->getDatabaseId()];
            if (Config::get('use_route_urls') && is_null($params['db'])) {
                $params['db'] = 0;
            }
            $params['type'] = $type;
            $params['data'] = $idData;
            if ($view) {
                $params['view'] = 1;
            }
            return new LinkEntry(Route::link(static::$handler, null, $params), $mime, $rel, $title);
        }

        return new LinkEntry(Route::url(str_replace('%2F', '/', rawurlencode($book->path . "/" . $filename))), $mime, $rel, $title);
    }
}
