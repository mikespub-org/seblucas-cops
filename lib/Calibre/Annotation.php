<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;
use JsonException;

class Annotation extends Base
{
    public const PAGE_ID = PageId::ALL_ANNOTATIONS_ID;
    public const PAGE_ALL = PageId::ALL_ANNOTATIONS;
    public const PAGE_BOOK = PageId::ANNOTATIONS_BOOK;
    public const PAGE_DETAIL = PageId::ANNOTATION_DETAIL;
    public const SQL_TABLE = "annotations";
    public const SQL_LINK_TABLE = "annotations";
    public const SQL_LINK_COLUMN = "id";
    public const SQL_SORT = "id";
    public const SQL_COLUMNS = "id, book, format, user_type, user, timestamp, annot_id, annot_type, annot_data";
    public const SQL_ALL_ROWS = "select {0} from annotations where 1=1 {1}";

    public int $book;
    public string $format;
    public string $userType;
    public string $user;
    public float $timestamp;
    public string $type;
    /** @var array<mixed> */
    public array $data;

    /**
     * Summary of __construct
     * @param object $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->book = $post->book;
        $this->format = $post->format;
        $this->userType = $post->user_type;
        $this->user = $post->user;
        $this->timestamp = $post->timestamp;
        $this->name = $post->annot_id;
        $this->type = $post->annot_type;
        try {
            $this->data = json_decode($post->annot_data, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->data = [ $post->annot_data ];
        }
        $this->databaseId = $database;
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['bookId'] = $this->book;
        $params['id'] = $this->id;
        // @todo use route urls
        return Route::page(static::PAGE_DETAIL, $params);
    }

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return '(' . strval($this->book) . ') ' . ucfirst($this->type) . ' ' . $this->name;
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    /**
     * Summary of getCountByBookId
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getCountByBookId($database = null)
    {
        $entries = [];
        $query = 'select book, count(*) as count from annotations group by book order by book';
        $result = Database::query($query, [], $database);
        while ($post = $result->fetchObject()) {
            $entries[$post->book] = $post->count;
        }
        return $entries;
    }

    /**
     * Summary of getInstancesByBookId
     * @param int $bookId
     * @param ?int $database
     * @return array<Annotation>
     */
    public static function getInstancesByBookId($bookId, $database = null)
    {
        // @todo filter by format, user, annotType etc.
        $query = 'select ' . static::getInstanceColumns($database) . '
from annotations
where book = ?';
        $result = Database::query($query, [$bookId], $database);
        $annotationArray = [];
        while ($post = $result->fetchObject()) {
            array_push($annotationArray, new Annotation($post, $database));
        }
        return $annotationArray;
    }
}
