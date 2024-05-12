<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
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
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
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
            array_push($this->entryArray, new Entry(
                $key,
                "cops:{$i}:catalog",
                str_format(localize("bookword", $nBooks), $nBooks),
                "text",
                [ new LinkNavigation(Route::page(null, ["db" => $i])) ],
                null,
                "",
                $nBooks
            ));
            $i++;
            Database::clearDb();
        }
    }

    /**
     * Summary of getFilterCountEntries
     * @return void
     */
    public function getFilterCountEntries()
    {
        $filterParams = $this->request->getFilterParams();
        if (!in_array(PageQueryResult::SCOPE_AUTHOR, $this->ignoredCategories)) {
            $baselist = new BaseList(Author::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Author::getCountEntry($count, $this->databaseId, null, $this->handler, $filterParams));
            }
        }
        if (!in_array(PageQueryResult::SCOPE_SERIES, $this->ignoredCategories)) {
            $baselist = new BaseList(Serie::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Serie::getCountEntry($count, $this->databaseId, null, $this->handler, $filterParams));
            }
        }
        if (!in_array(PageQueryResult::SCOPE_PUBLISHER, $this->ignoredCategories)) {
            $baselist = new BaseList(Publisher::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Publisher::getCountEntry($count, $this->databaseId, null, $this->handler, $filterParams));
            }
        }
        if (!in_array(PageQueryResult::SCOPE_TAG, $this->ignoredCategories)) {
            $baselist = new BaseList(Tag::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Tag::getCountEntry($count, $this->databaseId, null, $this->handler, $filterParams));
            }
        }
        if (!in_array(PageQueryResult::SCOPE_RATING, $this->ignoredCategories)) {
            $baselist = new BaseList(Rating::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Rating::getCountEntry($count, $this->databaseId, "ratings", $this->handler, $filterParams));
            }
        }
        if (!in_array(PageQueryResult::SCOPE_LANGUAGE, $this->ignoredCategories)) {
            $baselist = new BaseList(Language::class, $this->request, $this->databaseId);
            $count = $baselist->countRequestEntries();
            if ($count > 0) {
                array_push($this->entryArray, Language::getCountEntry($count, $this->databaseId, null, $this->handler, $filterParams));
            }
        }
        // @todo apply filter?
        $customColumnList = CustomColumnType::checkCustomColumnList(Config::get('calibre_custom_column'));
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDatabaseId());
            $customColumn->setHandler($this->handler);
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
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
        $this->entryArray = array_merge($this->entryArray, $booklist->getCount());

        if (Database::isMultipleDatabaseEnabled()) {
            $this->title =  Database::getDbName($this->getDatabaseId());
        }
    }

    /**
     * Summary of getTopCountEntries
     * @return void
     */
    public function getTopCountEntries()
    {
        if (!in_array(PageQueryResult::SCOPE_AUTHOR, $this->ignoredCategories)) {
            $author = Author::getCount($this->databaseId, $this->handler);
            if (!is_null($author)) {
                array_push($this->entryArray, $author);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_SERIES, $this->ignoredCategories)) {
            $series = Serie::getCount($this->databaseId, $this->handler);
            if (!is_null($series)) {
                array_push($this->entryArray, $series);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_PUBLISHER, $this->ignoredCategories)) {
            $publisher = Publisher::getCount($this->databaseId, $this->handler);
            if (!is_null($publisher)) {
                array_push($this->entryArray, $publisher);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_TAG, $this->ignoredCategories)) {
            $tags = Tag::getCount($this->databaseId, $this->handler);
            if (!is_null($tags)) {
                array_push($this->entryArray, $tags);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_RATING, $this->ignoredCategories)) {
            $rating = Rating::getCount($this->databaseId, $this->handler);
            if (!is_null($rating)) {
                array_push($this->entryArray, $rating);
            }
        }
        if (!in_array(PageQueryResult::SCOPE_LANGUAGE, $this->ignoredCategories)) {
            $languages = Language::getCount($this->databaseId, $this->handler);
            if (!is_null($languages)) {
                array_push($this->entryArray, $languages);
            }
        }
        $customColumnList = CustomColumnType::checkCustomColumnList(Config::get('calibre_custom_column'));
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDatabaseId());
            $customColumn->setHandler($this->handler);
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
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
        $this->entryArray = array_merge($this->entryArray, $booklist->getCount());

        if (Database::isMultipleDatabaseEnabled()) {
            $this->title =  Database::getDbName($this->getDatabaseId());
        }
    }
}
