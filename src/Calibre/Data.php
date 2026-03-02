<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\LinkAcquisition;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Output\Response;
use Exception;

class Data
{
    use HasRouteTrait;

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
        $this->setHandler(FetchHandler::class);
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
     * Summary of getDownloadFilename
     * @return string
     */
    public function getDownloadFilename()
    {
        $filename = Config::get('download_filename', '');
        if (empty($filename)) {
            return $this->name;
        }
        return Book::replaceTemplateFields($filename, $this->book);
    }

    /**
     * Summary of sendFile
     * @param bool $inline disposition inline (default false)
     * @param ?FileResponse $response
     * @return FileResponse
     */
    public function sendFile($inline = false, $response = null)
    {
        $response ??= new FileResponse();
        $file = $this->getLocalPath();

        if ($inline) {
            $filename = '';
        } else {
            $filename = $this->getDownloadFilename() . "." . strtolower($this->format);
        }
        $response->setHeaders($this->getMimeType(), 0, $filename);
        return $response->setFile($file);
    }

    /**
     * Summary of getUpdatedFilename
     * @return string
     */
    public function getUpdatedFilename()
    {
        $filename = Config::get('download_filename', '');
        if (empty($filename)) {
            return $this->book->getAuthorsSort() . " - " . $this->book->title;
        }
        return $this->getDownloadFilename();
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
     * Summary of sendUpdatedEpub
     * @param ?bool $updateForKepub
     * @param ?FileResponse $response
     * @return FileResponse|Response
     */
    public function sendUpdatedEpub($updateForKepub = null, $response = null)
    {
        $updateForKepub ??= $this->updateForKepub;
        $response ??= new FileResponse();
        // if we want to update metadata and then use kepubify, we need to save the updated Epub first
        if ($updateForKepub && !empty(Config::get('kepubify_path'))) {
            // make a temp copy for the updated Epub file
            $tmpfile = FileResponse::getTempFile('epub');
            if (!copy($this->getLocalPath(), $tmpfile)) {
                return Response::sendError(null, 'Error: unable to copy epub file');
            }
            $filePath = $tmpfile;
        } else {
            $filePath = $this->getLocalPath();
        }

        try {
            $epub = $this->book->getUpdatedEpub($filePath);
            $filename = $this->getUpdatedFilenameEpub();
            // @checkme this is set in fetch.php now
            if ($updateForKepub) {
                $filename = $this->getUpdatedFilenameKepub();
                // @todo no cache control here!?
                $response->setHeaders($this->getMimeType(), null, basename($filename));
                // save updated Epub file and convert to kepub
                if (!empty(Config::get('kepubify_path'))) {
                    $epub->save();

                    // run kepubify on updated Epub file and send converted tmpfile
                    $tmpfile = self::runKepubify($filePath);
                    if (empty($tmpfile)) {
                        return Response::sendError(null, 'Error: failed to convert epub file');
                    }
                    return $response->setFile($tmpfile, true);
                }
                $epub->updateForKepub();
            }
            // @todo no cache control here!?
            //$response->setHeaders($data->getMimeType(), null, basename($filename));
            $sendHeaders = headers_sent() ? false : true;
            $epub->download($filename, $sendHeaders);
            // tell response it's already sent
            $response->isSent(true);
            return $response;
        } catch (Exception $e) {
            return Response::sendError(null, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Summary of sendConvertedKepub
     * @param ?FileResponse $response
     * @return FileResponse|Response
     */
    public function sendConvertedKepub($response = null)
    {
        $file = $this->getLocalPath();
        // run kepubify on original Epub file and send converted tmpfile
        if (!empty(Config::get('kepubify_path'))) {
            // @todo no cache control here!?
            $response ??= new FileResponse($this->getMimeType(), null, basename($this->getUpdatedFilenameKepub()));
            $tmpfile = self::runKepubify($file);
            if (empty($tmpfile)) {
                return Response::sendError(null, 'Error: failed to convert epub file');
            }
            return $response->setFile($tmpfile, true);
        }
        // provide kepub in name only (without update of opf properties for cover-image in Epub)
        $response ??= new FileResponse($this->getMimeType(), 0, basename($this->getUpdatedFilenameKepub()));
        return $response->setFile($file);
    }

    /**
     * Summary of runKepubify
     * @param string $filepath
     * @return string|null
     */
    public static function runKepubify($filepath)
    {
        if (empty(Config::get('kepubify_path'))) {
            return null;
        }
        $tmpfile = FileResponse::getTempFile('kepub.epub');
        $cmd = escapeshellarg((string) Config::get('kepubify_path'));
        $cmd .= ' -o ' . escapeshellarg($tmpfile);
        $cmd .= ' ' . escapeshellarg($filepath);
        exec($cmd, $output, $return);
        if ($return == 0 && file_exists($tmpfile)) {
            return $tmpfile;
        }
        return null;
    }

    /**
     * Summary of getDataLink
     * @param ?string $title
     * @param bool $view
     * @return LinkAcquisition
     */
    public function getDataLink($title = null, $view = false)
    {
        return $this->getLinkResource($title, $view);
    }

    /**
     * Summary of getHtmlLink
     * @return string
     */
    public function getHtmlLink()
    {
        return $this->getDataLink()->getUri();
    }

    /**
     * Summary of getViewHtmlLink
     * @return string
     */
    public function getViewHtmlLink()
    {
        return $this->getDataLink(null, true)->getUri();
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
     * Summary of getExternalPath
     * @return string
     */
    public function getExternalPath()
    {
        // external storage is assumed to be already url-encoded if needed
        return $this->book->path . '/' . rawurlencode($this->getFilename());
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
     * Summary of getLinkResource
     * @param ?string $title
     * @param bool $view
     * @return LinkAcquisition
     */
    public function getLinkResource($title = null, $view = false)
    {
        if ($this->book->isExternal()) {
            // external storage is assumed to be already url-encoded if needed
            $href = $this->getExternalPath();
            return new LinkAcquisition(
                $href,
                $this->getMimeType(),
                LinkAcquisition::OPDS_ACQUISITION_TYPE,
                $title,
                // no filepath here
                null
            );
        }

        $filePath = $this->getLocalPath();
        if (!empty(Config::get('download_filename'))
            || Database::useAbsolutePath($this->databaseId)
            || ($this->extension == "epub" && Config::get('update_epub-metadata'))) {
            $params = [];
            $params['db'] = $this->databaseId ?? 0;
            $params['type'] = $this->extension;
            $params['data'] = $this->id;
            // this is set on book in JsonRenderer now
            if ($this->updateForKepub) {
                $params['ignore'] = $this->getUpdatedFilename() . '.kepub';
            } else {
                $params['ignore'] = $this->getDownloadFilename();
            }
            $routeName = self::ROUTE_DATA;
            if ($view) {
                $params['view'] = 1;
                $routeName = self::ROUTE_INLINE;
            }
            $href = fn() => $this->getRoute($routeName, $params);
            return new LinkAcquisition(
                $href,
                $this->getMimeType(),
                LinkAcquisition::OPDS_ACQUISITION_TYPE,
                $title,
                $filePath
            );
        }

        $urlPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
        $href = fn() => $this->getPath($urlPath);
        return new LinkAcquisition(
            $href,
            $this->getMimeType(),
            LinkAcquisition::OPDS_ACQUISITION_TYPE,
            $title,
            $filePath
        );
    }
}
