<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\EPubMeta\EPub;
use SebLucas\EPubMeta\Metadata as EPubMetadata;

/**
 * Calibre metadata.opf files are based on EPUB 2.0 <https://idpf.org/epub/20/spec/OPF_2.0_latest.htm#Section2.0>,
 * not EPUB 3.x <https://www.w3.org/TR/epub-33/#sec-package-doc>
 */
class Metadata extends EPubMetadata
{
    public const ROUTE_DETAIL = "restapi-metadata";
    public const ROUTE_ELEMENT = "restapi-metadata-element";
    public const ROUTE_ELEMENT_NAME = "restapi-metadata-element-name";

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
        return $book;
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
        return $book;
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
