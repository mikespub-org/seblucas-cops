<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Language\Normalizer;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;

class PageQueryResult extends Page
{
    public const ROUTE_SEARCH = "page-search";
    public const ROUTE_QUERY = "page-query";
    public const ROUTE_SCOPE = "page-query-scope";
    // specified in util.js as page=query&search=1&...
    //public const ROUTE_TYPEAHEAD = "page-typeahead";

    /** @var ?string */
    public $query;

    public function setRequest($request)
    {
        parent::setRequest($request);
        $this->query = $this->request->get('query');
    }

    /**
     * Summary of useTypeahead
     * @return bool
     */
    protected function useTypeahead()
    {
        return !is_null($this->request->get("search"));
    }

    /**
     * Summary of searchByScope
     * @param PageQueryScope $scope
     * @param bool $limit
     * @param ?int $database
     * @return array<mixed>
     */
    protected function searchByScope($scope, $limit = false, $database = null)
    {
        $n = $this->n;
        $numberPerPage = null;
        $queryNormedAndUp = trim((string) $this->query);
        if (Normalizer::useNormAndUp()) {
            $queryNormedAndUp = Normalizer::normAndUp($this->query);
        }
        if ($limit) {
            $n = 1;
            $numberPerPage = 5;
        }
        $libraryId = "";
        if (!Database::noDatabaseSelected($database)) {
            $libraryId = $this->request->getVirtualLibrary();
        }
        if (!empty($libraryId)) {
            $req = Request::build(['vl' => $libraryId], $this->handler);
        } else {
            $req = Request::build([], $this->handler);
        }
        switch ($scope) {
            case PageQueryScope::BOOK:
                $booklist = new BookList($req, $database, $numberPerPage);
                $array = $booklist->getBooksByFirstLetter('%' . $queryNormedAndUp, $n);
                break;
            case PageQueryScope::AUTHOR:
                $baselist = new BaseList(Author::class, $req, $database, $numberPerPage);
                // we need to repeat the query x 2 here because Author checks both name and sort fields
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n, 2);
                break;
            case PageQueryScope::SERIES:
                $baselist = new BaseList(Serie::class, $req, $database, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case PageQueryScope::TAG:
                $baselist = new BaseList(Tag::class, $req, $database, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case PageQueryScope::PUBLISHER:
                $baselist = new BaseList(Publisher::class, $req, $database, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            default:
                $booklist = new BookList($req, $database, $numberPerPage);
                $array = $booklist->getBooksByQueryScope(
                    ["all" => "%" . $queryNormedAndUp . "%"],
                    $n
                );
        }

        return $array;
    }

    /**
     * Summary of doSearchByCategory
     * @param ?int $database
     * @return void
     */
    public function doSearchByCategory($database = null)
    {
        $pagequery = $this->idPage;
        $dbArray = [""];
        $d = $database;
        $query = $this->query;
        $libraryId = "";
        // Special case when no databases were chosen, we search on all databases
        if (Database::noDatabaseSelected($database)) {
            $dbArray = Database::getDbNameList();
            $d = 0;
        } else {
            $libraryId = $this->request->getVirtualLibrary();
        }
        foreach ($dbArray as $key) {
            if (Database::noDatabaseSelected($database)) {
                $href = fn() => $this->getLink(["db" => $d]);
                array_push($this->entryArray, new Entry(
                    $key,
                    "db:query:{$d}",
                    " ",
                    "text",
                    [ new LinkNavigation($href) ],
                    null,
                    "tt-header"
                ));
                Database::getDb($d);
            }
            foreach ([PageQueryScope::BOOK,
                PageQueryScope::AUTHOR,
                PageQueryScope::SERIES,
                PageQueryScope::TAG,
                PageQueryScope::PUBLISHER] as $scope) {
                $value = $scope->value;
                if (in_array($value, $this->getIgnoredCategories())) {
                    continue;
                }
                $array = $this->searchByScope($scope, true, $database);

                $i = 0;
                if (count($array) == 2 && is_array($array [0])) {
                    $total = $array [1];
                    $array = $array [0];
                    // show the number of entries here, not the number of books found
                    //$total = count($array);
                } else {
                    $total = count($array);
                }
                if ($total > 0) {
                    // Comment to help the perl i18n script
                    // str_format (localize("bookword", count($array))
                    // str_format (localize("authorword", count($array))
                    // str_format (localize("seriesword", count($array))
                    // str_format (localize("tagword", count($array))
                    // str_format (localize("publisherword", count($array))
                    $params = ['query' => $query, 'db' => $d, 'scope' => $value];
                    if (!empty($libraryId)) {
                        $params['vl'] = $libraryId;
                    }
                    $href = fn() => $this->getRoute(self::ROUTE_SCOPE, $params);
                    array_push($this->entryArray, new Entry(
                        str_format(localize("search.result.{$value}"), $this->query),
                        "db:query:{$d}:{$value}",
                        str_format(localize("{$value}word", $total), $total),
                        "text",
                        [ new LinkNavigation($href) ],
                        $database,
                        Database::noDatabaseSelected($database) ? "" : "tt-header",
                        $total
                    ));
                }
                if (!Database::noDatabaseSelected($database) && $this->useTypeahead()) {
                    foreach ($array as $entry) {
                        array_push($this->entryArray, $entry);
                        $i++;
                        if ($i > 4) {
                            break;
                        };
                    }
                }
            }
            $d++;
            if (Database::noDatabaseSelected($database)) {
                Database::clearDb();
            }
        }
        if (empty($this->entryArray) && !$this->request->isFeed()) {
            array_push($this->entryArray, $this->getNoResultEntry($database));
        }
    }

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->idPage = PageId::SEARCH_ID;
        $value = $this->request->get("scope");
        if (empty($value)) {
            $this->title = str_format(localize("search.result"), $this->query);
        } else {
            $scope = PageQueryScope::from($value);
            $this->title = str_format($scope->result(), $this->query);
        }
        $this->getEntries();
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $database = $this->getDatabaseId();
        // Special case when we are doing a search and no database is selected
        if (Database::noDatabaseSelected($database) && !$this->useTypeahead()) {
            $this->getDatabaseEntries();
            return;
        }

        $value = $this->request->get("scope");
        if (empty($value)) {
            $this->doSearchByCategory($database);
            return;
        }
        $scope = PageQueryScope::from($value);

        $array = $this->searchByScope($scope, false, $database);
        if (count($array) == 2 && is_array($array [0])) {
            [$this->entryArray, $this->totalNumber] = $array;
        } else {
            $this->entryArray = $array;
        }
        if (empty($this->entryArray) && !$this->request->isFeed()) {
            array_push($this->entryArray, $this->getNoResultEntry($database, $value));
        }
    }

    /**
     * Summary of getDatabaseEntries
     * @return void
     */
    public function getDatabaseEntries()
    {
        $ignoredCategories = $this->getIgnoredCategories();
        $query = $this->query;
        $crit = "%" . $this->query . "%";
        $d = 0;
        foreach (Database::getDbNameList() as $key) {
            Database::clearDb();
            $booklist = new BookList($this->request, $d, 1);
            [$array, $totalNumber] = $booklist->getBooksByQueryScope(["all" => $crit], 1, $ignoredCategories);
            $this->addDatabaseEntry($key, $d, $totalNumber, $query);
            $d++;
        }
    }

    /**
     * Summary of addDatabaseEntry
     * @param string $name
     * @param int $idx
     * @param int $count
     * @param string $query
     * @return void
     */
    public function addDatabaseEntry($name, $idx, $count, $query)
    {
        array_push($this->entryArray, $this->getDatabaseEntry($name, $idx, $count, $query));
    }

    /**
     * Summary of getDatabaseEntry
     * @param string $name
     * @param int $idx
     * @param int $count
     * @param string $query
     * @return Entry
     */
    public function getDatabaseEntry($name, $idx, $count, $query)
    {
        $href = fn() => $this->getRoute(self::ROUTE_QUERY, ['query' => $query, 'db' => $idx]);
        return new Entry(
            $name,
            "db:query:{$idx}",
            str_format(localize("bookword", $count), $count),
            "text",
            [ new LinkNavigation($href) ],
            null,
            "",
            $count
        );
    }

    /**
     * Summary of getNoResultEntry
     * @param ?int $database
     * @param ?string $scope
     * @return Entry
     */
    public function getNoResultEntry($database = null, $scope = null)
    {
        $params = ['db' => $database, 'scope' => $scope];
        $href = fn() => $this->getRoute(self::ROUTE_SEARCH, $params);
        return new Entry(
            str_format(localize("search.result.none"), $this->query),
            "db:query:{$database}:{$scope}",
            " ",
            "text",
            [ new LinkNavigation($href) ],
            null,
            "tt-header"
        );
    }
}
