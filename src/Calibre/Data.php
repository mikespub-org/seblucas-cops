<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\LinkEntry;
use SebLucas\Cops\Output\Response;

class Data
{
    public const ROUTE_DATA = "fetch-data";
    public const ROUTE_INLINE = "fetch-inline";
    public const SQL_TABLE = "data";
    public const SQL_COLUMNS = "id, name, format";
    public const SQL_LINK_TABLE = "data";
    public const SQL_LINK_COLUMN = "id";
    public const SQL_SORT = "name";
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
    /** @var class-string */
    protected $handler;
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
        'css'   => 'text/css',
        'djv'   => 'image/vnd.djvu',
        'djvu'  => 'image/vnd.djvu',
        'doc'   => 'application/msword',
        'epub'  => 'application/epub+zip',
        'fb2'   => 'text/fb2+xml',
        'gif'   => 'image/gif',
        'ibooks' => 'application/x-ibooks+zip',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
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
        'otf'   => 'font/otf',
        'pdb'   => 'application/vnd.palm',
        'pdf'   => 'application/pdf',
        'png'   => 'image/png',
        'prc'   => 'application/x-mobipocket-ebook',
        'rtf'   => 'application/rtf',
        'svg'   => 'image/svg+xml',
        'ttf'   => 'font/ttf',
        'tpz'   => 'application/x-topaz-ebook',
        'txt'   => 'text/plain',
        'wav'   => 'audio/wav',
        'webp'  => 'image/webp',
        'wmf'   => 'image/wmf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'application/xhtml+xml',
        'xpgt'  => 'application/adobe-page-template+xml',
        'zip'   => 'application/zip',
    ];

    /**
     * Summary of __construct
     * @param \stdClass $post
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
        $this->handler = FetchHandler::class;
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
        return array_key_exists($this->extension, self::$mimetypes);
    }

    /**
     * Summary of getMimeType
     * @return string
     */
    public function getMimeType()
    {
        if ($this->isKnownType()) {
            return self::$mimetypes[$this->extension];
        }
        $default = "application/octet-stream";
        return Response::getMimeType($this->getLocalPath()) ?? $default;
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

        return self::getLink($this->book, $this->extension, $this->getMimeType(), $rel, $this->getFilename(), $this->id, $title, $view);
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
     * @deprecated 3.1.0 use route urls instead
     * @return LinkEntry
     */
    public function getHtmlLinkWithRewriting($title = null, $view = false)
    {
        $database = "";
        if (!is_null($this->databaseId)) {
            $database = $this->databaseId . "/";
        } else {
            $database = "0/";
        }

        $prefix = "download";
        //$routeName = 'fetch-download';
        if ($view) {
            $prefix = "view";
            //$routeName = 'fetch-view';
        }
        $href = $prefix . "/" . $this->id . "/" . $database;

        // this is set on book in JsonRenderer now
        if ($this->updateForKepub) {
            $href .= rawurlencode($this->getUpdatedFilenameKepub());
        } else {
            $href .= rawurlencode($this->getFilename());
        }
        return new LinkEntry(
            Route::path($href),
            $this->getMimeType(),
            LinkEntry::OPDS_ACQUISITION_TYPE,
            $title
        );
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
        if (!empty(Config::get('calibre_external_storage')) && str_starts_with($book->path, (string) Config::get('calibre_external_storage'))) {
            return new LinkEntry(
                $book->path . "/" . rawurlencode($filename),
                $mime,
                $rel,
                $title
            );
        }
        // moved image-specific code from Data to Cover
        if (Database::useAbsolutePath($book->getDatabaseId()) ||
            ($type == "epub" && Config::get('update_epub-metadata'))) {
            $params = ['db' => $book->getDatabaseId()];
            $params['db'] ??= 0;
            $params['type'] = $type;
            $params['data'] = $idData;
            //$params['ignore'] = rawurlencode(basename($filename));
            $params['ignore'] = 'ignore';
            $routeName = self::ROUTE_DATA;
            if ($view) {
                $params['view'] = 1;
                $routeName = self::ROUTE_INLINE;
            }
            return new LinkEntry(
                FetchHandler::route($routeName, $params),
                $mime,
                $rel,
                $title
            );
        }

        return new LinkEntry(
            Route::path(str_replace('%2F', '/', rawurlencode($book->path . "/" . $filename))),
            $mime,
            $rel,
            $title
        );
    }
}
