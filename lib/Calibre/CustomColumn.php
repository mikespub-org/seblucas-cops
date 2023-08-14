<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

/**
 * A CustomColumn with an value
 */
class CustomColumn extends Category
{
    public const PAGE_ID = Page::ALL_CUSTOMS_ID;
    public const PAGE_ALL = Page::ALL_CUSTOMS;
    public const PAGE_DETAIL = Page::CUSTOM_DETAIL;

    /** @var string the (string) representation of the value */
    public $value;
    /** @var CustomColumnType the custom column that contains the value */
    public $customColumnType;
    /** @var string the value encoded for HTML displaying */
    public $htmlvalue;

    /**
     * CustomColumn constructor.
     *
     * @param integer|string|null $pid id of the chosen value
     * @param string $pvalue string representation of the value
     * @param CustomColumnType $pcustomColumnType the CustomColumn this value lives in
     */
    public function __construct($pid, $pvalue, $pcustomColumnType)
    {
        $this->id = $pid;
        $this->value = $pvalue;
        $this->customColumnType = $pcustomColumnType;
        $this->htmlvalue = $this->customColumnType->encodeHTMLValue($this->value);
        $this->databaseId = $this->customColumnType->getDatabaseId();
    }

    public function getCustomId()
    {
        return $this->customColumnType->customId;
    }

    /**
     * Get the URI to show all books with this value
     *
     * @return string
     */
    public function getUri()
    {
        return "?page=" . self::PAGE_DETAIL . "&custom={$this->getCustomId()}&id={$this->id}";
    }

    public function getParentUri()
    {
        return $this->customColumnType->getUri();
    }

    /**
     * Get the EntryID to show all books with this value
     *
     * @return string
     */
    public function getEntryId()
    {
        return self::PAGE_ID . ":" . $this->getCustomId() . ":" . $this->id;
    }

    public function getTitle()
    {
        return $this->value;
    }

    public function getParentTitle()
    {
        return $this->customColumnType->getTitle();
    }

    public function getClassName()
    {
        return $this->customColumnType->getTitle();
    }

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
     * @return array{0: string, 1: array}
     */
    public function getQuery()
    {
        return $this->customColumnType->getQuery($this->id);
    }

    public function getFilter($parentTable = null)
    {
        return $this->customColumnType->getFilter($this->id, $parentTable);
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

    public function hasChildCategories()
    {
        return $this->customColumnType->hasChildCategories();
    }

    /**
     * Find related categories for hierarchical custom columns
     * Format: tag_browser_custom_column_2(id,value,count,avg_rating,sort)
     * @param mixed $find
     * @return array
     */
    public function getRelatedCategories($find)
    {
        return $this->customColumnType->getRelatedCategories($find);
    }

    /**
     * Create an CustomColumn by CustomColumnID and ValueID
     *
     * @param integer $customId the id of the customColumn
     * @param integer $id the id of the chosen value
     * @param mixed $database
     * @return CustomColumn|null
     */
    public static function createCustom($customId, $id, $database = null)
    {
        $columnType = CustomColumnType::createByCustomID($customId, $database);

        return $columnType->getCustom($id);
    }

    /**
     * Return this object as an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'valueID'          => $this->id,
            'value'            => $this->value,
            'customColumnType' => (array)$this->customColumnType,
            'htmlvalue'        => $this->htmlvalue,
        ];
    }
}
