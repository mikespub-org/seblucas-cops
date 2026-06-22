<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Pages\PageId;

class Note extends Base
{
    public const PAGE_ID = PageId::ALL_NOTES_ID;
    public const PAGE_ALL = PageId::ALL_NOTES;
    public const PAGE_TYPE = PageId::ALL_NOTES_TYPE;
    public const PAGE_DETAIL = PageId::NOTE_DETAIL;
    public const ROUTE_ALL = "restapi-notes";
    public const ROUTE_TYPE = "restapi-notes-type";
    public const ROUTE_DETAIL = "restapi-note";
    public const ALLOWED_TYPES = [
        'authors' => Author::class,
        //'languages' => Language::class,
        'publishers' => Publisher::class,
        //'ratings' => Rating::class,
        'series' => Serie::class,
        'tags' => Tag::class,
    ];
    public const SQL_TABLE = "notes_db.notes";
    public const SQL_LINK_TABLE = '{$type}';  // dummy placeholder for $type table
    public const SQL_LINK_COLUMN = "item";
    public const SQL_SORT = "colname, item";
    public const SQL_COLUMNS = "id, item, colname as type, doc as text, mtime";
    public const SQL_ALL_ROWS = "select {0} from notes_db.notes where 1=1 {1}";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from notes_db.notes where upper (strip_html(doc)) like ? {1} group by id order by colname, item";

    public int $item;
    public string $type;
    public string $text;
    public float $mtime;
    public int $size = 0;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?DatabaseContext $dbContext
     */
    public function __construct($post, $dbContext = null)
    {
        $this->dbContext = $dbContext;
        $this->databaseId = $dbContext?->getDatabase() ?? null;
        $this->config = $dbContext?->getConfig() ?? null;
        $this->id = $post->id;
        $this->item = $post->item;
        $this->type = $post->type;
        $this->text = property_exists($post, 'text') ? $post->text : '';
        $this->mtime = $post->mtime;
        $this->size = property_exists($post, 'size') ? $post->size : strlen($this->text);
        $this->name = property_exists($post, 'name') ? $post->name : null;
        $this->link = property_exists($post, 'link') ? $post->link : null;
        $this->setHandler(RestApiHandler::class);
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        if (!empty($this->link)) {
            return $this->link;
        }
        $params['type'] = $this->type;
        $params['item'] = $this->item;
        if (!empty($this->name)) {
            $params['title'] = $this->name;
        }
        return $this->getResource(static::class, $params);
    }

