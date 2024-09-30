<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\LinkEntry;
use SebLucas\Cops\Output\FileResponse;

class Cover
{
    public static string $handler = "fetch";
    /** @var Book */
    public $book;
    /** @var ?int */
    protected $databaseId;
    /** @var ?string */
    public $coverFileName = null;
    /** @var ?FileResponse */
    protected $response = null;

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
     * Summary of getThumbnailCachePath
     * @param ?int $width
     * @param ?int $height
     * @param string $type
     * @return ?string
     */
    public function getThumbnailCachePath($width, $height, $type = 'jpg')
    {
        // moved some of the thumbnail cache from fetch.php to Cover
        //by default, we don't cache
        $cachePath = null;
        if (empty(Config::get('thumbnail_cache_directory'))) {
            return $cachePath;
        }

        $uuid = $this->book->uuid;
        $database = $this->databaseId;

        $cachePath = Config::get('thumbnail_cache_directory');
        //if multiple databases, add a subfolder with the database ID
        $cachePath .= !is_null($database) ? 'db-' . $database . DIRECTORY_SEPARATOR : '';
        //when there are lots of thumbnails, it's better to save files in subfolders, so if the book's uuid is
        //"01234567-89ab-cdef-0123-456789abcdef", we will save the thumbnail in .../0/12/34567-89ab-cdef-0123-456789abcdef-...
        $cachePath .= substr($uuid, 0, 1) . DIRECTORY_SEPARATOR . substr($uuid, 1, 2) . DIRECTORY_SEPARATOR;
        //check if cache folder exists or create it
        if (file_exists($cachePath) || mkdir($cachePath, 0o700, true)) {
            //we name the thumbnail from the book's uuid and it's dimensions (width and/or height)
            $thumbnailCacheName = substr($uuid, 3) . '-' . strval($width) . 'x' . strval($height) . '.' . $type;
            $cachePath = $cachePath . $thumbnailCacheName;
        } else {
            //error creating the folder, so we don't cache
            $cachePath = null;
        }

        return $cachePath;
    }

    /**
     * Summary of getThumbnail
     * @param ?int $width
     * @param ?int $height
     * @param ?string $outputfile
     * @param string $inType
     * @return bool
     */
    public function getThumbnail($width, $height, $outputfile = null, $inType = 'jpg')
    {
        if (is_null($width) && is_null($height)) {
            return false;
        }
        if (empty($this->coverFileName) || !is_file($this->coverFileName) || !is_readable($this->coverFileName)) {
            return false;
        }
        // @todo support creating (and caching) thumbnails for external cover images someday

        // -DC- Use cover file name
        //$file = $this->getCoverFilePath('jpg');
        $file = $this->coverFileName;
        // get image size
        if ($size = GetImageSize($file)) {
            $w = $size[0];
            $h = $size[1];
            //set new size
            if (!is_null($width)) {
                $nw = $width;
                if ($nw >= $w) {
                    return false;
                }
                $nh = intval(($nw * $h) / $w);
            } else {
                $nh = $height;
                if ($nh >= $h) {
                    return false;
                }
                $nw = intval(($nh * $w) / $h);
            }
        } else {
            return false;
        }

        // Draw the image
        if ($inType == 'png') {
            $src_img = imagecreatefrompng($file);
        } else {
            $src_img = imagecreatefromjpeg($file);
        }
        $dst_img = imagecreatetruecolor($nw, $nh);
        if (!imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $nw, $nh, $w, $h)) {
            return false;
        }
        //if we don't cache the thumbnail, this already returns the image data
        if (is_null($outputfile)) {
            $mimetype = ($inType == 'png') ? 'image/png' : 'image/jpeg';
            // use cache control here
            $this->response->setHeaders($mimetype, 0);
            $this->response->sendHeaders();
        }
        if ($inType == 'png') {
            if (!imagepng($dst_img, $outputfile, 9)) {
                return false;
            }
        } else {
            if (!imagejpeg($dst_img, $outputfile, 80)) {
                return false;
            }
        }
        imagedestroy($src_img);
        imagedestroy($dst_img);

