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

class Book extends Base
{
    public const PAGE_ID = Page::ALL_BOOKS_ID;
    public const PAGE_ALL = Page::ALL_BOOKS;
    public const PAGE_LETTER = Page::ALL_BOOKS_LETTER;
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

    public static $endpoint = Config::ENDPOINT["index"];
    public $id;
    public $title;
    public $timestamp;
    public $pubdate;
    public $path;
    public $uuid;
    public $hasCover;
    public $relativePath;
    public $seriesIndex;
    public $comment;
    public $rating;
    public $datas = null;
    public $authors = null;
    public $publisher = null;
    public $serie = null;
    public $tags = null;
    public $identifiers = null;
    public $languages = null;
    public $format = [];
    private $coverFileName = null;
    public $updateForKepub = false;

    public function __construct($line, $database = null)
    {
        global $config;

        $this->id = $line->id;
        $this->title = $line->title;
        $this->timestamp = strtotime($line->timestamp);
        $this->pubdate = $line->pubdate;
        //$this->path = Base::getDbDirectory() . $line->path;
        //$this->relativePath = $line->path;
        // -DC- Init relative or full path
        $this->path = $line->path;
        if (!is_dir($this->path)) {
            $this->path = Base::getDbDirectory($database) . $line->path;
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
            if (!empty($config['calibre_database_field_cover'])) {
                $imgDirectory = Base::getImgDirectory($database);
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

    public function getEntryId()
    {
        return Page::ALL_BOOKS_UUID.':'.$this->uuid;
    }

    public static function getEntryIdByLetter($startingLetter)
    {
        return self::PAGE_ID.':letter:'.$startingLetter;
    }

    public function getUri()
    {
        return '?page='.self::PAGE_DETAIL.'&id=' . $this->id;
    }

    public function getDetailUrl()
    {
        $urlParam = $this->getUri();
        $urlParam = Format::addDatabaseParam($urlParam, $this->databaseId);
        return self::$endpoint . $urlParam;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /* Other class (author, series, tag, ...) initialization and accessors */

    /**
     * @return Author[]
     */
    public function getAuthors()
    {
        if (is_null($this->authors)) {
            $this->authors = Author::getAuthorByBookId($this->id, $this->databaseId);
        }
        return $this->authors;
    }

    public function getAuthorsName()
    {
        return implode(', ', array_map(function ($author) {
            return $author->name;
        }, $this->getAuthors()));
    }

    public function getAuthorsSort()
    {
        return implode(', ', array_map(function ($author) {
            return $author->sort;
        }, $this->getAuthors()));
    }

    public function getPublisher()
    {
        if (is_null($this->publisher)) {
            $this->publisher = Publisher::getPublisherByBookId($this->id, $this->databaseId);
        }
        return $this->publisher;
    }

    /**
     * @return Serie|null
     */
    public function getSerie()
    {
        if (is_null($this->serie)) {
            $this->serie = Serie::getSerieByBookId($this->id, $this->databaseId);
        }
        return $this->serie;
    }

    /**
     * @return string
     */
    public function getLanguages()
    {
        $lang = [];
        $result = parent::getDb($this->databaseId)->prepare('select languages.lang_code
                from books_languages_link, languages
                where books_languages_link.lang_code = languages.id
                and book = ?
                order by item_order');
        $result->execute([$this->id]);
        while ($post = $result->fetchObject()) {
            array_push($lang, Language::getLanguageString($post->lang_code));
        }
        return implode(', ', $lang);
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        if (is_null($this->tags)) {
            $this->tags = [];

            $result = parent::getDb($this->databaseId)->prepare('select tags.id as id, name
                from books_tags_link, tags
                where tag = tags.id
                and book = ?
                order by name');
            $result->execute([$this->id]);
            while ($post = $result->fetchObject()) {
                array_push($this->tags, new Tag($post, $this->databaseId));
            }
        }
        return $this->tags;
    }

    public function getTagsName()
    {
        return implode(', ', array_map(function ($tag) {
            return $tag->name;
        }, $this->getTags()));
    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers()
    {
        if (is_null($this->identifiers)) {
            $this->identifiers = [];

            $result = parent::getDb($this->databaseId)->prepare('select type, val, id
                from identifiers
                where book = ?
                order by type');
            $result->execute([$this->id]);
            while ($post = $result->fetchObject()) {
                array_push($this->identifiers, new Identifier($post, $this->databaseId));
            }
        }
        return $this->identifiers;
    }

    /**
     * @return Data[]
     */
    public function getDatas()
    {
        if (is_null($this->datas)) {
            $this->datas = self::getDataByBook($this);
        }
        return $this->datas;
    }

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

    public function getDataById($idData)
    {
        $reduced = array_filter($this->getDatas(), function ($data) use ($idData) {
            return $data->id == $idData;
        });
        return reset($reduced);
    }

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

    public function getDataFormat($format)
    {
        $reduced = array_filter($this->getDatas(), function ($data) use ($format) {
            return $data->format == $format;
        });
        return reset($reduced);
    }

    /**
     * @checkme always returns absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
     * @param false $relative Deprecated
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

    public function getUpdatedEpub($idData)
    {
        global $config;
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
     * @return CustomColumn[]
     */
    public function getCustomColumnValues($columns, $asArray = false)
    {
        $result = [];
        $database = $this->getDatabaseId();

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

    public function getLinkArray()
    {
        $database = $this->getDatabaseId();
        $linkArray = [];

        if ($this->hasCover) {
            // -DC- Use cover file name
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_IMAGE_TYPE, 'cover.jpg', NULL));
            //array_push($linkArray, Data::getLink($this, 'jpg', 'image/jpeg', Link::OPDS_THUMBNAIL_TYPE, 'cover.jpg', NULL));
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
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
    public static function getBookColumns()
    {
        global $config;

        $res = self::SQL_COLUMNS;
        if (!empty($config['calibre_database_field_cover'])) {
            $res = str_replace('has_cover,', 'has_cover, ' . $config['calibre_database_field_cover'] . ',', $res);
        }

        return $res;
    }

    public static function getBookById($bookId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select ' . self::getBookColumns() . '
from books ' . self::SQL_BOOKS_LEFT_JOIN . '
where books.id = ?');
        $result->execute([$bookId]);
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $database);
            return $book;
        }
        return null;
    }

    public static function getBookByDataId($dataId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select ' . self::getBookColumns() . ', data.name, data.format
from data, books ' . self::SQL_BOOKS_LEFT_JOIN . '
where data.book = books.id and data.id = ?');
        $result->execute([$dataId]);
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $database);
            $data = new Data($post, $book);
            $data->id = $dataId;
            $book->datas = [$data];
            return $book;
        }
        return null;
    }

    public static function getDataByBook($book)
    {
        global $config;

        $out = [];

        $sql = 'select id, format, name from data where book = ?';

        $ignored_formats = $config['cops_ignored_formats'];
        if (count($ignored_formats) > 0) {
            $sql .= " and format not in ('"
            . implode("','", $ignored_formats)
            . "')";
        }

        $database = $book->getDatabaseId();
        $result = parent::getDb($database)->prepare($sql);
        $result->execute([$book->id]);

        while ($post = $result->fetchObject()) {
            array_push($out, new Data($post, $book));
        }
        return $out;
    }
}
