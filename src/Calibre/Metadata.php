<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Output\ComicReader;
use SebLucas\EPubMeta\EPub;
use SebLucas\EPubMeta\Metadata as EPubMetadata;
use DOMDocument;
use InvalidArgumentException;

/**
 * Calibre metadata.opf files are based on EPUB 2.0 <https://idpf.org/epub/20/spec/OPF_2.0_latest.htm#Section2.0>,
 * not EPUB 3.x <https://www.w3.org/TR/epub-33/#sec-package-doc>
 */
class Metadata extends EPubMetadata
{
    public const ROUTE_DETAIL = "restapi-metadata";
    public const ROUTE_ELEMENT = "restapi-metadata-element";
    public const ROUTE_ELEMENT_NAME = "restapi-metadata-element-name";

    public string $filePath = '';

    /**
     * Summary of updateBook
     * @todo add other metadata from .opf file
     * @param Book $book
     * @return Book
     */
    public function updateBook($book)
    {
        $creator = $this->getElement('dc:creator');
        if (!empty($creator)) {
            $name = $creator[0]['value'];
            if (!empty($creator[0]['file-as'])) {
                $sort = $creator[0]['file-as'];
            } else {
                // convert to Lastname, Firstname(s)
                $pieces = explode(' ', $name);
                $last = array_pop($pieces);
                $sort = $last . ', ' . implode(' ', $pieces);
            }
            $post = (object) ['id' => null, 'name' => $name, 'sort' => $sort];
            $author = new Author($post);
            $book->authors = [$author];
        }
        $description = $this->getElement('dc:description');
        if (!empty($description)) {
            $book->comment = $description[0];
        }
        // set other properties to avoid db lookup
        $book->publisher ??= false;
        $book->serie ??= false;
        $book->tags ??= [];
        $book->rating ??= 0;
        $book->languages ??= '';
        $book->identifiers ??= [];
        $book->formats ??= [];
        $book->annotations ??= [];
        $book->pages ??= 0;
        $book->datas ??= [];
        $book->extraFiles ??= [];
        if (empty($book->uuid) && !empty($this->uniqueIdentifier)) {
            $book->uuid = $this->uniqueIdentifier;
        }
        // set fake uuid for cover cache
        if (empty($book->uuid) || str_contains($book->uuid, 'uuid')) {
            if (!empty($this->filePath)) {
                $mtime = filemtime($this->filePath);
                $book->uuid = md5((string) $mtime . '-' . $this->filePath);
            } else {
                $mtime = $book->timestamp ?: 0;
                $book->uuid = md5((string) $mtime . '-' . $book->path);
            }
        }
        return $book;
    }

    /**
     * Summary of updateBookFromFile
     * @param Book $book
     * @param string $filePath
     * @param ?string $format
     * @return Book
     */
    public static function updateBookFromFile($book, $filePath, $format = null)
    {
        $format ??= pathinfo($filePath, PATHINFO_EXTENSION);
        $format = strtoupper($format);
        switch ($format) {
            case 'CBZ':
                return static::updateBookFromComic($book, $filePath);
            case 'EPUB':
                return static::updateBookFromEPub($book, $filePath);
            case 'OPF':
                return static::updateBookFromMetadata($book, $filePath);
            default:
                throw new InvalidArgumentException('Invalid Format');
        }
    }

    /**
     * Summary of updateBookFromMetadata
     * @param Book $book
     * @param string $filePath
     * @return Book
     */
    public static function updateBookFromMetadata($book, $filePath)
    {
        $metadata = static::fromFile($filePath);
        $metadata->filePath = $filePath;
        return $metadata->updateBook($book);
    }

