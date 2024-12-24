<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkResource;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Output\Format as OutputFormat;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;
use SebLucas\EPubMeta\EPub;
use SebLucas\EPubMeta\Tools\ZipEdit;
use Exception;

//class Book extends Base
class Book
{
    use HasRouteTrait;

    public const PAGE_ID = PageId::ALL_BOOKS_ID;
    public const PAGE_ALL = PageId::ALL_BOOKS;
    public const PAGE_DETAIL = PageId::BOOK_DETAIL;
    public const ROUTE_ALL = "page-books";
    public const ROUTE_DETAIL = "page-book";
    // used to generate detailUrl in JsonRenderer
    public const ROUTE_PAGEID = "page-book-id";
    public const ROUTE_FILE = "fetch-file";
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
    public const DATA_DIR_NAME = 'data';

    /** @var int */
    public $id;
    /** @var string */
    public $title;
    /** @var mixed */
    public $timestamp;
    /** @var mixed */
    public $pubdate;
    /** @var string */
    public $path;
    /** @var string */
    public $uuid;
    /** @var bool */
    public $hasCover;
    /** @var string */
    public $relativePath;
    /** @var ?float */
    public $seriesIndex;
    /** @var string */
    public $comment;
    /** @var ?int */
    public $rating;
    /** @var ?int */
    protected $databaseId = null;
    /** @var ?array<Data> */
    public $datas = null;
    /** @var ?array<string> */
    public $extraFiles = null;
    /** @var ?array<Author> */
    public $authors = null;
    /** @var Publisher|false|null */
    public $publisher = null;
    /** @var Serie|false|null */
    public $serie = null;
    /** @var ?array<Tag> */
    public $tags = null;
    /** @var ?array<Identifier> */
    public $identifiers = null;
    /** @var ?array<Format> */
    public $formats = null;
    /** @var ?string */
    public $languages = null;
    /** @var ?array<Annotation> */
    public $annotations = null;
    /** @var array<mixed> */
    public $format = [];
    /** @var ?string */
    protected $coverFileName = null;
    public bool $updateForKepub = false;

    /**
     * Summary of __construct
     * @param \stdClass $line
     * @param ?int $database
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
        if (!empty(Config::get('calibre_external_storage'))) {
            $this->setExternalPath($line->path);
        } else {
            $this->setLocalPath($line->path, $database);
        }
        $this->seriesIndex = $line->series_index;
        $this->comment = $line->comment ?? '';
        $this->uuid = $line->uuid;
        $this->hasCover = $line->has_cover;
        $this->rating = $line->rating;
        $this->databaseId = $database;
        // do this at the end when all properties are set
        if ($this->hasCover) {
            $this->coverFileName = Cover::findCoverFileName($this, $line);
            if (empty($this->coverFileName)) {
                $this->hasCover = false;
            }
        }
    }

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of getCoverFileName
     * @return ?string
     */
    public function getCoverFileName()
    {
        if ($this->hasCover) {
            return $this->coverFileName;
        }
        return null;
    }

    /**
     * Summary of getEntryId
     * @return string
     */
    public function getEntryId()
    {
        return PageId::ALL_BOOKS_UUID . ':' . $this->uuid;
    }

    /**
     * Summary of getEntryIdByLetter
     * @param string $startingLetter
     * @return string
     */
    public static function getEntryIdByLetter($startingLetter)
    {
        return self::PAGE_ID . ':letter:' . $startingLetter;
    }

