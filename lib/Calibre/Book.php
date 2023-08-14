<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\Link;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\Page;
use SebLucas\EPubMeta\EPub;
use Exception;

//class Book extends Base
class Book
{
    public const PAGE_ID = Page::ALL_BOOKS_ID;
    public const PAGE_ALL = Page::ALL_BOOKS;
    public const PAGE_LETTER = Page::ALL_BOOKS_LETTER;
    public const PAGE_YEAR = Page::ALL_BOOKS_YEAR;
    public const PAGE_DETAIL = Page::BOOK_DETAIL;
    public const SQL_TABLE = "books";
    public const SQL_LINK_TABLE = "books";
    public const SQL_LINK_COLUMN = "id";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = 'books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index, uuid, has_cover, ratings.rating';
    public const SQL_ALL_ROWS = BookList::SQL_BOOKS_ALL;

    public const SQL_BOOKS_LEFT_JOIN = 'left outer join comments on comments.book = books.id
    left outer join books_ratings_link on books_ratings_link.book = books.id
    left outer join ratings on books_ratings_link.rating = ratings.id ';

    public const BAD_SEARCH = 'QQQQQ';

    public static string $endpoint = Config::ENDPOINT["index"];
    /** @var mixed */
    public $id;
    /** @var mixed */
    public $title;
    /** @var mixed */
    public $timestamp;
    /** @var mixed */
    public $pubdate;
    /** @var mixed */
    public $path;
    /** @var mixed */
    public $uuid;
    /** @var mixed */
    public $hasCover;
    /** @var mixed */
    public $relativePath;
    /** @var mixed */
    public $seriesIndex;
    /** @var mixed */
    public $comment;
    /** @var mixed */
    public $rating;
    /** @var mixed */
    protected $databaseId = null;
    /** @var Data[]|null */
    public $datas = null;
    /** @var Author[]|null */
    public $authors = null;
    /** @var Publisher|null */
    public $publisher = null;
    /** @var Serie|null */
    public $serie = null;
    /** @var Tag[]|null */
    public $tags = null;
    /** @var Identifier[]|null */
    public $identifiers = null;
    /** @var string|null */
    public $languages = null;
    /** @var array<mixed> */
    public $format = [];
    /** @var string|null */
    private $coverFileName = null;
    public bool $updateForKepub = false;

