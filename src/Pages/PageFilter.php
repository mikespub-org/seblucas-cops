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
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Calibre\Format;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;

class PageFilter extends Page
{
    protected $className = Filter::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->request->set('filter', '1');
        $this->filterParams = $this->request->getFilterParams();
        $this->getEntries();
        $this->idPage = Filter::PAGE_ID;
        $this->title = localize("filters.title");
    }

    /**
     * Summary of getEntries - @todo get filter links
     * @return void
     */
    public function getEntries()
    {
        if ($this->request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
            $limit = Config::get('opds_filter_limit');
        } else {
            $filterLinks = Config::get('html_filter_links');
            $limit = Config::get('html_filter_limit');
        }
        $this->entryArray = [];
        if (empty($filterLinks)) {
            return;
        }
        // we use g[a]=2 to indicate we want to paginate in facetgroup Authors
        $paging = $this->request->get('g');
        if (!is_array($paging)) {
            $paging = [];
        }
        // if we want to filter by virtual library etc.
        $libraryId = $this->request->getVirtualLibrary();
        if (!empty($libraryId)) {
            $this->filterParams = [VirtualLibrary::URL_PARAM => $libraryId];
        }
        // @todo get rid of extraParams in JsonRenderer and OpdsRenderer as filters should be included in navlink now
        $params = $this->filterParams;
        $params['db'] = $this->getDatabaseId();
        $req = Request::build($params, $this->handler);
        $filtersTitle = localize("filters.title");
        if (in_array('author', $filterLinks)) {
            $title = localize(phrase: "authors.title");
            $href = fn() => $this->getRoute(Author::ROUTE_ALL, $params);
            $relation = "authors";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Author::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Author::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Author::URL_PARAM]));
        }
        if (in_array('language', $filterLinks)) {
            $title = localize("languages.title");
            $href = fn() => $this->getRoute(Language::ROUTE_ALL, $params);
            $relation = "languages";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Language::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Language::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Language::URL_PARAM]));
        }
        if (in_array('publisher', $filterLinks)) {
            $title = localize("publishers.title");
            $href = fn() => $this->getRoute(Publisher::ROUTE_ALL, $params);
            $relation = "publishers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Publisher::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Publisher::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Publisher::URL_PARAM]));
        }
        if (in_array('rating', $filterLinks)) {
            $title = localize("ratings.title");
            $href = fn() => $this->getRoute(Rating::ROUTE_ALL, $params);
            $relation = "ratings";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Rating::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Rating::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Rating::URL_PARAM]));
        }
        if (in_array('series', $filterLinks)) {
            $title = localize("series.title");
            $href = fn() => $this->getRoute(Serie::ROUTE_ALL, $params);
            $relation = "series";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Serie::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Serie::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Serie::URL_PARAM]));
        }
        if (in_array('tag', $filterLinks)) {
            $title = localize("tags.title");
            $href = fn() => $this->getRoute(Tag::ROUTE_ALL, $params);
            $relation = "tags";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Tag::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Tag::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Tag::URL_PARAM]));
        }
        if (in_array('identifier', $filterLinks)) {
            $title = localize("identifiers.title");
            $href = fn() => $this->getRoute(Identifier::ROUTE_ALL, $params);
            $relation = "identifiers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Identifier::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Identifier::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Identifier::URL_PARAM]));
        }
        if (in_array('format', $filterLinks)) {
            $title = localize("formats.title");
            $href = fn() => $this->getRoute(Format::ROUTE_ALL, $params);
            $relation = "formats";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $baselist = new BaseList(Format::class, $req, $this->databaseId, $limit);
            $baselist->pagination = true;
            $paging[Format::URL_PARAM] ??= 1;
            $this->addEntries($baselist->getRequestEntries($paging[Format::URL_PARAM]));
        }
        /**
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        if (in_array('custom', $filterLinks)) {
            $columns = CustomColumnType::getAllCustomColumns($this->getDatabaseId());
            $paging['c'] ??= [];
            foreach ($columns as $label => $column) {
                $customType = CustomColumnType::createByCustomID($column["id"], $this->getDatabaseId());
                $title = $customType->getTitle();
                $href = fn() => $customType->getParentUri();
                $relation = $customType->getTitle();
                $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
                $paging['c'][$column['id']] ??= 1;
                $entries = $instance->getCustomValues($customType);
                // @todo
            }
        }
         */
    }

    /**
     * Summary of addEntries
     * @param array<Entry> $entries
     * @return void
     */
    public function addEntries($entries)
    {
        foreach ($entries as $idx => $entry) {
            // @todo replace instance link with filter link
            // ...
        }
        $this->entryArray = array_merge($this->entryArray, $entries);
    }
}
