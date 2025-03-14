<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * A CustomColumn with an value
 */
class CustomColumn extends Category
{
    public const PAGE_ID = PageId::ALL_CUSTOMS_ID;
    public const PAGE_ALL = PageId::ALL_CUSTOMS;
    public const PAGE_DETAIL = PageId::CUSTOM_DETAIL;
    public const ROUTE_ALL = "page-customtype";
    public const ROUTE_DETAIL = "page-custom";
    public const URL_PARAM = "c";
    public const CATEGORY = "custom_column";
    public const NOT_SET = "not_set";

    /** @var string the (string) representation of the value */
    public $value;
    /** @var CustomColumnType the custom column that contains the value */
    public $customColumnType;
    /** @var string the value encoded for HTML displaying */
    public $htmlvalue;

    /**
     * CustomColumn constructor.
     *
     * @param integer|string|null $id id of the chosen value
     * @param string $value string representation of the value
     * @param CustomColumnType $customColumnType the CustomColumn this value lives in
     */
    public function __construct($id, $value, $customColumnType)
    {
        $this->id = $id;
        $this->value = (string) $value;
        $this->customColumnType = $customColumnType;
        $this->htmlvalue = $this->customColumnType->encodeHTMLValue($this->value);
        $this->databaseId = $this->customColumnType->getDatabaseId();
        $this->handler = $this->customColumnType->getHandler();
    }

    /**
     * Summary of getCustomId
     * @return int
     */
    public function getCustomId()
    {
        return $this->customColumnType->customId;
    }

    /**
     * Get the URI to show all books with this value
     *
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        // @todo handle case where we have several values, e.g. array of text for type 2 (csv)
        $params['custom'] = $this->getCustomId();
        $params['id'] = $this->id ?? self::NOT_SET;
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->getDatabaseId();
        return $this->getRoute(self::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getParentUri
     * @param array<mixed> $params
     * @return string
     */
    public function getParentUri($params = [])
    {
        return $this->customColumnType->getUri($params);
    }

    /**
     * Get the EntryID to show all books with this value
     *
     * @return string
     */
    public function getEntryId()
    {
        return self::PAGE_ID . ":" . strval($this->getCustomId()) . ":" . $this->id;
    }

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return strval($this->value);
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return $this->customColumnType->getTitle();
    }

    /**
     * Summary of getClassName
     * @param ?class-string $className
     * @return string
     */
    public function getClassName($className = null)
    {
        return $this->customColumnType->getTitle();
    }

    /**
     * Summary of getCustomCount
     * @return Entry
     */
    public function getCustomCount()
    {
        [$query, $params] = $this->getQuery();
        $columns = 'count(*)';
        $count = Database::countFilter($query, $columns, "", $params, $this->databaseId);
        return $this->getEntry($count);
    }

    /**
     * Get the query to find all books with this value
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     *
     * @return array{0: string, 1: array<mixed>}
     */
    public function getQuery()
    {
        return $this->customColumnType->getQuery($this->id);
    }

    /**
     * Summary of getFilter
     * @param ?string $parentTable
     * @return array{0: string, 1: array<mixed>}
     */
    public function getFilter($parentTable = null)
    {
        return $this->customColumnType->getFilter($this->id, $parentTable);
    }

    /**
     * Summary of getExtraParams if we want to add extra params to entry links etc.
     * @return array<string, mixed>
     */
    public function getExtraParams()
    {
        return array_merge([static::URL_PARAM => [strval($this->getCustomId()) => $this->id ?? self::NOT_SET]], $this->filterParams);
    }

    /**
     * Return the value of this column as an HTML snippet
     *
     * @return string
     */
    public function getHTMLEncodedValue()
    {
        return $this->htmlvalue;
    }

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        return $this->customColumnType->hasChildCategories();
    }

    /**
     * Summary of getParentByName
     * @param string $parentName
     * @return CustomColumn
     */
    public function getParentByName($parentName)
    {
        return $this->customColumnType->getCustomByValue($parentName);
    }

    /**
     * Find related categories for hierarchical custom columns
     * Format: tag_browser_custom_column_2(id,value,count,avg_rating,sort)
     * @param string|array<mixed> $find
     * @return array<CustomColumn>
     */
    public function getRelatedCategories($find)
    {
        return $this->customColumnType->getRelatedCategories($find);
    }

    /**
     * Create missing parent for hierarchy
     * @param string $name
     * @return CustomColumn
     */
    public function createMissingParent($name)
    {
        return $this->customColumnType->createMissingParent($name);
    }

    /**
     * Create an CustomColumn by CustomColumnID and ValueID
     *
     * @param int $customId the id of the customColumn
     * @param string|int|null $id the id of the chosen value
     * @param ?int $database
     * @return ?CustomColumn
     */
    public static function createCustom($customId, $id, $database = null)
    {
        $columnType = CustomColumnType::createByCustomID($customId, $database);

        return $columnType->getCustom($id);
    }

    /**
     * Summary of setHandler
     * @param class-string<BaseHandler> $handler
     * @return void
     */
    public function setHandler($handler)
    {
        // set handler for customColumnType too - see getParentUri()
        $this->customColumnType->setHandler($handler);
        $this->handler = $handler;
    }

    /**
     * Return this object as an array
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'valueID'          => $this->id,
            'value'            => $this->value,
            'customColumnType' => $this->customColumnType->toArray(),
            'htmlvalue'        => $this->htmlvalue,
            'url'              => $this->getUri(),
        ];
    }
}