    /**
     * Summary of __construct
     * @param mixed $line
     * @param mixed $database
     */
    public function __construct($line, $database = null)
    {
        $this->id = $line->id;
        $this->title = $line->title;
        $this->timestamp = strtotime($line->timestamp);
        $this->pubdate = $line->pubdate;
        //$this->path = Database::getDbDirectory() . $line->path;
        //$this->relativePath = $line->path;
        // -DC- Init relative or full path
        $this->path = $line->path;
        if (!is_dir($this->path)) {
            $this->path = Database::getDbDirectory($database) . $line->path;
        }
        $this->seriesIndex = $line->series_index;
        $this->comment = $line->comment ?? '';
        $this->uuid = $line->uuid;
        $this->hasCover = $line->has_cover;
        // -DC- Use cover file name
        //if (!file_exists($this->getFilePath('jpg'))) {
        //    // double check
        //    $this->hasCover = 0;
        //}
        if ($this->hasCover) {
            if (!empty(Config::get('calibre_database_field_cover'))) {
                $imgDirectory = Database::getImgDirectory($database);
                $this->coverFileName = $line->cover;
                if (!file_exists($this->coverFileName)) {
                    $this->coverFileName = null;
                }
                if (empty($this->coverFileName)) {
                    $this->coverFileName = sprintf('%s%s', $imgDirectory, $line->cover);
                    if (!file_exists($this->coverFileName)) {
                        $this->coverFileName = null;
                    }
                }
                if (empty($this->coverFileName)) {
                    // Try with the epub file name
                    $data = $this->getDataFormat('EPUB');
                    if ($data) {
                        $this->coverFileName = sprintf('%s%s/%s', $imgDirectory, $data->name, $line->cover);
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
            }
            // Else try with default cover file name
            if (empty($this->coverFileName)) {
                $cover = $this->getFilePath("jpg");
                if ($cover === false || !file_exists($cover)) {
                    $cover = $this->getFilePath("png");
                }
                if ($cover === false || !file_exists($cover)) {
                    $this->hasCover = 0;
                } else {
                    $this->coverFileName = $cover;
                }
            }
        }
        $this->rating = $line->rating;
        $this->databaseId = $database;
    }

    /**
     * Summary of getDatabaseId
     * @return mixed
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of getEntryId
     * @return string
     */
    public function getEntryId()
    {
        return Page::ALL_BOOKS_UUID.':'.$this->uuid;
    }

    /**
     * Summary of getEntryIdByLetter
     * @param mixed $startingLetter
     * @return string
     */
    public static function getEntryIdByLetter($startingLetter)
    {
        return self::PAGE_ID.':letter:'.$startingLetter;
    }

    /**
     * Summary of getEntryIdByYear
     * @param mixed $year
     * @return string
     */
    public static function getEntryIdByYear($year)
    {
        return self::PAGE_ID.':year:'.$year;
    }

    /**
     * Summary of getUri
     * @return string
     */
    public function getUri()
    {
        return '?page='.self::PAGE_DETAIL.'&id=' . $this->id;
    }

    /**
     * Summary of getDetailUrl
     * @return string
     */
    public function getDetailUrl()
    {
        $urlParam = $this->getUri();
        $urlParam = Format::addDatabaseParam($urlParam, $this->databaseId);
        return self::$endpoint . $urlParam;
    }

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /* Other class (author, series, tag, ...) initialization and accessors */

    /**
     * @param mixed $n
     * @param mixed $sort
     * @return array<Author>|null
     */
    public function getAuthors($n = -1, $sort = null)
    {
        if (is_null($this->authors)) {
            $this->authors = Author::getInstancesByBookId($this->id, $this->databaseId);
        }
        return $this->authors;
    }

    /**
     * Summary of getAuthorsName
     * @return string
     */
    public function getAuthorsName()
    {
        return implode(', ', array_map(function ($author) {
            return $author->name;
        }, $this->getAuthors()));
    }

    /**
     * Summary of getAuthorsSort
     * @return string
     */
    public function getAuthorsSort()
    {
        return implode(', ', array_map(function ($author) {
            return $author->sort;
        }, $this->getAuthors()));
    }

    /**
     * Summary of getPublisher
     * @return Publisher|null
     */
    public function getPublisher()
    {
        if (is_null($this->publisher)) {
            $this->publisher = Publisher::getInstanceByBookId($this->id, $this->databaseId);
        }
        return $this->publisher;
    }

    /**
     * @return Serie|null
     */
    public function getSerie()
    {
        if (is_null($this->serie)) {
            $this->serie = Serie::getInstanceByBookId($this->id, $this->databaseId);
        }
        return $this->serie;
    }

    /**
     * @param mixed $n
     * @param mixed $sort
     * @return string
     */
    public function getLanguages($n = -1, $sort = null)
    {
        if (is_null($this->languages)) {
            $this->languages = Language::getLanguagesByBookId($this->id, $this->databaseId);
        }
        return $this->languages;
    }

    /**
     * @param mixed $n
     * @param mixed $sort
     * @return array<Tag>
     */
    public function getTags($n = -1, $sort = null)
    {
        if (is_null($this->tags)) {
            $this->tags = Tag::getInstancesByBookId($this->id, $this->databaseId);
        }
        return $this->tags;
    }

    /**
     * Summary of getTagsName
     * @return string
     */
    public function getTagsName()
    {
        return implode(', ', array_map(function ($tag) {
            return $tag->name;
        }, $this->getTags()));
    }

    /**
     * @return array<Identifier>
     */
    public function getIdentifiers()
    {
        if (is_null($this->identifiers)) {
            $this->identifiers = Identifier::getInstancesByBookId($this->id, $this->databaseId);
        }
        return $this->identifiers;
    }

    /**
     * @return array<Data>
     */
    public function getDatas()
    {
        if (is_null($this->datas)) {
            $this->datas = self::getDataByBook($this);
        }
        return $this->datas;
    }

    /**
     * Summary of GetMostInterestingDataToSendToKindle
     * @return Data|null
     */
    public function GetMostInterestingDataToSendToKindle()
    {
        $bestFormatForKindle = ['PDF', 'AZW3', 'MOBI', 'EPUB'];
        $bestRank = -1;
        $bestData = null;
        foreach ($this->getDatas() as $data) {
            $key = array_search($data->format, $bestFormatForKindle);
            if ($key !== false && $key > $bestRank) {
                $bestRank = $key;
                $bestData = $data;
            }
        }
        return $bestData;
    }

    /**
     * Summary of getDataById
     * @param mixed $idData
     * @return Data|false
     */
    public function getDataById($idData)
    {
        $reduced = array_filter($this->getDatas(), function ($data) use ($idData) {
            return $data->id == $idData;
        });
        return reset($reduced);
    }

    /**
     * Summary of getRating
     * @return string
     */
    public function getRating()
    {
        if (is_null($this->rating) || $this->rating == 0) {
            return '';
        }
        $retour = '';
        for ($i = 0; $i < $this->rating / 2; $i++) {
            $retour .= '&#9733;'; // full star
        }
        for ($i = 0; $i < 5 - $this->rating / 2; $i++) {
            $retour .= '&#9734;'; // empty star
        }
        return $retour;
    }

    /**
     * Summary of getPubDate
     * @return string
     */
    public function getPubDate()
    {
        if (empty($this->pubdate)) {
            return '';
        }
        $dateY = (int) substr($this->pubdate, 0, 4);
        if ($dateY > 102) {
            return str_pad(strval($dateY), 4, '0', STR_PAD_LEFT);
        }
        return '';
    }

    /**
     * Summary of getComment
     * @param bool $withSerie
     * @return string
     */
    public function getComment($withSerie = true)
    {
        $addition = '';
        $se = $this->getSerie();
        if (!is_null($se) && $withSerie) {
            $addition = $addition . '<strong>' . localize('content.series') . '</strong>' . str_format(localize('content.series.data'), $this->seriesIndex, htmlspecialchars($se->name)) . "<br />\n";
        }
        //if (preg_match('/<\/(div|p|a|span)>/', $this->comment)) {
        return $addition . Format::html2xhtml($this->comment);
        //} else {
        //    return $addition . htmlspecialchars($this->comment);
        //}
    }

    /**
     * Summary of getDataFormat
     * @param mixed $format
     * @return Data|false
     */
    public function getDataFormat($format)
    {
        $reduced = array_filter($this->getDatas(), function ($data) use ($format) {
            return $data->format == $format;
        });
        return reset($reduced);
    }

    /**
     * @checkme always returns absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
     * @param string $extension
     * @param mixed $idData
     * @param false $relative Deprecated
     * @return string|false|null
     */
    public function getFilePath($extension, $idData = null, $relative = false)
    {
        /*if ($extension == 'jpg')
        {
            $file = 'cover.jpg';
        } else {
            $data = $this->getDataById($idData);
            if (!$data) {
                return null;
            }
            $file = $data->name . '.' . strtolower($data->format);
        }

        if ($relative) {
            return $this->relativePath.'/'.$file;
        } else {
            return $this->path.'/'.$file;
        }*/
        if ($extension == "jpg" || $extension == "png") {
            if (empty($this->coverFileName)) {
                return $this->path . '/cover.' . $extension;
            } else {
                $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
                if ($ext == $extension) {
                    return $this->coverFileName;
                }
            }
            return false;
        } else {
            $data = $this->getDataById($idData);
            if (!$data) {
                return null;
            }
            $file = $data->name . "." . strtolower($data->format);
            return $this->path . '/' . $file;
        }
    }

    /**
     * Summary of getUpdatedEpub
     * @param mixed $idData
     * @return void
     */
    public function getUpdatedEpub($idData)
    {
        $data = $this->getDataById($idData);

        try {
            $epub = new EPub($data->getLocalPath());

            $epub->Title($this->title);
            $authorArray = [];
            foreach ($this->getAuthors() as $author) {
                $authorArray[$author->sort] = $author->name;
            }
            $epub->Authors($authorArray);
            $epub->Language($this->getLanguages());
            $epub->Description($this->getComment(false));
            $epub->Subjects($this->getTagsName());
            // -DC- Use cover file name
            // $epub->Cover2($this->getFilePath('jpg'), 'image/jpeg');
            $epub->Cover2($this->coverFileName, 'image/jpeg');
            $epub->Calibre($this->uuid);
            $se = $this->getSerie();
            if (!is_null($se)) {
                $epub->Serie($se->name);
                $epub->SerieIndex($this->seriesIndex);
            }
            $filename = $data->getUpdatedFilenameEpub();
            // @checkme this is set in fetch.php now
            if ($this->updateForKepub) {
                $epub->updateForKepub();
                $filename = $data->getUpdatedFilenameKepub();
            }
            $epub->download($filename);
        } catch (Exception $e) {
            echo 'Exception : ' . $e->getMessage();
        }
    }

    /**
     * Summary of getThumbnail
     * @param mixed $width
     * @param mixed $height
     * @param mixed $outputfile
     * @param mixed $inType
     * @return bool
     */
    public function getThumbnail($width, $height, $outputfile = null, $inType = 'jpg')
    {
        if (is_null($width) && is_null($height)) {
            return false;
        }

        // -DC- Use cover file name
        //$file = $this->getFilePath('jpg');
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
                $nh = intval(($nw*$h)/$w);
            } else {
                $nh = $height;
                if ($nh >= $h) {
                    return false;
                }
                $nw = intval(($nh*$w)/$h);
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
     * The values of all the specified columns
     *
     * @param string[] $columns
     * @param bool $asArray
     * @return array<mixed>
     */
    public function getCustomColumnValues($columns, $asArray = false)
    {
        $result = [];
        $database = $this->databaseId;

        $columns = CustomColumnType::checkCustomColumnList($columns, $database);

        foreach ($columns as $lookup) {
            $col = CustomColumnType::createByLookup($lookup, $database);
            if (!is_null($col)) {
                $cust = $col->getCustomByBook($this);
                if (!is_null($cust)) {
                    if ($asArray) {
                        array_push($result, $cust->toArray());
                    } else {
                        array_push($result, $cust);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Summary of getLinkArray
     * @return array<Link>
     */
    public function getLinkArray()
    {
        $database = $this->databaseId;
        $linkArray = [];

        if ($this->hasCover) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_IMAGE_TYPE, 'cover.jpg', NULL));
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_THUMBNAIL_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            // @todo set height for thumbnail here depending on opds vs. html
            if ($ext == 'png') {
                array_push($linkArray, Data::getLink($this, "png", "image/png", Link::OPDS_IMAGE_TYPE, "cover.png", null));
                array_push($linkArray, Data::getLink($this, "png", "image/png", Link::OPDS_THUMBNAIL_TYPE, "cover.png", null));
            } else {
                array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_IMAGE_TYPE, 'cover.jpg', null));
                array_push($linkArray, Data::getLink($this, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", null));
            }
        }

        foreach ($this->getDatas() as $data) {
            if ($data->isKnownType()) {
                array_push($linkArray, $data->getDataLink(Link::OPDS_ACQUISITION_TYPE, $data->format));
            }
        }

        foreach ($this->getAuthors() as $author) {
            /** @var Author $author */
            array_push($linkArray, new LinkNavigation($author->getUri(), 'related', str_format(localize('bookentry.author'), localize('splitByLetter.book.other'), $author->name), $database));
        }

        $serie = $this->getSerie();
        if (!is_null($serie)) {
            array_push($linkArray, new LinkNavigation($serie->getUri(), 'related', str_format(localize('content.series.data'), $this->seriesIndex, $serie->name), $database));
        }

        return $linkArray;
    }

    /**
     * Summary of getEntry
     * @param mixed $count
     * @return EntryBook
     */
    public function getEntry($count = 0)
    {
        return new EntryBook(
            $this->getTitle(),
            $this->getEntryId(),
            $this->getComment(),
            'text/html',
            $this->getLinkArray(),
            $this
        );
    }

    /* End of other class (author, series, tag, ...) initialization and accessors */

    // -DC- Get customisable book columns
    /**
     * Summary of getBookColumns
     * @return string
     */
    public static function getBookColumns()
    {
        $res = self::SQL_COLUMNS;
        if (!empty(Config::get('calibre_database_field_cover'))) {
            $res = str_replace('has_cover,', 'has_cover, ' . Config::get('calibre_database_field_cover') . ',', $res);
        }

        return $res;
    }

    /**
     * Summary of getBookById
     * @param mixed $bookId
     * @param mixed $database
     * @return Book|null
     */
    public static function getBookById($bookId, $database = null)
    {
        $query = 'select ' . self::getBookColumns() . '
from books ' . self::SQL_BOOKS_LEFT_JOIN . '
where books.id = ?';
        $result = Database::query($query, [$bookId], $database);
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $database);
            return $book;
        }
        return null;
    }

    /**
     * Summary of getBookByDataId
     * @param mixed $dataId
     * @param mixed $database
     * @return Book|null
     */
    public static function getBookByDataId($dataId, $database = null)
    {
        $query = 'select ' . self::getBookColumns() . ', data.name, data.format
from data, books ' . self::SQL_BOOKS_LEFT_JOIN . '
where data.book = books.id and data.id = ?';
        $result = Database::query($query, [$dataId], $database);
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $database);
            $data = new Data($post, $book);
            $data->id = $dataId;
            $book->datas = [$data];
            return $book;
        }
        return null;
    }

    /**
     * Summary of getDataByBook
     * @param Book $book
     * @return array<Data>
     */
    public static function getDataByBook($book)
    {
        $out = [];

        $sql = 'select id, format, name from data where book = ?';

        $ignored_formats = Config::get('ignored_formats');
        if (count($ignored_formats) > 0) {
            $sql .= " and format not in ('"
            . implode("','", $ignored_formats)
            . "')";
        }

        $database = $book->getDatabaseId();
        $result = Database::query($sql, [$book->id], $database);

        while ($post = $result->fetchObject()) {
            array_push($out, new Data($post, $book));
        }
        return $out;
    }
}