    /**
     * Summary of getTitle
     * @return string|null
     */
    public function getTitle()
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        // @todo get corresponding name from type item instance
        return "Note for {$this->type} #{$this->item}";
    }

    /**
     * Summary of getTypeItem
     * @return Base|null
     */
    public function getTypeItem()
    {
        if (empty($this->type) || empty(self::ALLOWED_TYPES[$this->type]) || empty($this->item)) {
            return null;
        }
        /** @var class-string<Base> $className */
        $className = self::ALLOWED_TYPES[$this->type];
        $instance = $className::getInstanceById($this->item, $this->getDbContext());
        $instance->setHandler($this->handler);
        //$instance->setLocale($this->locale);
        if (empty($this->name)) {
            $this->name = $instance->getTitle();
        }
        // update link to point to instance REST API link here
        if (empty($this->link)) {
            $this->link = $instance->getUri();
        }
        return $instance;
    }

    /**
     * Summary of getResources
     * @return array<mixed>
     */
    public function getResources()
    {
        $dbContext = $this->getDbContext();
        $notesDb = $dbContext->getNotesDb();
        if (is_null($notesDb)) {
            return [];
        }
        $resources = [];
        $query = 'select hash, name from notes_db.resources, notes_db.notes_resources_link where hash = resource and note = ?';
        $params = [$this->id];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        while ($post = $result->fetchObject()) {
            $resources[$post->hash] = new Resource($post, $dbContext);
        }
        return $resources;
    }

    /**
     * Summary of getCountByType
     * @param DatabaseContext $dbContext
     * @return array<mixed>
     */
    public static function getCountByType($dbContext)
    {
        $notesDb = $dbContext->getNotesDb();
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
     * @param DatabaseContext $dbContext
     * @return array<mixed>
     */
    public static function getEntriesByType($type, $dbContext)
    {
        if (!array_key_exists($type, self::ALLOWED_TYPES)) {
            return [];
        }
        $notesDb = $dbContext->getNotesDb();
        if (is_null($notesDb)) {
            return [];
        }
        $entries = [];
        $query = 'select id, item, colname as type, length(doc) as size, mtime from notes_db.notes where colname = ? order by item';
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
        $result = $dbContext->query($query, $itemIdList);
        while ($post = $result->fetchObject()) {
            if (array_key_exists($post->id, $entries)) {
                $entries[$post->id]["name"] = $post->name;
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
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getInstanceByTypeItem($type, $item, $dbContext)
    {
        if (!array_key_exists($type, self::ALLOWED_TYPES)) {
            return null;
        }
        $notesDb = $dbContext->getNotesDb();
        if (is_null($notesDb)) {
            return null;
        }
        $query = 'select id, item, colname as type, doc as text, mtime from notes_db.notes where item = ? and colname = ?';
        $params = [$item, $type];
        $result = $notesDb->prepare($query);
        $result->execute($params);
        if ($post = $result->fetchObject()) {
            return new self($post, $dbContext);
        }
        return null;
    }

    /**
     * Summary of getInstanceByTypeItem
     * @param string $type
     * @param string $name
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getInstanceByTypeName($type, $name, $dbContext)
    {
        if (!array_key_exists($type, self::ALLOWED_TYPES)) {
            return null;
        }
        $notesDb = $dbContext->getNotesDb();
        if (is_null($notesDb)) {
            return null;
        }
        $query = "select id, name from {$type} where name = ?";
        $result = $dbContext->query($query, [$name]);
        if ($post = $result->fetchObject()) {
            $item = (int) $post->id;
            return self::getInstanceByTypeItem($type, $item, $dbContext);
        }
        return null;
    }

    /**
     * Replace note entries with type item entries
     * @param array<Entry> $entryArray
     * @param Request $request
     * @param DatabaseContext $dbContext
     * @param array<mixed> $params set query + scope in instance links
     * @return array<Entry>
     */
    public static function replaceEntryArray($entryArray, $request, $dbContext, $params = [])
    {
        $types = [];
        foreach ($entryArray as $entry) {
            /** @var self $instance */
            $instance = $entry->instance;
            $types[$instance->type] ??= [];
            array_push($types[$instance->type], $instance->item);
        }
        $entries = [];
        foreach ($types as $type => $idlist) {
            $className = self::ALLOWED_TYPES[$type];
            $baselist = new BaseList($className, $request, $dbContext);
            // this expects an array like [$bookId => $instanceIdList]
            $instances = $baselist->getInstancesByIds([1 => $idlist]);
            foreach ($instances as $instance) {
                // set query + scope in instance links
                $entries[] = $instance->getEntry(0, $params);
            }
        }
        if (!empty($entries)) {
            return $entries;
        }
        return $entryArray;
    }

    /**
     * Update entry title and navlink based on type item instance
     * @param array<Entry> $entryArray
     * @param Request $request
     * @param DatabaseContext $dbContext
     * @param array<mixed> $params set query + scope in instance links
     * @return array<Entry>
     */
    public static function updateEntryArray($entryArray, $request, $dbContext, $params = [])
    {
        $types = [];
        foreach ($entryArray as $entry) {
            /** @var self $instance */
            $instance = $entry->instance;
            $types[$instance->type] ??= [];
            array_push($types[$instance->type], $instance->item);
        }
        $items = [];
        foreach ($types as $type => $idlist) {
            $items[$type] = [];
            $className = self::ALLOWED_TYPES[$type];
            $baselist = new BaseList($className, $request, $dbContext);
            // this expects an array like [$bookId => $instanceIdList]
            $instances = $baselist->getInstancesByIds([1 => $idlist]);
            foreach ($instances as $id => $instance) {
                $items[$type][$id] = $instance;
            }
        }
        foreach ($entryArray as $idx => $entry) {
            /** @var self $instance */
            $instance = $entry->instance;
            $type = $instance->type;
            $item = $instance->item;
            if (!empty($items[$type]) && !empty($items[$type][$item])) {
                $entryArray[$idx]->title = $items[$type][$item]->getTitle();
                // set query + scope in instance links
                $entryArray[$idx]->setNavLink($items[$type][$item]->getUri($params));
            }
        }
        return $entryArray;
    }
}
