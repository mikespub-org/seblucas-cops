<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * Note: this could become a trait, but for now it fits inheritance
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;
use Exception;

abstract class Category extends Base
{
    public const SQL_CREATE = 'insert into categories (name) values (?)';
    public const CATEGORY = "categories";

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
                // @todo add child count to parent
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
            // skip current entry
            if ($sibling->id == $this->id) {
                continue;
            }
            // skip entries deeper in hierarchy like Fiction.Historical.Mystery
            $siblingName = substr($sibling->getTitle(), strlen($parentName) + 1);
            if (str_contains($siblingName, '.')) {
                continue;
            }
            array_push($entryArray, $sibling->getEntry($sibling->count));
        }
        return $entryArray;
    }

    /**
     * Find current name of hierarchical name
     * @param string $name
     * @return string
     */
    public static function findCurrentName($name)
    {
        $parts = explode('.', $name);
        $current = array_pop($parts);
        return $current;
    }

    /**
     * Find parent name of hierarchical name
     * @param string $name
     * @return string
     */
    public static function findParentName($name)
    {
        $parts = explode('.', $name);
        $current = array_pop($parts);
        if (empty($parts)) {
            return '';
        }
        $parent = implode('.', $parts);
        return $parent;
    }

    /**
     * Summary of hasParentCategory
     * @return bool
     */
    public function hasParentCategory()
    {
        $parentName = static::findParentName($this->getTitle());
        if (empty($parentName)) {
            return false;
        }
        return true;
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
        // no count in tag_browser_* for this parent - try to find it by name
        $this->parent = $this->getParentByName($parentName);
        if (!empty($this->parent)) {
            $this->parent->count = 0;
            $this->parent->setHandler($this->handler);
            return $this->parent;
        }
        // parent is missing - try to create it
        try {
            $this->parent = $this->createMissingParent($parentName);
        } catch (Exception $e) {
            throw new Exception('Unable to create missing parent ' . static::CATEGORY . ' "' . $parentName . '": ' . $e->getMessage());
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
     * Summary of getParentByName
     * @param string $parentName
     * @return Category|null
     */
    public function getParentByName($parentName)
    {
        return static::getInstanceByName($parentName, $this->databaseId);
    }

    /**
     * Get trail of parent entries
     * @return array<Entry>
     */
    public function getParentTrail()
    {
        $trail = [];
        $parentName = static::findParentName($this->getTitle());
        while (!empty($parentName)) {
            $parent = $this->getParentByName($parentName);
            if (empty($parent) || empty($parent->id)) {
                try {
                    $this->parent = $this->createMissingParent($parentName);
                } catch (Exception $e) {
                    throw new Exception('Unable to create missing parent ' . static::CATEGORY . ' "' . $parentName . '": ' . $e->getMessage());
                }
            }
            $parent->setHandler($this->handler);
            $entry = $parent->getEntry();
            $entry->title = static::findCurrentName($entry->title);
            $trail[] = $entry;
            $parentName = static::findParentName($parentName);
        }
        return array_reverse($trail);
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
        $className = static::class;
        $tableName = 'tag_browser_' . static::CATEGORY;
        if (is_array($find)) {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE id IN (" . str_repeat("?,", count($find) - 1) . "?) ORDER BY sort";
            $params = $find;
        } elseif (!str_contains($find, '%')) {
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
            $instance->setHandler($this->handler);
            array_push($instances, $instance);
        }
        return $instances;
    }

    /**
     * Get parent, current and child entries for hierarchical tags or custom columns
     * @param int|bool|null $expand include all child categories at all levels or only direct children
     * @return array<string, mixed>
     */
    public function getHierarchy($expand = false)
    {
        $parents = $this->getParentTrail();
        $current = $this->getEntry();
        $children = $this->getChildEntries($expand);
        // remove current title from children
        foreach ($children as $id => $entry) {
            $childTitle = substr($entry->title, strlen($current->title) + 1);
            $children[$id]->title = $childTitle;
        }
        // remove parent title from current
        $current->title = static::findCurrentName($current->title);
        return [
            "parents" => $parents,
            "current" => $current,
            "children" => $children,
        ];
    }

    /**
     * Create missing parent for hierarchy
     * @param string $name
     * @return Category|null
     */
    public function createMissingParent($name)
    {
        $query = static::SQL_CREATE;
        $params = [ $name ];
        $result = Database::getDb($this->databaseId)->prepare($query);
        $result->execute($params);
        $instance = $this->getParentByName($name);
        if ($instance) {
            $instance->count = 0;
            $instance->setHandler($this->handler);
        } else {
            // create dummy parent for missing hierarchy? doesn't help filter by it afterwards :-(
            //$className = static::class;
            // use id = 0 to support route urls
            //$instance = new $className((object) ['id' => 0, 'name' => $name, 'sort' => $name, 'count' => 0], $this->databaseId);
            //$instance->setHandler($this->handler);
        }
        return $instance;
    }
}
