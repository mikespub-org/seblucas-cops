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
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Pages\PageId;
use JsonException;

class Preference
{
    use HasRouteTrait;

    public const PAGE_ID = PageId::ALL_PREFERENCES_ID;
    public const PAGE_ALL = PageId::ALL_PREFERENCES;
    public const PAGE_DETAIL = PageId::PREFERENCE_DETAIL;
    public const ROUTE_ALL = "restapi-preferences";
    public const ROUTE_DETAIL = "restapi-preference";
    public const SQL_TABLE = "preferences";
    public const SQL_COLUMNS = "id, key, val";

    public int $id;
    public string $key;
    public mixed $val;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->key = $post->key;
        try {
            $this->val = json_decode($post->val, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->val = $post->val;
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
        $params['key'] = $this->key;
        return $this->getResource(static::class, $params);
    }

    /**
     * Summary of getInstances
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getInstances($database = null)
    {
        $preferences = [];
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' order by key';
        $result = Database::query($query, [], $database);
        while ($post = $result->fetchObject()) {
            $preferences[$post->key] = new self($post, $database);
        }
        return $preferences;
    }

    /**
     * Summary of getInstanceByKey
     * @param string $key
     * @param ?int $database
     * @return self|null
     */
    public static function getInstanceByKey($key, $database = null)
    {
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' where key = ?';
        $params = [$key];
        $result = Database::query($query, $params, $database);
        if ($post = $result->fetchObject()) {
            return new self($post, $database);
        }
        return null;
    }

    /**
     * Summary of getVirtualLibraries
     * {
     *   "Both Authors": "authors:\"=Author Two\" and authors:\"=Author One\"",
     *   "Kindle 2": "tags:\"=Kindle_Mike\" or tags:\"=Kindle_Luca\"",
     *   "No Device": "not tags:\"=Kindle_Mike\" and not tags:\"=Kindle_Luca\" and not tags:\"=Kindle_Lydia\""
     * }
     * See https://github.com/seblucas/cops/pull/233
     * @param ?int $database
     * @return self|null
     */
    public static function getVirtualLibraries($database = null)
    {
        return self::getInstanceByKey('virtual_libraries', $database);
    }

    /**
     * Summary of getCategoriesUsingHierarchy
     * @param ?int $database
     * @return self|null
     */
    public static function getCategoriesUsingHierarchy($database = null)
    {
        return self::getInstanceByKey('categories_using_hierarchy', $database);
    }

    /**
     * Summary of getFieldMetadata
     * @param ?int $database
     * @return self|null
     */
    public static function getFieldMetadata($database = null)
    {
        // @todo investigate format
        return self::getInstanceByKey('field_metadata', $database);
    }

    /**
     * Summary of getUserCategories
     * @param ?int $database
     * @return self|null
     */
    public static function getUserCategories($database = null)
    {
        // @todo investigate format
        return self::getInstanceByKey('user_categories', $database);
    }

    /**
     * Summary of getSavedSearches
     * {
     *   "Author One": "authors:one and not authors:two"
     * }
     * @param ?int $database
     * @return self|null
     */
    public static function getSavedSearches($database = null)
    {
        // @todo map search string from saved search to filters
        return self::getInstanceByKey('saved_searches', $database);
    }
}