        return true;
    }

    /**
     * Summary of sendThumbnail
     * @param Request $request
     * @param FileResponse $response
     * @return FileResponse
     */
    public function sendThumbnail($request, $response)
    {
        $type = $request->get('type', 'jpg');
        $width = $request->get('width');
        $height = $request->get('height');
        $thumb = $request->get('thumb');
        if (!empty($thumb) && empty($height)) {
            $height = match ($thumb) {
                "opds2" => intval(Config::get('opds_thumbnail_height')) * 2,
                "opds" => intval(Config::get('opds_thumbnail_height')),
                "html2" => intval(Config::get('html_thumbnail_height')) * 2,
                "html" => intval(Config::get('html_thumbnail_height')),
                default => intval($thumb),
            };
        }
        $mime = ($type == 'jpg') ? 'image/jpeg' : 'image/png';
        $file = $this->coverFileName;

        $cachePath = $this->getThumbnailCachePath($width, $height, $type);

        if ($cachePath !== null && file_exists($cachePath)) {
            //return the already cached thumbnail
            $response->setHeaders($mime, 0);
            return $response->setFile($cachePath, true);
        }

        $this->response = $response;
        if ($this->getThumbnail($width, $height, $cachePath, $type)) {
            //if we don't cache the thumbnail, imagejpeg() in $cover->getThumbnail() already return the image data
            if ($cachePath === null) {
                // The cover had to be resized
                // tell response it's already sent
                $this->response->isSent(true);
                return $this->response;
            }
            //return the just cached thumbnail
            $response->setHeaders($mime, 0);
            return $response->setFile($cachePath, true);
        }

        $response->setHeaders($mime, 0);
        return $response->setFile($file);
    }

    /**
     * Summary of getCoverUri
     * @return ?string
     */
    public function getCoverUri()
    {
        $link = $this->getCoverLink();
        if ($link) {
            return $link->hrefXhtml();
        }
        return null;
    }

    /**
     * Summary of getCoverLink
     * @return ?LinkEntry
     */
    public function getCoverLink()
    {
        if ($this->coverFileName) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', LinkEntry::OPDS_IMAGE_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            if (!empty(Config::get('calibre_database_field_cover')) && str_contains($this->coverFileName, '://')) {
                return new LinkEntry(
                    $this->coverFileName,
                    $mime,
                    LinkEntry::OPDS_IMAGE_TYPE
                );
            } elseif (!empty(Config::get('calibre_external_storage')) && str_starts_with($this->coverFileName, (string) Config::get('calibre_external_storage'))) {
                return new LinkEntry(
                    $this->coverFileName,
                    $mime,
                    LinkEntry::OPDS_IMAGE_TYPE
                );
            }
            $file = 'cover.' . $ext;
            // moved image-specific code from Data to Cover
            if (!Database::useAbsolutePath($this->databaseId)) {
                return new LinkEntry(
                    Route::path(str_replace('%2F', '/', rawurlencode($this->book->path . "/" . $file))),
                    $mime,
                    LinkEntry::OPDS_IMAGE_TYPE
                );
            }
            $params = ['id' => $this->book->id, 'db' => $this->databaseId];
            if (Config::get('use_route_urls') && is_null($params['db'])) {
                $params['db'] = 0;
            }
            if ($ext != 'jpg') {
                $params['type'] = $ext;
            }
            return new LinkEntry(
                Route::link(static::$handler, null, $params),
                $mime,
                LinkEntry::OPDS_IMAGE_TYPE
            );
        }

        return null;
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
            return $link->hrefXhtml();
        }
        return null;
    }

    /**
     * Summary of getThumbnailLink
     * @param string $thumb
     * @param bool $useDefault
     * @return ?LinkEntry
     */
    public function getThumbnailLink($thumb, $useDefault = true)
    {
        if (Config::get('thumbnail_handling') != "1" &&
            !empty(Config::get('thumbnail_handling'))) {
            $fileName = Config::get('thumbnail_handling');
            $ext = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            return new LinkEntry(
                Route::path($fileName),
                $mime,
                LinkEntry::OPDS_THUMBNAIL_TYPE
            );
        }

        if ($this->coverFileName) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', LinkEntry::OPDS_THUMBNAIL_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            // @todo support creating (and caching) thumbnails for external cover images someday
            if (!empty(Config::get('calibre_database_field_cover')) && str_contains($this->coverFileName, '://')) {
                return new LinkEntry(
                    $this->coverFileName,
                    $mime,
                    LinkEntry::OPDS_THUMBNAIL_TYPE
                );
            } elseif (!empty(Config::get('calibre_external_storage')) && str_starts_with($this->coverFileName, (string) Config::get('calibre_external_storage'))) {
                return new LinkEntry(
                    $this->coverFileName,
                    $mime,
                    LinkEntry::OPDS_THUMBNAIL_TYPE
                );
            }
            //$file = 'cover.' . $ext;
            // moved image-specific code from Data to Cover
            $params = ['id' => $this->book->id, 'db' => $this->databaseId];
            if (Config::get('use_route_urls') && is_null($params['db'])) {
                $params['db'] = 0;
            }
            if ($ext != 'jpg') {
                $params['type'] = $ext;
            }
            if (Config::get('thumbnail_handling') != "1") {
                $params['thumb'] = $thumb;
            }
            return new LinkEntry(
                Route::link(static::$handler, null, $params),
                $mime,
                LinkEntry::OPDS_THUMBNAIL_TYPE
            );
        }

        if ($useDefault) {
            return $this->getDefaultLink();
        }
        return null;
    }

    /**
     * Summary of getDefaultLink
     * @return ?LinkEntry
     */
    public function getDefaultLink()
    {
        if (!empty(Config::get('thumbnail_default'))) {
            $ext = strtolower(pathinfo((string) Config::get('thumbnail_default'), PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            return new LinkEntry(
                Route::path(Config::get('thumbnail_default')),
                $mime,
                LinkEntry::OPDS_THUMBNAIL_TYPE
            );
        }
        return null;
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
