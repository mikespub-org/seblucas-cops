<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Format;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Database\DatabaseContext;
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
        if ($this->getDbContext()->noDatabaseSelected()) {
            $this->getDatabaseEntries();
        } elseif ($this->request->hasFilter() || !empty($this->config('database_filter'))) {
            $this->getFilteredEntries();
        } else {
            $this->getEntries();
        }
        $this->idPage = PageId::INDEX_ID;
        $this->title = $this->config('title_default');
        $this->subtitle = $this->config('subtitle_default');
    }

    /**
     * Summary of getDatabaseEntries
     * @return void
     */
    public function getDatabaseEntries()
    {
        $i = 0;
        foreach ($this->getDbContext()->getDbNameList() as $key) {
            $dbContext = new DatabaseContext($i, $this->getConfig());
            $booklist = new BookList($this->request, $dbContext);
            $nBooks = $booklist->getBookCount();
            $this->addDatabaseEntry($key, $i, $nBooks);
            $dbContext->clear();
            $i++;
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
        $href = fn() => $this->getLink(["db" => $idx]);
        return new Entry(
            $name,
            "cops:{$idx}:catalog",
            str_format($this->localize("bookword", $count), (string) $count),
            "text",
            [ new LinkNavigation($href) ],
            null,
            "",
            $count
        );
    }

    /**
     * Summary of getFilteredEntries
     * @return void
     */
    public function getFilteredEntries()
    {
        $this->filterParams = $this->request->getFilterParams();
        if (!in_array(PageQueryScope::AUTHOR->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Author::class);
        }
        if (!in_array(PageQueryScope::SERIES->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Serie::class);
        }
        if (!in_array(PageQueryScope::PUBLISHER->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Publisher::class);
        }
        if (!in_array(PageQueryScope::TAG->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Tag::class);
        }
        if (!in_array(PageQueryScope::RATING->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Rating::class, "ratings");
        }
        if (!in_array(PageQueryScope::LANGUAGE->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Language::class);
        }
        if (!in_array(PageQueryScope::FORMAT->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Format::class);
        }
        if (!in_array(PageQueryScope::IDENTIFIER->value, $this->ignoredCategories)) {
            $this->addFilterCountEntry(Identifier::class);
        }
        // @todo apply filter?
        // for multi-database setup, not all databases may have all custom columns - see issue #89
        $customColumnList = CustomColumnType::checkCustomColumnList($this->config('calibre_custom_column'), $this->getDbContext());
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDbContext(), false);
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
                $customColumn->setHandler($this->handler);
                $customColumn->setLocale($this->locale);
                array_push($this->entryArray, $customColumn->getCount());
            }
        }
        if (!empty($this->config('calibre_virtual_libraries')) && !in_array(PageQueryScope::LIBRARIES->value, $this->ignoredCategories)) {
            $library = VirtualLibrary::getCount($this->getDbContext(), $this->handler, $this->locale);
            if (!is_null($library)) {
                array_push($this->entryArray, $library);
            }
        }
        // differentiate between ignored search & index categories BOOK (search) vs. ALLBOOKS/RECENT (home screen)
        if (!in_array(PageQueryScope::ALLBOOKS->value, $this->ignoredCategories)) {
            $booklist = new BookList($this->request, $this->getDbContext());
            $this->addEntries([ $booklist->getAllBooksCountEntry() ]);
        }
        if (!in_array(PageQueryScope::RECENT->value, $this->ignoredCategories) && $this->config('recentbooks_limit') > 0) {
            $booklist = new BookList($this->request, $this->getDbContext());
            $this->addEntries([ $booklist->getRecentCountEntry() ]);
        }

        if ($this->getDbContext()->isMultipleDatabaseEnabled()) {
            $this->title =  $this->getDbContext()->getDbName();
        }
    }

    /**
     * Summary of addFilterCountEntry
     * @param class-string<Base> $className
     * @param ?string $numberOfString
     * @return void
     */
    public function addFilterCountEntry($className, $numberOfString = null)
    {
        $baselist = new BaseList($className, $this->request, $this->getDbContext());
        $count = $baselist->countRequestEntries();
        if ($count > 0) {
            array_push($this->entryArray, $className::getCountEntry($count, $this->databaseId, $numberOfString, $this->handler, $this->filterParams, $this->locale));
        }
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        if (!in_array(PageQueryScope::AUTHOR->value, $this->ignoredCategories)) {
            $this->addCountEntry(Author::class);
        }
        if (!in_array(PageQueryScope::SERIES->value, $this->ignoredCategories)) {
            $this->addCountEntry(Serie::class);
        }
        if (!in_array(PageQueryScope::PUBLISHER->value, $this->ignoredCategories)) {
            $this->addCountEntry(Publisher::class);
        }
        if (!in_array(PageQueryScope::TAG->value, $this->ignoredCategories)) {
            $this->addCountEntry(Tag::class);
        }
        if (!in_array(PageQueryScope::RATING->value, $this->ignoredCategories)) {
            $this->addCountEntry(Rating::class);
        }
        if (!in_array(PageQueryScope::LANGUAGE->value, $this->ignoredCategories)) {
            $this->addCountEntry(Language::class);
        }
        if (!in_array(PageQueryScope::FORMAT->value, $this->ignoredCategories)) {
            $this->addCountEntry(Format::class);
        }
        if (!in_array(PageQueryScope::IDENTIFIER->value, $this->ignoredCategories)) {
            $this->addCountEntry(Identifier::class);
        }
        // for multi-database setup, not all databases may have all custom columns - see issue #89
        $customColumnList = CustomColumnType::checkCustomColumnList($this->config('calibre_custom_column'), $this->getDbContext());
        foreach ($customColumnList as $lookup) {
            $customColumn = CustomColumnType::createByLookup($lookup, $this->getDbContext(), false);
            if (!is_null($customColumn) && $customColumn->isSearchable()) {
                $customColumn->setHandler($this->handler);
                $customColumn->setLocale($this->locale);
                array_push($this->entryArray, $customColumn->getCount());
            }
        }
        if (!empty($this->config('calibre_virtual_libraries')) && !in_array(PageQueryScope::LIBRARIES->value, $this->ignoredCategories)) {
            $this->addCountEntry(VirtualLibrary::class);
        }
        // differentiate between ignored search & index categories BOOK(search) vs. ALLBOOKS/RECENT (home screen)
        if (!in_array(PageQueryScope::ALLBOOKS->value, $this->ignoredCategories)) {
            $booklist = new BookList($this->request, $this->getDbContext());
            $this->addEntries([ $booklist->getAllBooksCountEntry() ]);
        }
        if (!in_array(PageQueryScope::RECENT->value, $this->ignoredCategories) && $this->config('recentbooks_limit') > 0) {
            $booklist = new BookList($this->request, $this->getDbContext());
            $this->addEntries([ $booklist->getRecentCountEntry() ]);
        }

        if ($this->getDbContext()->isMultipleDatabaseEnabled()) {
            $this->title =  $this->getDbContext()->getDbName();
        }
    }

    /**
     * Summary of addCountEntry
     * @param class-string<Base> $className
     * @return void
     */
    public function addCountEntry($className)
    {
        $entry = $className::getCount($this->getDbContext(), $this->handler, $this->locale);
        if (!is_null($entry)) {
            array_push($this->entryArray, $entry);
        }
    }
}
