<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class ImageResponse extends FileResponse
{
    public ?Request $request = null;
    public string $type = 'jpg';
    public ?string $thumb = null;
    public ?int $width = null;
    public ?int $height = null;
    public string $uuid;
    public string $name;
    public int $mtime;
    public ?int $database = null;

    /**
     * Summary of getImageFromFile
     * @param string $file
     * @return static
     */
    public function getImageFromFile($file)
    {
        $mime = static::getMimeType($file);

        $this->setHeaders($mime, 0);
        return $this->setFile($file);
    }

    /**
     * Summary of setRequest
     * @param Request $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
        $this->type = $request->get('type', 'jpg');
        $this->width = $request->get('width');
        $this->height = $request->get('height');
        $this->thumb = $request->get('thumb');
        if (!empty($this->thumb) && empty($this->height)) {
            $this->height = static::getThumbnailHeight($this->thumb);
        }
    }

    /**
     * Summary of setSource
     * @param string $uuid
     * @param string $name
     * @param int $mtime
     * @param ?int $database
     * @return static
     */
    public function setSource($uuid, $name, $mtime, $database = null)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->mtime = $mtime;
        $this->database = $database;
        return $this;
    }

    /**
     * Summary of checkCache
     * @return static|string|null
     */
    public function checkCache()
    {
        $cachePath = static::getCachePath($this->uuid, $this->width, $this->height, $this->type, $this->database);

        if ($cachePath !== null && file_exists($cachePath)) {
            $mime = ($this->type == 'jpg') ? 'image/jpeg' : 'image/png';
            // return the already cached thumbnail
            $this->setHeaders($mime, 0);
            return $this->setFile($cachePath, true);
        }

        // use dummy etag with original timestamp for cachePath null here
        if (is_null($cachePath) && (!is_null($this->width) || !is_null($this->height))) {
            $etag = '"' . md5((string) $this->mtime . '-' . $this->name . '-' . strval($this->width) . 'x' . strval($this->height) . '.' . $this->type) . '"';
            $this->addHeader('ETag', $etag);
            $modified = gmdate('D, d M Y H:i:s \G\M\T', $this->mtime);
            $this->addHeader('Last-Modified', $modified);
            // no encoding here: $this->addHeader('Vary', 'Accept-Encoding');
            if ($this->isNotModified($this->request)) {
                return $this->setNotModified();
            }
        }

        return $cachePath;
    }

    /**
     * Summary of getThumbFromFile
     * @param string $file
     * @param ?string $cacheFile
     * @return static
     */
    public function getThumbFromFile($file, $cacheFile)
    {
        $result = $this->generateThumbnail($file, $cacheFile);
        if ($result) {
            return $this->setThumbResult($result, $cacheFile);
        }

        $mime = static::getMimeType($file);
        $this->setHeaders($mime, 0);
        return $this->setFile($file);
    }

    /**
     * Summary of getThumbFromData
     * @param string $data
     * @param ?string $cacheFile
     * @return static|string
     */
    public function getThumbFromData($data, $cacheFile)
    {
        $result = $this->generateThumbnail(null, $cacheFile, $data);
        if ($result) {
            return $this->setThumbResult($result, $cacheFile);
        }

        $mime = ($this->type == 'jpg') ? 'image/jpeg' : 'image/png';
        $this->setHeaders($mime, 0);
        return $this->setContent($data);
    }

    /**
     * Summary of generateThumbnail
     * @param ?string $file real image file
     * @param ?string $outputfile save in cache file or output data now if resized
     * @param ?string $data image data
     * @return bool|string true if resized and saved, string data if resized and not saved, false otherwise
     */
    public function generateThumbnail($file = null, $outputfile = null, $data = null)
    {
        if (empty($file) && empty($data)) {
            return false;
        }
        if (is_null($this->width) && is_null($this->height)) {
            return false;
        }
        if (!empty($file) && (!is_file($file) || !is_readable($file))) {
            return false;
        }

        // -DC- Use cover file name
        // get image size
        if (!empty($file)) {
            $size = getimagesize($file);
        } else {
            $size = getimagesizefromstring($data);
        }
        if ($size) {
            $w = $size[0];
            $h = $size[1];
            // set new size
            if (!is_null($this->width)) {
                $nw = $this->width;
                if ($nw >= $w) {
                    return false;
                }
                $nh = intval(($nw * $h) / $w);
            } else {
                $nh = $this->height;
                if ($nh >= $h) {
                    return false;
                }
                $nw = intval(($nh * $w) / $h);
            }
        } else {
            return false;
        }

        // Draw the image
        if (!empty($file)) {
            if ($this->type == 'png') {
                $src_img = imagecreatefrompng($file);
            } else {
                $src_img = imagecreatefromjpeg($file);
            }
        } else {
            $src_img = imagecreatefromstring($data);
        }
        if (!$src_img) {
            return false;
        }
        $dst_img = imagecreatetruecolor($nw, $nh);
        if (!imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $nw, $nh, $w, $h)) {
            return false;
        }
        // if we don't cache the thumbnail, capture the output
        if (is_null($outputfile)) {
            ob_start();
            if ($this->type == 'png') {
                imagepng($dst_img, null, 9);
            } else {
                imagejpeg($dst_img, null, 80);
            }
            return ob_get_clean();
        }
        if ($this->type == 'png') {
            if (!imagepng($dst_img, $outputfile, 9)) {
                return false;
            }
        } else {
            if (!imagejpeg($dst_img, $outputfile, 80)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Summary of setThumbResult
     * @param bool|string $result
     * @param ?string $cacheFile
     * @return static
     */
    protected function setThumbResult($result, $cacheFile)
    {
        $mime = ($this->type == 'jpg') ? 'image/jpeg' : 'image/png';

        // if we don't cache the thumbnail, generateThumbnail() returns the image data
        if ($cacheFile === null && is_string($result)) {
            $this->setHeaders($mime, 0);
            return $this->setContent($result);
        }
        // return the just cached thumbnail
        $this->setHeaders($mime, 0);
        return $this->setFile($cacheFile, true);
    }

    /**
     * Summary of getCachePath
     * @param string $uuid unique identifier for cached image
     * @param ?int $width
     * @param ?int $height
     * @param string $type
     * @param ?int $database cache per database for cover files
     * @return ?string
     */
    public static function getCachePath($uuid, $width, $height, $type = 'jpg', $database = null)
    {
        // moved some of the thumbnail cache from fetch.php to Cover
        //by default, we don't cache
        $cachePath = null;
        if (empty(Config::get('thumbnail_cache_directory'))) {
            return $cachePath;
        }

        if (empty($uuid)) {
            return $cachePath;
        }

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
     * Summary of getThumbnailHeight
     * @param string $thumb
     * @return int
     */
    public static function getThumbnailHeight($thumb)
    {
        return match ($thumb) {
            "opds2" => intval(Config::get('opds_thumbnail_height')) * 2,
            "opds" => intval(Config::get('opds_thumbnail_height')),
            "html2" => intval(Config::get('html_thumbnail_height')) * 2,
            "html" => intval(Config::get('html_thumbnail_height')),
            default => intval($thumb),
        };
    }
}
