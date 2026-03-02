<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Pages\PageId;

class Note
{
    use HasRouteTrait;

    public const PAGE_ID = PageId::ALL_NOTES_ID;
    public const PAGE_ALL = PageId::ALL_NOTES;
    public const PAGE_TYPE = PageId::ALL_NOTES_TYPE;
    public const PAGE_DETAIL = PageId::NOTE_DETAIL;
    public const ROUTE_ALL = "restapi-notes";
    public const ROUTE_TYPE = "restapi-notes-type";
    public const ROUTE_DETAIL = "restapi-note";
    public const ALLOWED_FIELDS = [
        'authors' => Author::class,
        //'languages' => Language::class,
        'publishers' => Publisher::class,
        //'ratings' => Rating::class,
        'series' => Serie::class,
        'tags' => Tag::class,
    ];

    public int $id;
    public int $item;
    public string $colname;
    public string $doc;
    public float $mtime;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->item = $post->item;
        $this->colname = $post->colname;
        $this->doc = $post->doc;
        $this->mtime = $post->mtime;
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
        $params['type'] = $this->colname;
        $params['item'] = $this->item;
        return $this->getResource(static::class, $params);
    }

    /**
     * Summary of getTitle
     * @return string|null
     */
    public function getTitle()
    {
        // @todo get corresponding title from item instance
        return '';
    }

    /**
     * Summary of getResources
     * @return array<mixed>
     */
    public function getResources()
    {
        $notesDb = Database::getNotesDb($this->databaseId);
        if (is_null($notesDb)) {
            return [];
        }
        $resources = [];
        $query = 'select hash, name from notes_db.resources, notes_db.notes_resources_link where hash = resource and note = ?';
        $params = [$this->id];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        while ($post = $result->fetchObject()) {
            $resources[$post->hash] = new Resource($post, $this->databaseId);
        }
        return $resources;
    }

    /**
     * Summary of getCountByType
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getCountByType($database = null)
    {
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return [];
        }
        $entries = [];
        $query = 'select colname as type, count(*) as count from notes_db.notes group by colname order by colname';
        $result = $notesDb->prepare($query);
        $result->execute();
        while ($post = $result->fetchObject()) {
            $entries[$post->type] = $post->count;
        }
        return $entries;
    }

    /**
     * Summary of getEntriesByType
     * @param string $type
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getEntriesByType($type, $database = null)
    {
        if (!array_key_exists($type, self::ALLOWED_FIELDS)) {
            return [];
        }
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return [];
        }
        $entries = [];
        $query = 'select item, length(doc) as size, mtime from notes_db.notes where colname = ? order by item';
        $params = [$type];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        while ($post = $result->fetchObject()) {
            $entries[$post->item] = (array) $post;
            // @todo add link to resource
            //$link = RestApiHandler::resource(self::class, $params);
        }
        $itemIdList = array_keys($entries);
        if (empty($itemIdList)) {
            return $entries;
        }
        $query = "select id, name from {$type} where id in (" . str_repeat('?,', count($itemIdList) - 1) . '?)';
        $result = Database::query($query, $itemIdList, $database);
        while ($post = $result->fetchObject()) {
            if (array_key_exists($post->id, $entries)) {
                $entries[$post->id]["title"] = $post->name;
                // @todo add link to resource
                //$link = RestApiHandler::resource(self::class, $params);
            }
        }
        return $entries;
    }

    /**
     * Summary of getInstanceByTypeItem
     * @param string $type
     * @param int $item
     * @param ?int $database
     * @return self|null
     */
    public static function getInstanceByTypeItem($type, $item, $database = null)
    {
        if (!array_key_exists($type, self::ALLOWED_FIELDS)) {
            return null;
        }
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return null;
        }
        $query = 'select id, item, colname, doc, mtime from notes_db.notes where item = ? and colname = ?';
        $params = [$item, $type];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        if ($post = $result->fetchObject()) {
            return new self($post, $database);
        }
        return null;
    }

    /**
     * Summary of getInstanceByTypeItem
     * @param string $type
     * @param string $name
     * @param ?int $database
     * @return self|null
     */
    public static function getInstanceByTypeName($type, $name, $database = null)
    {
        if (!array_key_exists($type, self::ALLOWED_FIELDS)) {
            return null;
        }
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return null;
        }
        $query = "select id, name from {$type} where name = ?";
        $result = Database::query($query, [$name], $database);
        if ($post = $result->fetchObject()) {
            $item = (int) $post->id;
            return self::getInstanceByTypeItem($type, $item, $database);
        }
        return null;
    }
}
