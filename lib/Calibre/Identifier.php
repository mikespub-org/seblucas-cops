<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     SenorSmartyPants <senorsmartypants@gmail.com>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\PageId;

class Identifier extends Base
{
    public const PAGE_ID = PageId::ALL_IDENTIFIERS_ID;
    public const PAGE_ALL = PageId::ALL_IDENTIFIERS;
    public const PAGE_DETAIL = PageId::IDENTIFIER_DETAIL;
    public const SQL_TABLE = "identifiers";
    public const SQL_LINK_TABLE = "identifiers";
    public const SQL_LINK_COLUMN = "type";
    public const SQL_SORT = "type";
    public const SQL_COLUMNS = "identifiers.type as id, identifiers.type as type, '' as val";
    public const SQL_ALL_ROWS = "select {0} from identifiers where 1=1 {1} group by identifiers.type order by identifiers.type";
    public const SQL_ROWS_FOR_SEARCH = "";  // "select {0} from tags, books_tags_link where tags.id = tag and upper (tags.name) like ? {1} group by tags.id, tags.name order by tags.name";
    public const SQL_BOOKLIST = 'select {0} from identifiers, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where identifiers.book = books.id and identifiers.type = ? {1} order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from identifiers) {1} order by books.sort';
    public const URL_PARAM = "i";

    /** @var ?int */
    public $id;
    /** @var string */
    public $type;
    public string $formattedType;
    /** @var string */
    public $val;
    public string $uri;
    /** @var ?int */
    protected $databaseId;

    /**
     * Summary of __construct
     * @param object $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->type = strtolower($post->type);
        $this->val = $post->val;
        $this->formatType();
        $this->databaseId = $database;
    }

    /**
     * Summary of formatType
     * @return void
     */
    public function formatType()
    {
        if ($this->type == 'amazon') {
            $this->formattedType = "Amazon";
            $this->uri = sprintf("https://amazon.com/dp/%s", $this->val);
        } elseif ($this->type == "asin") {
            $this->formattedType = $this->type;
            $this->uri = sprintf("https://amazon.com/dp/%s", $this->val);
        } elseif (str_starts_with($this->type, "amazon_")) {
            $this->formattedType = sprintf("Amazon.co.%s", substr($this->type, 7));
            $this->uri = sprintf("https://amazon.co.%s/dp/%s", substr($this->type, 7), $this->val);
        } elseif ($this->type == "isbn") {
            $this->formattedType = "ISBN";
            $this->uri = sprintf("https://www.worldcat.org/isbn/%s", $this->val);
        } elseif ($this->type == "doi") {
            $this->formattedType = "DOI";
            $this->uri = sprintf("https://dx.doi.org/%s", $this->val);
        } elseif ($this->type == "douban") {
            $this->formattedType = "Douban";
            $this->uri = sprintf("https://book.douban.com/subject/%s", $this->val);
        } elseif ($this->type == "goodreads") {
            $this->formattedType = "Goodreads";
            $this->uri = sprintf("https://www.goodreads.com/book/show/%s", $this->val);
        } elseif ($this->type == "google") {
            $this->formattedType = "Google Books";
            $this->uri = sprintf("https://books.google.com/books?id=%s", $this->val);
        } elseif ($this->type == "kobo") {
            $this->formattedType = "Kobo";
            $this->uri = sprintf("https://www.kobo.com/ebook/%s", $this->val);
        } elseif ($this->type == "litres") {
            $this->formattedType = "ЛитРес";
            $this->uri = sprintf("https://www.litres.ru/%s", $this->val);
        } elseif ($this->type == "issn") {
            $this->formattedType = "ISSN";
            $this->uri = sprintf("https://portal.issn.org/resource/ISSN/%s", $this->val);
        } elseif ($this->type == "isfdb") {
            $this->formattedType = "ISFDB";
            $this->uri = sprintf("http://www.isfdb.org/cgi-bin/pl.cgi?%s", $this->val);
        } elseif ($this->type == "lubimyczytac") {
            $this->formattedType = "Lubimyczytac";
            $this->uri = sprintf("https://lubimyczytac.pl/ksiazka/%s/ksiazka", $this->val);
        } elseif ($this->type == "wd") {
            $this->formattedType = "Wikidata";
            $this->uri = sprintf("https://www.wikidata.org/entity/%s", $this->val);
        } elseif ($this->type == "ltid") {
            $this->formattedType = "LibraryThing";
            $this->uri = sprintf("https://www.librarything.com/work/book/%s", $this->val);
        } elseif ($this->type == "olid") {
            $this->formattedType = "OpenLibrary";
            $this->uri = sprintf("https://openlibrary.org/works/%s", $this->val);
        } elseif ($this->type == "url") {
            $this->formattedType = $this->type;
            $this->uri = $this->val;
        } else {
            $this->formattedType = $this->type;
            $this->uri = '';
        }
    }

    /**
     * Summary of getTitle
     * @return mixed
     */
    public function getTitle()
    {
        return $this->formattedType;
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("identifiers.title");
    }

    /**
     * Summary of getLink
     * @return string
     */
    public function getLink()
    {
        return $this->uri;
    }

    /**
     * Summary of getInstanceById
     * @param string|int|null $id used for the type of identifier here
     * @param ?int $database
     * @return object
     */
    public static function getInstanceById($id, $database = null)
    {
        if (isset($id)) {
            return new Identifier((object) ['id' => $id, 'type' => $id, 'val' => ''], $database);
        }
        $default = static::getDefaultName();
        return new Identifier((object) ['id' => null, 'type' => $default, 'val' => ''], $database);
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return localize("identifierword.none");
    }

    /**
     * Summary of getInstancesByBookId
     * @param int $bookId
     * @param ?int $database
     * @return array<Identifier>
     */
    public static function getInstancesByBookId($bookId, $database = null)
    {
        $identifiers = [];

        $query = 'select type, val, id
            from identifiers
            where book = ?
            order by type';
        $result = Database::query($query, [$bookId], $database);
        while ($post = $result->fetchObject()) {
            array_push($identifiers, new Identifier($post, $database));
        }
        return $identifiers;
    }
}
