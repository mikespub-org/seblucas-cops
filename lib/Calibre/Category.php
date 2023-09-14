<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * Note: this could become a trait, but for now it fits inheritance
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;

abstract class Category extends Base
{
    public const CATEGORY = "categories";
    /** @var ?int */
    public $count;
    /** @var ?array<Category> */
    protected $children = null;
    /** @var Category|false|null */
    protected $parent = null;

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        if (empty(Config::get('calibre_categories_using_hierarchy')) || !in_array(static::CATEGORY, Config::get('calibre_categories_using_hierarchy'))) {
            return false;
        }
        return true;
    }

    /**
     * Get child instances for hierarchical tags or custom columns
     * @return array<Category>
     */
    public function getChildCategories()
    {
        if (!is_null($this->children)) {
            return $this->children;
        }
        // Fiction -> Fiction.% matching Fiction.Historical and Fiction.Romance
        $find = $this->getTitle() . '.%';
        $this->children = $this->getRelatedCategories($find);
        return $this->children;
    }

    /**
     * Get child entries for hierarchical tags or custom columns
     * @param int|bool|null $expand include all child categories at all levels or only direct children
     * @return array<Entry>
     */
    public function getChildEntries($expand = false)
    {
        $entryArray = [];
        foreach ($this->getChildCategories() as $child) {
            // check if this is an immediate child or not, like Fiction matches Fiction.Historical but not Fiction.Historical.Romance
            if (empty($expand) && !preg_match('/^' . $this->getTitle() . '\.[^.]+$/', $child->getTitle())) {
                continue;
            }
            array_push($entryArray, $child->getEntry($child->count));
        }
        return $entryArray;
    }

    /**
     * Get sibling entries for hierarchical tags or custom columns
     * @return array<Entry>
     */
    public function getSiblingEntries()
    {
        // Fiction.Historical -> Fiction.% matching Fiction.Historical and Fiction.Romance
        $parentName = static::findParentName($this->getTitle());
        if (empty($parentName)) {
            return [];
        }
        // pattern match here
        $find = $parentName . '.%';
        $siblings = $this->getRelatedCategories($find);
        $entryArray = [];
        foreach ($siblings as $sibling) {
            array_push($entryArray, $sibling->getEntry($sibling->count));
        }
        return $entryArray;
    }

    /**
     * Find parent name of hierarchical name
     * @param string $name
     * @return string
     */
    protected static function findParentName($name)
    {
        $parts = explode('.', $name);
        $child = array_pop($parts);
        if (empty($parts)) {
            return '';
        }
        $parent = implode('.', $parts);
        return $parent;
    }

    /**
     * Get parent instance for hierarchical tags or custom columns
     * @return Category|false
     */
    public function getParentCategory()
    {
        if (!is_null($this->parent)) {
            return $this->parent;
        }
        $this->parent = false;
        // Fiction.Historical -> Fiction
        $parentName = static::findParentName($this->getTitle());
        if (empty($parentName)) {
            return $this->parent;
        }
        // exact match here
        $find = $parentName;
        $parents = $this->getRelatedCategories($find);
        if (count($parents) == 1) {
            $this->parent = $parents[0];
        }
        return $this->parent;
    }

    /**
     * Get parent entry for hierarchical tags or custom columns
     * @return ?Entry
     */
    public function getParentEntry()
    {
        $parent = $this->getParentCategory();
        if (!empty($parent)) {
            return $parent->getEntry($parent->count);
        }
        return null;
    }

    /**
     * Find related categories for hierarchical tags or series - @todo needs title_sort function in sqlite for series
     * Format: tag_browser_tags(id,name,count,avg_rating,sort)
     * @param string|array<mixed> $find pattern match or exact match for name, or array of child ids
     * @return array<Category>
     */
    public function getRelatedCategories($find)
    {
        if (!$this->hasChildCategories()) {
            return [];
        }
        $className = get_class($this);
        $tableName = 'tag_browser_' . static::CATEGORY;
        if (is_array($find)) {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE id IN (" . str_repeat("?,", count($find) - 1) . "?) ORDER BY sort";
            $params = $find;
        } elseif (strpos($find, '%') === false) {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE name = ? ORDER BY sort";
            $params = [$find];
        } else {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE name LIKE ? ORDER BY sort";
            $params = [$find];
        }
        $query = str_format($queryFormat, $tableName);
        $result = Database::query($query, $params, $this->databaseId);

        $instances = [];
        while ($post = $result->fetchObject()) {
            /** @var Category $instance */
            $instance = new $className($post, $this->databaseId);
            $instance->count = $post->count;
            array_push($instances, $instance);
        }
        return $instances;
    }
}
