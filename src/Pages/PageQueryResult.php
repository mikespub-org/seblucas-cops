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
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;

class PageQueryResult extends Page
{
    public const PAGE_ID = PageId::OPENSEARCH_QUERY;
    public const SCOPE_TAG = "tag";
    public const SCOPE_RATING = "rating";
    public const SCOPE_SERIES = "series";
    public const SCOPE_AUTHOR = "author";
    public const SCOPE_BOOK = "book";
    public const SCOPE_PUBLISHER = "publisher";
    public const SCOPE_LANGUAGE = "language";

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
     * @param string $scope
     * @param bool $limit
     * @param ?int $database
     * @return array<mixed>
     */
    protected function searchByScope($scope, $limit = false, $database = null)
    {
        $n = $this->n;
        $numberPerPage = null;
        $queryNormedAndUp = trim((string) $this->query);
        if (Translation::useNormAndUp()) {
            $queryNormedAndUp = Translation::normAndUp($this->query);
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
            case static::SCOPE_BOOK :
                $booklist = new BookList($req, $database, $numberPerPage);
                $array = $booklist->getBooksByFirstLetter('%' . $queryNormedAndUp, $n);
                break;
            case static::SCOPE_AUTHOR :
                $baselist = new BaseList(Author::class, $req, $database, $numberPerPage);
                // we need to repeat the query x 2 here because Author checks both name and sort fields
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n, 2);
                break;
            case static::SCOPE_SERIES :
                $baselist = new BaseList(Serie::class, $req, $database, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case static::SCOPE_TAG :
                $baselist = new BaseList(Tag::class, $req, $database, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case static::SCOPE_PUBLISHER :
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
                $url = Route::link($this->handler, null, ["db" => $d]);
                array_push($this->entryArray, new Entry(
                    $key,
                    "db:query:{$d}",
                    " ",
                    "text",
                    [ new LinkNavigation($url) ],
                    null,
                    "tt-header"
                ));
                Database::getDb($d);
            }
            foreach ([PageQueryResult::SCOPE_BOOK,
                PageQueryResult::SCOPE_AUTHOR,
                PageQueryResult::SCOPE_SERIES,
                PageQueryResult::SCOPE_TAG,
                PageQueryResult::SCOPE_PUBLISHER] as $key) {
                if (in_array($key, $this->getIgnoredCategories())) {
                    continue;
                }
                $array = $this->searchByScope($key, true, $database);

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
                    $params = ['query' => $query, 'db' => $d, 'scope' => $key];
                    if (!empty($libraryId)) {
                        $params['vl'] = $libraryId;
                    }
                    $url = Route::link($this->handler, $pagequery, $params);
                    array_push($this->entryArray, new Entry(
                        str_format(localize("search.result.{$key}"), $this->query),
                        "db:query:{$d}:{$key}",
                        str_format(localize("{$key}word", $total), $total),
                        "text",
                        [ new LinkNavigation($url) ],
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
        $this->idPage = static::PAGE_ID;
        $scope = $this->request->get("scope");
        if (empty($scope)) {
            $this->title = str_format(localize("search.result"), $this->query);
        } else {
            // Comment to help the perl i18n script
            // str_format (localize ("search.result.author"), $this->query)
            // str_format (localize ("search.result.tag"), $this->query)
            // str_format (localize ("search.result.series"), $this->query)
            // str_format (localize ("search.result.book"), $this->query)
            // str_format (localize ("search.result.publisher"), $this->query)
            $this->title = str_format(localize("search.result.{$scope}"), $this->query);
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

        $scope = $this->request->get("scope");
        if (empty($scope)) {
            $this->doSearchByCategory($database);
            return;
        }

        $array = $this->searchByScope($scope, false, $database);
        if (count($array) == 2 && is_array($array [0])) {
            [$this->entryArray, $this->totalNumber] = $array;
        } else {
            $this->entryArray = $array;
        }
        if (empty($this->entryArray) && !$this->request->isFeed()) {
            array_push($this->entryArray, $this->getNoResultEntry($database, $scope));
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
        $url = Route::link($this->handler, $this->idPage, ['query' => $query, 'db' => $idx]);
        return new Entry(
            $name,
            "db:query:{$idx}",
            str_format(localize("bookword", $count), $count),
            "text",
            [ new LinkNavigation($url) ],
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
        $url = Route::link($this->handler, PageId::OPENSEARCH, $params);
        return new Entry(
            str_format(localize("search.result.none"), $this->query),
            "db:query:{$database}:{$scope}",
            " ",
            "text",
            [ new LinkNavigation($url) ],
            null,
            "tt-header"
        );
    }
}
