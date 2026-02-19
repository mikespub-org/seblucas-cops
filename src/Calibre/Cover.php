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
use SebLucas\Cops\Handlers\ZipFsHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\LinkImage;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\ImageResponse;

class Cover
{
    use HasRouteTrait;

    public const ROUTE_COVER = "fetch-cover";
    public const ROUTE_THUMB = "fetch-thumb";
    public const ROUTE_IMAGE = "fetch-image";
    public const ROUTE_ZIPFS = "zipfs-format";

    /** @var Book */
    public $book;
    /** @var ?int */
    protected $databaseId;
    /** @var ?string */
    public $coverFileName = null;

    /**
     * Summary of __construct
     * @param Book $book
     * @param ?int $database
     */
    public function __construct($book, $database = null)
    {
        $this->book = $book;
        if ($book->hasCover) {
            $this->coverFileName = $book->getCoverFileName();
        }
        $this->databaseId = $database ?? $book->getDatabaseId();
        $this->setHandler(FetchHandler::class);
    }

    /**
     * Summary of checkDatabaseFieldCover
     * @param string $fileName
     * @return ?string
     */
    public function checkDatabaseFieldCover($fileName)
    {
        if (!empty($fileName) && str_contains($fileName, '://')) {
            $this->coverFileName = $fileName;
            return $this->coverFileName;
        }
        $imgDirectory = Database::getImgDirectory($this->databaseId);
        $this->coverFileName = $fileName;
        if (!file_exists($this->coverFileName)) {
            $this->coverFileName = null;
        }
        if (empty($this->coverFileName)) {
            $this->coverFileName = sprintf('%s%s', $imgDirectory, $fileName);
            if (!file_exists($this->coverFileName)) {
                $this->coverFileName = null;
            }
        }
        if (empty($this->coverFileName)) {
            // Try with the epub file name
            $data = $this->book->getDataFormat('EPUB');
            if ($data) {
                // this won't work for Calibre directories due to missing (book->id) in path here
                $this->coverFileName = sprintf('%s%s/%s', $imgDirectory, $data->name, $fileName);
                if (!file_exists($this->coverFileName)) {
                    $this->coverFileName = null;
                }
                if (empty($this->coverFileName)) {
                    $this->coverFileName = sprintf('%s%s.jpg', $imgDirectory, $data->name);
                    if (!file_exists($this->coverFileName)) {
                        $this->coverFileName = null;
                    }
                }
            }
        }
        return $this->coverFileName;
    }

    /**
     * Summary of checkCoverFilePath
     * @return ?string
     */
    public function checkCoverFilePath()
    {
        $cover = $this->book->getCoverFilePath("jpg");
        if (!empty($cover) && !empty(Config::get('calibre_external_storage'))) {
            $this->coverFileName = $cover;
            return $this->coverFileName;
        }
        if ($cover === false || !file_exists($cover)) {
            $cover = $this->book->getCoverFilePath("png");
        }
        if ($cover === false || !file_exists($cover)) {
            $this->coverFileName = null;
        } else {
            $this->coverFileName = $cover;
        }
        return $this->coverFileName;
    }

    /**
     * Summary of sendImage
     * @param ?ImageResponse $image
     * @return ImageResponse
     */
    public function sendImage($image = null)
    {
        $image ??= new ImageResponse();
        $file = $this->coverFileName;
        return $image->getImageFromFile($file);
    }

    /**
     * Summary of getThumbnailCachePath
     * @param string $uuid
     * @param ?int $width
     * @param ?int $height
     * @param string $type
     * @param ?int $database
     * @return ?string
     * @deprecated 4.3.0 use ImageResponse::getCachePath() instead
     */
    public function getThumbnailCachePath($uuid, $width, $height, $type = 'jpg', $database = null)
    {
        // moved some of the thumbnail cache from fetch.php to Cover
        return ImageResponse::getCachePath($uuid, $width, $height, $type, $database);
    }

    /**
     * Summary of getThumbnail
     * @param string $file
     * @param ?int $width
     * @param ?int $height
     * @param ?string $outputfile
     * @param string $inType
     * @return bool
     * @deprecated 4.3.0 use ImageResponse::generateThumbnail() instead
     */
    public function getThumbnail($file, $width, $height, $outputfile = null, $inType = 'jpg')
    {
        $image = new ImageResponse();
        $image->type = $inType;
        $image->width = $width;
        $image->height = $height;
        return $image->generateThumbnail($file, $outputfile);
    }

