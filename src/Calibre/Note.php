<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;

class Note
{
    public const PAGE_ID = PageId::ALL_NOTES_ID;
    public const PAGE_ALL = PageId::ALL_NOTES;
    public const PAGE_TYPE = PageId::ALL_NOTES_TYPE;
    public const PAGE_DETAIL = PageId::NOTE_DETAIL;
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
    protected string $handler = '';

    /**
     * Summary of __construct
     * @param object $post
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
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        // @todo get handler from somewhere
        return Route::link($this->handler) . '/notes/' . $this->colname . '/' . $this->item;
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
        $query = 'select hash, name from resources, notes_resources_link where resources.hash = resource and note = ?';
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
        $query = 'select colname as type, count(*) as count from notes group by colname order by colname';
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
        if (!array_key_exists($type, static::ALLOWED_FIELDS)) {
            return [];
        }
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return [];
        }
        $entries = [];
        $query = 'select item, length(doc) as size, mtime from notes where colname = ? order by item';
        $params = [$type];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        while ($post = $result->fetchObject()) {
            $entries[$post->item] = (array) $post;
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
            }
        }
        return $entries;
    }

    /**
     * Summary of getInstanceByTypeId
     * @param string $type
     * @param int $id
     * @param ?int $database
     * @return self|null
     */
    public static function getInstanceByTypeId($type, $id, $database = null)
    {
        if (!array_key_exists($type, static::ALLOWED_FIELDS)) {
            return null;
        }
        $notesDb = Database::getNotesDb($database);
        if (is_null($notesDb)) {
            return null;
        }
        $query = 'select id, item, colname, doc, mtime from notes where item = ? and colname = ?';
        $params = [$id, $type];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        if ($post = $result->fetchObject()) {
            return new self($post, $database);
        }
        return null;
    }
}
