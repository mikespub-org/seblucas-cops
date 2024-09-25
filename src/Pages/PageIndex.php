<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;

class PageIndex extends Page
{
    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = static::PAGE_ID;
        $this->title = Config::get('title_default');
        $this->subtitle = Config::get('subtitle_default');
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        if (Database::noDatabaseSelected($this->databaseId)) {
            $this->getDatabaseEntries();
        } elseif ($this->request->hasFilter()) {
            $this->getFilterCountEntries();
        } else {
            $this->getTopCountEntries();
        }
        $this->getExtra();
    }

    /**
     * Summary of getDatabaseEntries
     * @return void
     */
    public function getDatabaseEntries()
    {
        $i = 0;
        foreach (Database::getDbNameList() as $key) {
            $booklist = new BookList($this->request, $i);
            $nBooks = $booklist->getBookCount();
            $this->addDatabaseEntry($key, $i, $nBooks);
            $i++;
            Database::clearDb();
        }
    }

    /**
     * Summary of addDatabaseEntry
     * @param string $name
     * @param int $idx
     * @param int $count
     * @return void
     */
    public function addDatabaseEntry($name, $idx, $count)
    {
        array_push($this->entryArray, $this->getDatabaseEntry($name, $idx, $count));
    }

    /**
     * Summary of getDatabaseEntry
     * @param string $name
     * @param int $idx
     * @param int $count
     * @return Entry
     */
    public function getDatabaseEntry($name, $idx, $count)
    {
        $url = Route::link($this->handler, null, ["db" => $idx]);
        return new Entry(
            $name,
            "cops:{$idx}:catalog",
            str_format(localize("bookword", $count), $count),
            "text",
            [ new LinkNavigation($url) ],
            null,
            "",
            $count
        );
    }

    /**
     * Summary of getFilterCountEntries
     * @return void
     */
    public function getFilterCountEntries()
    {
        $this->filterParams = $this->request->getFilterParams();
        if (!in_array(PageQueryResult::SCOPE_AUTHOR, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Author::class);
        }
        if (!in_array(PageQueryResult::SCOPE_SERIES, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Serie::class);
        }
        if (!in_array(PageQueryResult::SCOPE_PUBLISHER, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Publisher::class);
        }
        if (!in_array(PageQueryResult::SCOPE_TAG, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Tag::class);
        }
        if (!in_array(PageQueryResult::SCOPE_RATING, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Rating::class, "ratings");
        }
        if (!in_array(PageQueryResult::SCOPE_LANGUAGE, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Language::class);
        }
        // @todo apply filter?
        // for multi-database setup, not all databases may have all custom columns - see issue #89
        $customColumnList = CustomColumnType::checkCustomColumnList(Config::get('calibre_custom_column'), $this->getDatabaseId());
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDatabaseId());
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
                $customColumn->setHandler($this->handler);
                array_push($this->entryArray, $customColumn->getCount());
            }
        }
        if (!empty(Config::get('calibre_virtual_libraries')) && !in_array('libraries', $this->ignoredCategories)) {
            $library = VirtualLibrary::getCount($this->databaseId, $this->handler);
            if (!is_null($library)) {
                array_push($this->entryArray, $library);
            }
        }
        $booklist = new BookList($this->request);
        $this->addEntries($booklist->getCount());

        if (Database::isMultipleDatabaseEnabled()) {
            $this->title =  Database::getDbName($this->getDatabaseId());
        }
    }

    /**
     * Summary of addFilterCountEntry
     * @param string $className
     * @param ?string $numberOfString
     * @return void
     */
    public function addFilterCountEntry($className, $numberOfString = null)
    {
        $baselist = new BaseList($className, $this->request, $this->databaseId);
        $count = $baselist->countRequestEntries();
        if ($count > 0) {
            array_push($this->entryArray, $className::getCountEntry($count, $this->databaseId, $numberOfString, $this->handler, $this->filterParams));
        }
    }

    /**
     * Summary of getTopCountEntries
     * @return void
     */
    public function getTopCountEntries()
    {
        if (!in_array(PageQueryResult::SCOPE_AUTHOR, $this->ignoredCategories)) {
            $this->addTopCountEntry(Author::class);
        }
        if (!in_array(PageQueryResult::SCOPE_SERIES, $this->ignoredCategories)) {
            $this->addTopCountEntry(Serie::class);
        }
        if (!in_array(PageQueryResult::SCOPE_PUBLISHER, $this->ignoredCategories)) {
            $this->addTopCountEntry(Publisher::class);
        }
        if (!in_array(PageQueryResult::SCOPE_TAG, $this->ignoredCategories)) {
            $this->addTopCountEntry(className: Tag::class);
        }
        if (!in_array(PageQueryResult::SCOPE_RATING, $this->ignoredCategories)) {
            $this->addTopCountEntry(className: Rating::class);
        }
        if (!in_array(PageQueryResult::SCOPE_LANGUAGE, $this->ignoredCategories)) {
            $this->addTopCountEntry(Language::class);
        }
        // for multi-database setup, not all databases may have all custom columns - see issue #89
        $customColumnList = CustomColumnType::checkCustomColumnList(Config::get('calibre_custom_column'), $this->getDatabaseId());
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDatabaseId());
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
                $customColumn->setHandler($this->handler);
                array_push($this->entryArray, $customColumn->getCount());
            }
        }
        if (!empty(Config::get('calibre_virtual_libraries')) && !in_array('libraries', $this->ignoredCategories)) {
            $this->addTopCountEntry(VirtualLibrary::class);
        }
        $booklist = new BookList($this->request);
        $this->addEntries($booklist->getCount());

        if (Database::isMultipleDatabaseEnabled()) {
            $this->title =  Database::getDbName($this->getDatabaseId());
        }
    }

    /**
     * Summary of addTopCountEntry
     * @param string $className
     * @return void
     */
    public function addTopCountEntry($className)
    {
        $entry = $className::getCount($this->databaseId, $this->handler);
        if (!is_null($entry)) {
            array_push($this->entryArray, $entry);
        }
    }
}