    /**
     * Summary of getEntryIdByYear
     * @param string|int $year
     * @return string
     */
    public static function getEntryIdByYear($year)
    {
        return self::PAGE_ID . ':year:' . $year;
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['id'] = $this->id;
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->databaseId;
        $params['author'] = $this->getAuthorsName();
        $params['title'] = $this->getTitle();
        return $this->getRoute(self::ROUTE_DETAIL, $params);
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
     * @param int $n
     * @param ?string $sort
     * @return ?array<Author>
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
     * @return Publisher|false
     */
    public function getPublisher()
    {
        if (is_null($this->publisher)) {
            $this->publisher = Publisher::getInstanceByBookId($this->id, $this->databaseId);
        }
        return $this->publisher;
    }

    /**
     * @return Serie|false
     */
    public function getSerie()
    {
        if (is_null($this->serie)) {
            $this->serie = Serie::getInstanceByBookId($this->id, $this->databaseId);
        }
        return $this->serie;
    }

    /**
     * @param int $n
     * @param ?string $sort
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
     * @param int $n
     * @param ?string $sort
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
     * @return array<Format>
     */
    public function getFormats()
    {
        if (is_null($this->formats)) {
            $this->formats = Format::getInstancesByBookId($this->id, $this->databaseId);
        }
        return $this->formats;
    }

    /**
     * @return array<Annotation>
     */
    public function getAnnotations()
    {
        if (is_null($this->annotations)) {
            $this->annotations = Annotation::getInstancesByBookId($this->id, $this->databaseId);
        }
        return $this->annotations;
    }

    /**
     * @param string $source from metadata.opf file (default)
     * @return Metadata|false
     */
    public function getMetadata($source = 'file')
    {
        $file = realpath($this->path . '/metadata.opf');
        if (empty($file) || !file_exists($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return Metadata::parseData($content);
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
     * Get extra data files associated with this book
     * @see https://manual.calibre-ebook.com/metadata.html#data-files
     * @return array<string>
     */
    public function getExtraFiles()
    {
        if (is_null($this->extraFiles)) {
            $this->extraFiles = [];
            $dataPath = $this->path . '/' . self::DATA_DIR_NAME . '/';
            if (!$this->isExternal() && is_dir($dataPath)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dataPath));
                foreach ($iterator as $file) {
                    if ($file->isDir()) {
                        continue;
                    }
                    if (!str_starts_with((string) $file->getPathname(), $dataPath)) {
                        continue;
                    }
                    array_push($this->extraFiles, substr((string) $file->getPathname(), strlen($dataPath)));
                }
            }
        }
        return $this->extraFiles;
    }

    /**
     * Summary of getExtraFileLink
     * @param string $fileName
     * @return LinkResource
     */
    public function getExtraFileLink($fileName)
    {
        $filePath = $this->path . '/' . self::DATA_DIR_NAME . '/' . $fileName;
        $mimetype = Response::getMimeType($filePath) ?? 'application/octet-stream';
        if (Database::useAbsolutePath($this->databaseId)) {
            $params = ['id' => $this->id, 'db' => $this->databaseId];
            $params['db'] ??= 0;
            $params['file'] = $fileName;
            $href = fn() => FetchHandler::route(self::ROUTE_FILE, $params);
        } else {
            $urlPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            $href = fn() => $this->getPath($urlPath);
        }
        $linkResource = new LinkResource(
            $href,
            $mimetype,
            'related',
            $fileName,
            $filePath
        );
        return $linkResource;
    }

    /**
     * Summary of GetMostInterestingDataToSendToKindle
     * @return ?Data
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
     * @param int $idData
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
        $dateY = (int) substr((string) $this->pubdate, 0, 4);
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
        if (!empty($se) && $withSerie) {
            $addition = $addition . '<strong>' . localize('content.series') . '</strong>' . str_format(localize('content.series.data'), $this->seriesIndex, htmlspecialchars($se->name)) . "<br />\n";
        }
        //if (preg_match('/<\/(div|p|a|span)>/', $this->comment)) {
        return $addition . OutputFormat::html2xhtml($this->comment);
        //} else {
        //    return $addition . htmlspecialchars($this->comment);
        //}
    }

    /**
     * Summary of getDataFormat
     * @param string $format
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
     * @param int $idData
     * @return string|false|null string for file path, false for missing cover, null for missing data
     */
    public function getFilePath($extension, $idData = null)
    {
        if ($extension == "jpg" || $extension == "png") {
            return $this->getCoverFilePath($extension);
        }
        $data = $this->getDataById($idData);
        if (!$data) {
            return null;
        }
        if ($this->isExternal()) {
            // external storage is assumed to be already url-encoded if needed
            return $data->getExternalPath();
        }
        return $data->getLocalPath();
    }

    /**
     * @checkme always returns absolute path for single DB in PHP app here - cfr. internal dir for X-Accel-Redirect with Nginx
     * @param string $extension
     * @return string|false string for file path, false for missing cover
     */
    public function getCoverFilePath($extension)
    {
        if (empty($this->coverFileName)) {
            return $this->path . '/cover.' . $extension;
        } else {
            $ext = strtolower(pathinfo($this->coverFileName, PATHINFO_EXTENSION));
            if ($ext == $extension) {
                return $this->coverFileName;
            }
        }
        return false;
    }

    /**
     * Summary of setLocalPath
     * @param string $path
     * @param ?int $database
     * @return string
     */
    public function setLocalPath($path, $database)
    {
        if (!is_dir($path)) {
            $this->path = Database::getDbDirectory($database) . $path;
        } else {
            $this->path = $path;
        }
        return $this->path;
    }

    /**
     * Summary of setExternalPath
     * @param string $path
     * @return string
     */
    public function setExternalPath($path)
    {
        if (str_starts_with($path, Config::get('calibre_external_storage'))) {
            $this->path = $path;
        } else {
            // external storage is assumed to be already url-encoded if needed
            $urlPath = implode('/', array_map('rawurlencode', explode('/', $path)));
            $this->path = Config::get('calibre_external_storage') . $urlPath;
        }
        return $this->path;
    }

    /**
     * Summary of isExternal
     * @return bool
     */
    public function isExternal()
    {
        if (!empty(Config::get('calibre_external_storage')) && str_starts_with($this->path, (string) Config::get('calibre_external_storage'))) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getUpdatedEpub
     * @param string $filePath
     * @return EPub
     */
    public function getUpdatedEpub($filePath)
    {
        $epub = new EPub($filePath, ZipEdit::class);

        $epub->setTitle($this->title);
        $authorArray = [];
        foreach ($this->getAuthors() as $author) {
            $authorArray[$author->sort] = $author->name;
        }
        $epub->setAuthors($authorArray);
        $epub->setLanguage($this->getLanguages());
        $epub->setDescription($this->getComment(false));
        $epub->setSubjects($this->getTagsName());
        // -DC- Use cover file name
        // $epub->Cover2($this->getCoverFilePath('jpg'), 'image/jpeg');
        $epub->setCoverFile($this->coverFileName, 'image/jpeg');
        $epub->setCalibre($this->uuid);
        $se = $this->getSerie();
        if (!empty($se)) {
            $epub->setSeries($se->name);
            $epub->setSeriesIndex(strval($this->seriesIndex));
        }
        return $epub;
    }

    /**
     * Summary of sendUpdatedEpub
     * @param int $idData
     * @param ?FileResponse $response
     * @return FileResponse
     */
    public function sendUpdatedEpub($idData, $response = null)
    {
        $data = $this->getDataById($idData);
        if (!$data) {
            // this will call exit()
            Response::sendError(null, 'Error: unable to find epub file');
        }
        return $data->sendUpdatedEpub($this->updateForKepub, $response);
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
                $cust->setHandler($this->getHandler());
                if ($asArray) {
                    array_push($result, $cust->toArray());
                } else {
                    array_push($result, $cust);
                }
            }
        }

        return $result;
    }

    /**
     * Summary of getLinkArray
     * @param array<mixed> $params @todo is this useful here?
     * @return array<LinkFeed|LinkResource>
     */
    public function getLinkArray($params = [])
    {
        $database = $this->databaseId;
        $linkArray = [];

        $cover = new Cover($this);
        $coverLink = $cover->getCoverLink();
        if ($coverLink) {
            array_push($linkArray, $coverLink);
        }
        // set height for thumbnail here depending on opds vs. html
        if (in_array($this->handler, ['feed', 'opds'])) {
            $thumb = 'opds';
        } else {
            $thumb = 'html';
        }
        $thumbnailLink = $cover->getThumbnailLink($thumb);
        if ($thumbnailLink) {
            array_push($linkArray, $thumbnailLink);
        }

        foreach ($this->getDatas() as $data) {
            if ($data->isKnownType()) {
                $linkResource = $data->getDataLink($data->format);
                array_push($linkArray, $linkResource);
            }
        }

        // don't use collection here, or OPDS reader will group all entries together - messes up recent books
        foreach ($this->getAuthors() as $author) {
            /** @var Author $author */
            $author->setHandler($this->handler);
            $href = fn() => $author->getUri();
            array_push(
                $linkArray,
                new LinkFeed(
                    $href,
                    'related',
                    str_format(localize('bookentry.author'), localize('splitByLetter.book.other'), $author->name)
                )
            );
        }

        // don't use collection here, or OPDS reader will group all entries together - messes up recent books
        $serie = $this->getSerie();
        if (!empty($serie)) {
            $serie->setHandler($this->handler);
            $href = fn() => $serie->getUri();
            array_push(
                $linkArray,
                new LinkFeed(
                    $href,
                    'related',
                    str_format(localize('content.series.data'), $this->seriesIndex, $serie->name)
                )
            );
        }

        return $linkArray;
    }

    /**
     * Summary of getEntry
     * @param int $count
     * @param array<mixed> $params
     * @return EntryBook
     */
    public function getEntry($count = 0, $params = [])
    {
        return new EntryBook(
            $this->getTitle(),
            $this->getEntryId(),
            $this->getComment(),
            'text/html',
            $this->getLinkArray($params),
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
     * @param int $bookId
     * @param ?int $database
     * @return ?Book
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
     * @param int $dataId
     * @param ?int $database
     * @return ?Book
     */
    public static function getBookByDataId($dataId, $database = null)
    {
        $query = 'select ' . self::getBookColumns() . ', data.name, data.format
from data, books ' . self::SQL_BOOKS_LEFT_JOIN . '
where data.book = books.id and data.id = ?';
        $ignored_formats = Config::get('ignored_formats');
        if (count($ignored_formats) > 0) {
            $query .= " and data.format not in ('"
            . implode("','", $ignored_formats)
            . "')";
        }
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
