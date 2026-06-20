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
     * @param DatabaseContext $dbContext
     * @return array<mixed>
     */
    public static function getInstances($dbContext)
    {
        $preferences = [];
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' order by key';
        $result = $dbContext->query($query, []);
        while ($post = $result->fetchObject()) {
            $preferences[$post->key] = new self($post, $dbContext->getDatabase());
        }
        return $preferences;
    }

    /**
     * Summary of getInstanceByKey
     * @param string $key
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getInstanceByKey($key, $dbContext)
    {
        $query = 'select ' . self::SQL_COLUMNS . ' from ' . self::SQL_TABLE . ' where key = ?';
        $params = [$key];
        $result = $dbContext->query($query, $params);
        if ($post = $result->fetchObject()) {
            return new self($post, $dbContext->getDatabase());
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
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getVirtualLibraries($dbContext)
    {
        return self::getInstanceByKey('virtual_libraries', $dbContext);
    }

    /**
     * Summary of getCategoriesUsingHierarchy
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getCategoriesUsingHierarchy($dbContext)
    {
        return self::getInstanceByKey('categories_using_hierarchy', $dbContext);
    }

    /**
     * Summary of getFieldMetadata
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getFieldMetadata($dbContext)
    {
        // @todo investigate format
        return self::getInstanceByKey('field_metadata', $dbContext);
    }

    /**
     * Summary of getUserCategories
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getUserCategories($dbContext)
    {
        // @todo investigate format
        return self::getInstanceByKey('user_categories', $dbContext);
    }

    /**
     * Summary of getSavedSearches
     * {
     *   "Author One": "authors:one and not authors:two"
     * }
     * @param DatabaseContext $dbContext
     * @return self|null
     */
    public static function getSavedSearches($dbContext)
    {
        // @todo map search string from saved search to filters
        return self::getInstanceByKey('saved_searches', $dbContext);
    }
}