    /**
     * Summary of getThumbnailHeight
     * @param string $thumb
     * @return int
     * @deprecated 4.3.0 use ImageResponse::getThumbnailHeight() instead
     */
    public function getThumbnailHeight($thumb)
    {
        return ImageResponse::getThumbnailHeight($thumb);
    }

    /**
     * Summary of sendThumbnail
     * @param Request $request
     * @param ?ImageResponse $image
     * @return ImageResponse
     */
    public function sendThumbnail($request, $image = null)
    {
        $image ??= new ImageResponse();
        // @todo support creating (and caching) thumbnails for external cover images someday
        $file = $this->coverFileName;
        $uuid = $this->book->uuid;
        $database = $this->databaseId;
        $image->setRequest($request);
        $image->setSource($uuid, $file, filemtime($file), $database);

        $cacheFile = $image->checkCache();
        if ($cacheFile instanceof ImageResponse) {
            return $cacheFile;
        }
        return $image->getThumbFromFile($file, $cacheFile);
    }

    /**
     * Summary of getCoverUri
     * @return ?string
     */
    public function getCoverUri()
    {
        $link = $this->getCoverLink();
        if ($link) {
            return $link->getUri();
        }
        return null;
    }

    /**
     * Summary of getCoverLink
     * @return ?LinkImage
     */
    public function getCoverLink()
    {
        if (empty($this->coverFileName)) {
            return null;
        }

        if (isset($this->book->folderId)) {
            return $this->getFolderLink();
        }

        // -DC- Use cover file name
        $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
        $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
        if (!empty(Config::get('calibre_database_field_cover')) && str_contains($this->coverFileName, '://')) {
            $href = $this->coverFileName;
            return new LinkImage(
                $href,
                $mime,
                LinkImage::OPDS_IMAGE_TYPE
                // no filepath here
            );
        } elseif ($this->isExternal()) {
            $href = $this->coverFileName;
            return new LinkImage(
                $href,
                $mime,
                LinkImage::OPDS_IMAGE_TYPE
                // no filepath here
            );
        }
        $file = 'cover.' . $ext;
        $filePath = $this->book->path . "/" . $file;
        if (!Database::useAbsolutePath($this->databaseId)) {
            $urlPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            $href = fn() => $this->getPath($urlPath);
            return new LinkImage(
                $href,
                $mime,
                LinkImage::OPDS_IMAGE_TYPE,
                null,
                $filePath
            );
        }
        $params = ['id' => $this->book->id, 'db' => $this->databaseId];
        $params['db'] ??= 0;
        if ($ext != 'jpg') {
            $params['type'] = $ext;
        }
        $href = fn() => $this->getRoute(self::ROUTE_COVER, $params);
        return new LinkImage(
            $href,
            $mime,
            LinkImage::OPDS_IMAGE_TYPE,
            null,
            $filePath
        );
    }

    /**
     * Summary of getThumbnailUri
     * @param string $thumb
     * @param bool $useDefault
     * @return ?string
     */
    public function getThumbnailUri($thumb, $useDefault = true)
    {
        $link = $this->getThumbnailLink($thumb, $useDefault);
        if ($link) {
            return $link->getUri();
        }
        return null;
    }

    /**
     * Summary of getThumbnailLink
     * @param string $thumb
     * @param bool $useDefault
     * @return ?LinkImage
     */
    public function getThumbnailLink($thumb, $useDefault = true)
    {
        if (Config::get('thumbnail_handling') != "1"
            && !empty(Config::get('thumbnail_handling'))) {
            return $this->getDefaultLink(Config::get('thumbnail_handling'));
        }

        if (empty($this->coverFileName)) {
            if ($useDefault) {
                return $this->getDefaultLink();
            }
            return null;
        }

        if (isset($this->book->folderId)) {
            return $this->getFolderLink($thumb);
        }

        // -DC- Use cover file name
        $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
        $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
        // @todo support creating (and caching) thumbnails for external cover images someday
        if (!empty(Config::get('calibre_database_field_cover')) && str_contains($this->coverFileName, '://')) {
            $href = $this->coverFileName;
            return new LinkImage(
                $href,
                $mime,
                LinkImage::OPDS_THUMBNAIL_TYPE
                // no filepath here
            );
        } elseif ($this->isExternal()) {
            $href = $this->coverFileName;
            return new LinkImage(
                $href,
                $mime,
                LinkImage::OPDS_THUMBNAIL_TYPE
                // no filepath here
            );
        }
        //$file = 'cover.' . $ext;
        // moved image-specific code from Data to Cover
        $params = ['id' => $this->book->id, 'db' => $this->databaseId];
        $params['db'] ??= 0;
        if ($ext != 'jpg') {
            $params['type'] = $ext;
        }
        if (Config::get('thumbnail_handling') != "1") {
            $params['thumb'] = $thumb;
            $routeName = self::ROUTE_THUMB;
            $height = ImageResponse::getThumbnailHeight($thumb);
            // @todo get thumbnail cache path here?
            if ($thumb == "opds") {
                $uuid = $this->book->uuid;
                $database = $this->databaseId;
                $filePath = ImageResponse::getCachePath($uuid, null, $height, $ext, $database);
            } else {
                $filePath = null;
            }
        } else {
            $routeName = self::ROUTE_COVER;
            $height = null;
            $filePath = $this->coverFileName;
        }
        $href = fn() => $this->getRoute($routeName, $params);
        $link = new LinkImage(
            $href,
            $mime,
            LinkImage::OPDS_THUMBNAIL_TYPE,
            null,
            $filePath
        );
        return $link->setHeight($height);
    }

