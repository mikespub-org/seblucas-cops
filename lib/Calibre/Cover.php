<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Link;

class Cover
{
    /** @var Book */
    public $book;
    /** @var mixed */
    protected $databaseId;
    /** @var string|null */
    public $coverFileName = null;

    /**
     * Summary of __construct
     * @param mixed $book
     * @param mixed $database
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
     * @return string|null
     */
    public function checkDatabaseFieldCover($fileName)
    {
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
     * @return string|null
     */
    public function checkCoverFilePath()
    {
        $cover = $this->book->getFilePath("jpg");
        if ($cover === false || !file_exists($cover)) {
            $cover = $this->book->getFilePath("png");
        }
        if ($cover === false || !file_exists($cover)) {
            $this->coverFileName = null;
        } else {
            $this->coverFileName = $cover;
        }
        return $this->coverFileName;
    }

    /**
     * Summary of getCoverUri
     * @param string $endpoint
     * @return string|null
     */
    public function getCoverUri($endpoint)
    {
        $link = $this->getCoverLink();
        if ($link) {
            return $link->hrefXhtml($endpoint);
        }
        return null;
    }

    /**
     * Summary of getCoverLink
     * @return Link|null
     */
    public function getCoverLink()
    {
        if ($this->coverFileName) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_IMAGE_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            $file = 'cover.' . $ext;
            // @todo move some of the image-specific code from Data to Cover
            return Data::getLink($this->book, $ext, $mime, Link::OPDS_IMAGE_TYPE, $file, null);
        }
        return null;
    }

    /**
     * Summary of getThumbnailUri
     * @param string $endpoint
     * @param mixed $height
     * @param bool $useDefault
     * @return string|null
     */
    public function getThumbnailUri($endpoint, $height, $useDefault = true)
    {
        $link = $this->getThumbnailLink($height, $useDefault);
        if ($link) {
            return $link->hrefXhtml($endpoint);
        }
        return null;
    }

    /**
     * Summary of getThumbnailLink
     * @param mixed $height
     * @param bool $useDefault
     * @return Link|null
     */
    public function getThumbnailLink($height = null, $useDefault = true)
    {
        if ($this->coverFileName) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_THUMBNAIL_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            $file = 'cover.' . $ext;
            // @todo set height for thumbnail here depending on opds vs. html
            // @todo move some of the image-specific code from Data to Cover
            return Data::getLink($this->book, $ext, $mime, Link::OPDS_THUMBNAIL_TYPE, $file, null, null, $height);
        }
        if ($useDefault) {
            return $this->getDefaultLink();
        }
        return null;
    }

    /**
     * Summary of getDefaultLink
     * @return Link|null
     */
    public function getDefaultLink()
    {
        if (!empty(Config::get('thumbnail_default'))) {
            $ext = strtolower(pathinfo(Config::get('thumbnail_default'), PATHINFO_EXTENSION));
            $mime = ($ext == 'jpg') ? 'image/jpeg' : 'image/png';
            return new Link(Config::get('thumbnail_default'), $mime, Link::OPDS_THUMBNAIL_TYPE);
        }
        return null;
    }
}