    /**
     * Summary of updateBookFromEPub
     * @param Book $book
     * @param string $filePath
     * @return Book
     */
    public static function updateBookFromEPub($book, $filePath)
    {
        $epub = new EPub($filePath);
        // Note: cover URLs will fail in JsonRenderer::getFullBookContentArray()
        // for books in folders /ebook/ if book title is updated based on EPUB file
        //$book->title = $epub->getTitle();
        $book->pubdate = $epub->getCreationDate();
        $book->authors = [];
        foreach ($epub->getAuthors() as $sort => $name) {
            $post = (object) ['id' => null, 'name' => $name, 'sort' => $sort];
            $author = new Author($post);
            $book->authors[] = $author;
        }
        $description = $epub->getDescription();
        if (!empty($description)) {
            $book->comment = $description;
        }
        $publisher = $epub->getPublisher();
        if ($publisher) {
            $post = (object) ['id' => null, 'name' => $publisher];
            $book->publisher = new Publisher($post);
        }
        [$series, $index] = $epub->getSeriesOrCollection();
        if ($series) {
            $post = (object) ['id' => null, 'name' => $series];
            $book->serie = new Serie($post);
            $book->seriesIndex = (float) $index;
        }
        $book->tags = [];
        foreach ($epub->getSubjects() as $name) {
            $post = (object) ['id' => null, 'name' => $name];
            $tag = new Tag($post);
            $book->tags[] = $tag;
        }
        //$book->rating = $epub->getRating();
        $book->languages = $epub->getLanguage();
        $book->identifiers = [];
        foreach (['ISBN', 'URI'] as $type) {
            $val = $epub->getIdentifier($type);
            if ($val) {
                if ($type == 'URI') {
                    $type = 'url';
                }
                $post = (object) ['id' => null, 'type' => $type, 'val' => $val];
                $identifier = new Identifier($post);
                $book->identifiers[] = $identifier;
            }
        }
        $book->uuid = $epub->getUuid();
        // set fake uuid for cover cache
        if (empty($book->uuid) || str_contains($book->uuid, 'uuid')) {
            $mtime = filemtime($filePath);
            $book->uuid = md5((string) $mtime . '-' . $filePath);
        }
        return $book;
    }

    /**
     * Summary of updateBookFromComic
     * @param Book $book
     * @param string $filePath
     * @return Book
     */
    public static function updateBookFromComic($book, $filePath)
    {
        $reader = new ComicReader();
        $metadata = $reader->getMetadata($filePath);
        if (empty($metadata)) {
            return $book;
        }
        $title = $metadata->getElement('Title');
        if (!empty($title)) {
            $book->title = $title[0];
        }
        $year = $metadata->getElement('Year');
        if (!empty($year)) {
            $book->pubdate = $year[0];
            $month = $metadata->getElement('Month');
            if (!empty($month)) {
                $book->pubdate .= '-' . $month[0];
                $day = $metadata->getElement('Day');
                if (!empty($day)) {
                    $book->pubdate .= '-' . $day[0];
                }
            }
        }
        $writer = $metadata->getElement('Writer');
        $book->authors = [];
        if (!empty($writer)) {
            $post = (object) ['id' => null, 'name' => $writer[0], 'sort' => $writer[0]];
            $author = new Author($post);
            $book->authors[] = $author;
        }
        $summary = $metadata->getElement('Summary');
        if (!empty($summary)) {
            $book->comment = $summary[0];
        }
        $publisher = $metadata->getElement('Publisher');
        if (!empty($publisher)) {
            $post = (object) ['id' => null, 'name' => $publisher[0]];
            $book->publisher = new Publisher($post);
        }
        $series = $metadata->getElement('Series');
        if (!empty($series)) {
            $post = (object) ['id' => null, 'name' => $series[0]];
            $book->serie = new Serie($post);
            $index = $metadata->getElement('Number');
            if (!empty($index)) {
                $book->seriesIndex = (float) $index[0];
            }
        }
        $genre = $metadata->getElement('Genre');
        $book->tags = [];
        if (!empty($genre)) {
            $post = (object) ['id' => null, 'name' => $genre[0]];
            $tag = new Tag($post);
            $book->tags[] = $tag;
        }
        $language = $metadata->getElement('LanguageISO');
        if (!empty($language)) {
            $book->languages = $language[0];
        }
        $web = $metadata->getElement('Web');
        $book->identifiers = [];
        if (!empty($web)) {
            $type = 'url';
            $post = (object) ['id' => null, 'type' => $type, 'val' => $web[0]];
            $identifier = new Identifier($post);
            $book->identifiers[] = $identifier;
        }
        $count = $metadata->getElement('PageCount');
        if (!empty($count)) {
            $book->pages = $count[0];
        } else {
            $pages = $metadata->getElement('Pages');
            if (!empty($pages)) {
                $book->pages = count($pages);
            }
        }
        // set fake uuid for cover cache
        if (empty($book->uuid) || str_contains($book->uuid, 'uuid')) {
            $mtime = filemtime($filePath);
            $book->uuid = md5((string) $mtime . '-' . $filePath);
        }
        return $book;
    }

    /**
     * Summary of parseComicInfo
     * @param string $data
     * @return Metadata
     */
    public static function parseComicInfo($data)
    {
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $root = static::getNode($doc, 'ComicInfo');

        $info = new static();
        $info->metadata = static::addNode($root);
        return $info;
    }

    /**
     * Summary of getInstanceByBookId
     * @param int $bookId
     * @param ?int $database
     * @return Metadata|false
     */
    public static function getInstanceByBookId($bookId, $database = null)
    {
        $book = Book::getBookById($bookId, $database);
        if (empty($book)) {
            return false;
        }
        $file = realpath($book->path . '/metadata.opf');
        return self::fromFile($file);
    }
}
