<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Pages\PageId;
use JsonException;

class Annotation extends Base
{
    public const PAGE_ID = PageId::ALL_ANNOTATIONS_ID;
    public const PAGE_ALL = PageId::ALL_ANNOTATIONS;
    public const PAGE_BOOK = PageId::ANNOTATIONS_BOOK;
    public const PAGE_DETAIL = PageId::ANNOTATION_DETAIL;
    public const ROUTE_ALL = "restapi-annotations";
    public const ROUTE_BOOK = "restapi-annotations-book";
    public const ROUTE_DETAIL = "restapi-annotation";
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
     * @param \stdClass $post
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
        } catch (JsonException) {
            $this->data = [ $post->annot_data ];
        }
        $this->databaseId = $database;
        $this->setHandler(RestApiHandler::class);
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
        return $this->getResource(static::class, $params);
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
        $query = 'select ' . self::getInstanceColumns($database) . '
from annotations
where book = ?';
        $result = Database::query($query, [$bookId], $database);
        $annotationArray = [];
        while ($post = $result->fetchObject()) {
            array_push($annotationArray, new Annotation($post, $database));
        }
        return $annotationArray;
    }

    /**
     * Forget about converting annotations
     *
     * Epub CFIs are *not* compatible between Calibre, Kobo, epub.js, readium.js etc.
     * See https://github.com/futurepress/epub.js/issues/1358 for comments and links
     *
     * @return void
     */
    private static function forgetAboutConvertAnnotations()
    {
        /**
        From Calibre annotations:
        {
            "id": 2,
            "book": 17,
            "format": "EPUB",
            "userType": "local",
            "user": "viewer",
            "timestamp": 1710158035.583,
            "type": "highlight",
            "data": {
                "end_cfi": "/2/4/2/2/6/1:24",
                "highlighted_text": "Charles Lutwidge Dodgson",
                "notes": "Full author name",
                "spine_index": 2,
                "spine_name": "OPS/about.xml",
                "start_cfi": "/2/4/2/2/6/1:0",
                "style": {
                    "kind": "color",
                    "type": "builtin",
                    "which": "yellow"
                },
                "timestamp": "2024-03-11T11:53:55.583Z",
                "toc_family_titles": [
                    "About"
                ],
                "type": "highlight",
                "uuid": "5HHGuoCOtpA-umaIbBuc0Q"
            }
        }
        From epub.js local storage in Chrome:
        {
            "restore": true,
            "bookPath": "/cops/zipfs.php/0/20/",
            "flow": "paginated",
            "history": true,
            "reload": false,
            "bookmarks": [
                "epubcfi(/6/8!/4/2/2[chapter_458]/2/2/2/1:0)",
                "epubcfi(/6/10!/4/2/2[chapter_460]/4/26/1:372)"
            ],
            "annotations": [
                {
                    "cfi": "epubcfi(/6/6!/4/2/2/2,/1:0,/1:13)",
                    "date": "2024-03-13T10:11:48.731Z",
                    "text": "About author",
                    "uuid": "60c92408-81b9-47d9-f705-66f26317f2ee"
                },
                {
                    "cfi": "epubcfi(/6/6!/4/2/2/6,/1:0,/1:24)",
                    "date": "2024-03-13T10:19:09.443Z",
                    "text": "Actual person",
                    "uuid": "71aad015-28f9-4088-abce-48762492b52e"
                },
                {
                    "cfi": "epubcfi(/6/6!/4/2/2/6,/1:93,/1:106)",
                    "date": "2024-03-13T10:19:31.777Z",
                    "text": "Pen name",
                    "uuid": "66f559ae-f8a5-4b39-b3cc-673a08d67f00"
                }
            ],
            "sectionId": "level1-about",
            "spread": {
                "mod": "auto",
                "min": 800
            },
            "styles": {
                "fontSize": 100
            },
            "pagination": false,
            "language": "en",
            "previousLocationCfi": "epubcfi(/6/6!/4/2/2/2/1:0)"
        }
         */
    }
}
