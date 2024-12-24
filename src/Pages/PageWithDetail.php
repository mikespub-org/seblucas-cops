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
use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\Format;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\VirtualLibrary;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class PageWithDetail extends Page
{
    /**
     * Summary of __construct
     * @param ?Request $request
     * @param ?Base $instance @todo investigate potential use as alternative to getEntry()
     */
    public function __construct($request = null, $instance = null)
    {
        $this->setConfig();
        $this->setRequest($request);

        // move to constructor as this is always called directly after PageId::getPage()
        if (empty($instance)) {
            $this->initializeContent();
        } else {
            // do not call getEntries() here
            $this->setInstance($instance);
        }
    }

    /**
     * Summary of setInstance
     * @param ?Base $instance
     * @return void
     */
    public function setInstance($instance)
    {
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->getTitle();
        // this is the unfiltered uri here, used in JsonRenderer - @todo do we want to use request->urlParams?
        $this->currentUri = $instance->getUri();
        $this->parentTitle = $instance->getParentTitle();
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = $instance->getParentUri($filterParams);
    }

    /**
     * Summary of getFilters
     * @param Author|Language|Publisher|Rating|Serie|Tag|Identifier|Format|CustomColumn $instance
     * @return void
     */
    public function getFilters($instance)
    {
        if ($this->request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
            $instance->setFilterLimit(Config::get('opds_filter_limit'));
        } else {
            $filterLinks = Config::get('html_filter_links');
            $instance->setFilterLimit(Config::get('html_filter_limit'));
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
            $instance->setFilterParams([VirtualLibrary::URL_PARAM => $libraryId]);
        }
        // @todo get rid of extraParams in JsonRenderer and OpdsRenderer as filters should be included in navlink now
        $params = $instance->getExtraParams();
        $params['db'] = $this->getDatabaseId();
        $filtersTitle = localize("filters.title");
        if (!($instance instanceof Author) && in_array('author', $filterLinks)) {
            $title = localize("authors.title");
            $href = fn() => $this->getRoute(Author::ROUTE_ALL, $params);
            $relation = "authors";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Author::URL_PARAM] ??= 1;
            $this->addEntries($instance->getAuthors($paging[Author::URL_PARAM]));
        }
        if (!($instance instanceof Language) && in_array('language', $filterLinks)) {
            $title = localize("languages.title");
            $href = fn() => $this->getRoute(Language::ROUTE_ALL, $params);
            $relation = "languages";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Language::URL_PARAM] ??= 1;
            $this->addEntries($instance->getLanguages($paging[Language::URL_PARAM]));
        }
        if (!($instance instanceof Publisher) && in_array('publisher', $filterLinks)) {
            $title = localize("publishers.title");
            $href = fn() => $this->getRoute(Publisher::ROUTE_ALL, $params);
            $relation = "publishers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Publisher::URL_PARAM] ??= 1;
            $this->addEntries($instance->getPublishers($paging[Publisher::URL_PARAM]));
        }
        if (!($instance instanceof Rating) && in_array('rating', $filterLinks)) {
            $title = localize("ratings.title");
            $href = fn() => $this->getRoute(Rating::ROUTE_ALL, $params);
            $relation = "ratings";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Rating::URL_PARAM] ??= 1;
            $this->addEntries($instance->getRatings($paging[Rating::URL_PARAM]));
        }
        if (!($instance instanceof Serie) && in_array('series', $filterLinks)) {
            $title = localize("series.title");
            $href = fn() => $this->getRoute(Serie::ROUTE_ALL, $params);
            $relation = "series";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Serie::URL_PARAM] ??= 1;
            $this->addEntries($instance->getSeries($paging[Serie::URL_PARAM]));
        }
        if (in_array('tag', $filterLinks)) {
            $title = localize("tags.title");
            $href = fn() => $this->getRoute(Tag::ROUTE_ALL, $params);
            $relation = "tags";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Tag::URL_PARAM] ??= 1;
            // special case if we want to find other tags applied to books where this tag applies
            if ($instance instanceof Tag) {
                $instance->limitSelf = false;
            }
            $this->addEntries($instance->getTags($paging[Tag::URL_PARAM]));
        }
        if (in_array('identifier', $filterLinks)) {
            $title = localize("identifiers.title");
            $href = fn() => $this->getRoute(Identifier::ROUTE_ALL, $params);
            $relation = "identifiers";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Identifier::URL_PARAM] ??= 1;
            // special case if we want to find other identifiers applied to books where this identifier applies
            if ($instance instanceof Identifier) {
                $instance->limitSelf = false;
            }
            $this->addEntries($instance->getIdentifiers($paging[Identifier::URL_PARAM]));
        }
        if (in_array('format', $filterLinks)) {
            $title = localize("formats.title");
            $href = fn() => $this->getRoute(Format::ROUTE_ALL, $params);
            $relation = "formats";
            $this->addHeaderEntry($title, $filtersTitle, $href, $relation);
            $paging[Format::URL_PARAM] ??= 1;
            // special case if we want to find other formats applied to books where this format applies
            if ($instance instanceof Format) {
                $instance->limitSelf = false;
            }
            $this->addEntries($instance->getFormats($paging[Format::URL_PARAM]));
        }
        /**
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        if (!($instance instanceof CustomColumn) && in_array('custom', $filterLinks)) {
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
     * Summary of canFilter
     * @return bool
     */
    public function canFilter()
    {
        if ($this->request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
        } else {
            $filterLinks = Config::get('html_filter_links');
        }
        if (!empty($filterLinks)) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getExtra
     * @param Base $instance
     * @return void
     */
    public function getExtra($instance = null)
    {
        if (!is_null($instance) && !empty($instance->id)) {
            $content = null;
            $note = $instance->getNote();
            if (!empty($note) && !empty($note->doc)) {
                $content = Resource::fixResourceLinks($note->doc, $instance->getDatabaseId());
            }
            if (!empty($instance->link) || !empty($content)) {
                $this->extra = [
                    "title" => localize("extra.title"),
                    "link" => $instance->link,
                    "content" => $content,
                ];
            }
        }
    }
}
