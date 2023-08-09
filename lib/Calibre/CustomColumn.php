<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

/**
 * A CustomColumn with an value
 */
class CustomColumn extends Base
{
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

    /**
     * Get the URI to show all books with this value
     *
     * @return string
     */
    public function getUri()
    {
        return $this->customColumnType->getUri($this->id);
    }

    /**
     * Get the EntryID to show all books with this value
     *
     * @return string
     */
    public function getEntryId()
    {
        return $this->customColumnType->getEntryId($this->id);
    }

    public function getTitle()
    {
        return $this->value;
    }

    public function getLinkArray()
    {
        return $this->customColumnType->getLinkArray($this->id);
    }

    public function getClassName()
    {
        return $this->customColumnType->getTitle();
    }

    public function getCount()
    {
        [$query, $params] = $this->getQuery();
        $columns = 'count(*)';
        $count = Base::countQuery($query, $columns, "", $params, $this->databaseId);
        return $this->getEntry($count);
    }

    /**
     * Get the query to find all books with this value
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->customColumnType->getQuery($this->id);
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

    /** Use inherited class methods to get entries from <Whatever> by customType and valueId (linked via books) */

    public function getBooks($n = -1)
    {
        return Book::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getAuthors($n = -1)
    {
        return Author::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getLanguages($n = -1)
    {
        return Language::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getPublishers($n = -1)
    {
        return Publisher::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getRatings($n = -1)
    {
        return Rating::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getSeries($n = -1)
    {
        return Serie::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
    }

    public function getTags($n = -1)
    {
        return Tag::getEntriesByCustomValueId($this->customColumnType, $this->id, $n, $this->databaseId);
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