    /**
     * Summary of getDefaultLink
     * @param ?string $filePath
     * @return ?LinkImage
     */
    public function getDefaultLink($filePath = null)
    {
        $filePath ??= (string) Config::get('thumbnail_default');
        if (empty($filePath)) {
            return null;
        }
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
        $href = fn() => $this->getPath($filePath);
        return new LinkImage(
            $href,
            $mime,
            LinkImage::OPDS_THUMBNAIL_TYPE,
            null,
            $filePath
        );
    }

    /**
     * Summary of isExternal
     * @return bool
     */
    public function isExternal()
    {
        if (empty($this->coverFileName)) {
            return false;
        }
        if (!empty(Config::get('calibre_external_storage')) && str_starts_with($this->coverFileName, (string) Config::get('calibre_external_storage'))) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getFolderPath
     * @return string
     */
    public function getFolderPath()
    {
        if (!empty($this->book->folderId)) {
            return $this->book->folderId . '/' . $this->book->getTitle() . '.jpg';
        }
        return $this->book->getTitle() . '.jpg';
    }

    /**
     * Summary of getFolderLink with real cover.jpg file in same directory
     * @param string $thumb default 'full' for size here (see url format)
     * @return LinkImage
     */
    public function getFolderLink($thumb = 'full')
    {
        $params = [];
        $params['path'] = $this->getFolderPath();
        $params['size'] = $thumb;
        $routeName = self::ROUTE_IMAGE;
        $href = fn() => $this->getRoute($routeName, $params);
        $mime = 'image/jpeg';
        $rel = ($thumb == 'full') ? LinkImage::OPDS_IMAGE_TYPE : LinkImage::OPDS_THUMBNAIL_TYPE;
        return new LinkImage(
            $href,
            $mime,
            $rel
            // no filepath here
        );
    }

    /**
     * Summary of getFolderDataLink with fake cover.jpg in data file (.cbz)
     * @param Data $data
     * @param string $thumb default empty '' for size here (see url format)
     * @return LinkImage
     */
    public static function getFolderDataLink($data, $thumb = '')
    {
        $params = [];
        $params['path'] = $data->getFolderPath();
        $params['comp'] = EPubReader::COVER_FILE;  // use fixed value here
        $params['size'] = $thumb;
        $routeName = self::ROUTE_ZIPFS;
        $href = fn() => ZipFsHandler::route($routeName, $params);
        $mime = 'image/jpeg';
        $rel = ($thumb == '') ? LinkImage::OPDS_IMAGE_TYPE : LinkImage::OPDS_THUMBNAIL_TYPE;
        return new LinkImage(
            $href,
            $mime,
            $rel
            // no filepath here
        );
    }

    /**
     * Summary of findCoverFileName
     * @param Book $book
     * @param object $line
     * @return ?string
     */
    public static function findCoverFileName($book, $line)
    {
        // -DC- Use cover file name
        $coverFileName = null;
        $cover = new Cover($book);
        if (!empty(Config::get('calibre_database_field_cover'))) {
            $field = Config::get('calibre_database_field_cover');
            $coverFileName = $cover->checkDatabaseFieldCover($line->{$field});
        }
        // Else try with default cover file name
        if (empty($coverFileName)) {
            $coverFileName = $cover->checkCoverFilePath();
        }
        return $coverFileName;
    }
}
