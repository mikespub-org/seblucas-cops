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
use SebLucas\Cops\Calibre\Comment;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Database\DatabaseContext;
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
     * @param ?DatabaseContext $dbContext
     * @return array<mixed>
     */
    protected function searchByScope($scope, $limit = false, $dbContext = null)
    {
        $n = $this->n;
        $numberPerPage = null;
        $queryNormedAndUp = trim((string) $this->query);
        if (Normalizer::useNormAndUp($this->getConfig())) {
            $queryNormedAndUp = Normalizer::normAndUp($this->query);
        }
        if ($limit) {
            $n = 1;
            $numberPerPage = 5;
        }
        $dbContext ??= $this->getDbContext();
        $libraryId = "";
        if (!$dbContext->noDatabaseSelected()) {
            $libraryId = $this->request->getVirtualLibrary();
        }
        if (!empty($libraryId)) {
            $req = Request::build(['vl' => $libraryId], $this->handler);
        } else {
            $req = Request::build([], $this->handler);
        }
        $req->locale = $this->locale;
        if ($this->getConfig() !== null) {
            $req->setConfig($this->getConfig());
        }
        switch ($scope) {
            case PageQueryScope::BOOK:
                $booklist = new BookList($req, $dbContext, $numberPerPage);
                $array = $booklist->getBooksByFirstLetter('%' . $queryNormedAndUp, $n);
                break;
            case PageQueryScope::AUTHOR:
                $baselist = new BaseList(Author::class, $req, $dbContext, $numberPerPage);
                // we need to repeat the query x 2 here because Author checks both name and sort fields
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n, 2);
                break;
            case PageQueryScope::SERIES:
                $baselist = new BaseList(Serie::class, $req, $dbContext, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case PageQueryScope::TAG:
                $baselist = new BaseList(Tag::class, $req, $dbContext, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case PageQueryScope::PUBLISHER:
                $baselist = new BaseList(Publisher::class, $req, $dbContext, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                break;
            case PageQueryScope::COMMENT:
                $baselist = new BaseList(Comment::class, $req, $dbContext, $numberPerPage);
                $array = $baselist->getAllEntriesByQuery($queryNormedAndUp, $n);
                if (!$limit) {
                    // re-map comments to books
                    $idlist = array_map(fn($entry) => (int) $entry->instance->name, $array);
                    $booklist = new BookList($req, $dbContext, $numberPerPage);
                    $array = $booklist->getBooksByIdList($idlist);
                } elseif ($this->useTypeahead()) {
                    // update title based on books
                    $idlist = array_map(fn($entry) => (int) $entry->instance->name, $array);
                    $booklist = new BookList($req, $dbContext, $numberPerPage);
                    [$bookArray, ] = $booklist->getBooksByIdList($idlist);
                    $titles = [];
                    foreach ($bookArray as $entryBook) {
                        $titles[$entryBook->book->id] = $entryBook->title;
                    }
                    foreach ($array as $id => $entry) {
                        if (!empty($titles[$entry->instance->name])) {
                            $array[$id]->title = $titles[$entry->instance->name];
                        }
                    }
                }
                break;
            case PageQueryScope::NOTE:
                $array = [];
                break;
            case PageQueryScope::ANNOTATION:
                $array = [];
                break;
            default:
                $booklist = new BookList($req, $dbContext, $numberPerPage);
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
        $dbArray = [""];
        $dbNum = $database;
        $query = $this->query;
        $libraryId = "";
        // Special case when no databases were chosen, we search on all databases
        $noDatabaseSelected = $this->getDbContext()->noDatabaseSelected();
        if ($noDatabaseSelected) {
            $dbArray = $this->getDbContext()->getDbNameList();
            $dbContext = clone $this->getDbContext();
            $dbNum = 0;
        } else {
            $dbContext = $this->getDbContext();
            $libraryId = $this->request->getVirtualLibrary();
        }
        $scopeList = [
            PageQueryScope::BOOK,
            PageQueryScope::AUTHOR,
            PageQueryScope::SERIES,
            PageQueryScope::TAG,
            PageQueryScope::PUBLISHER,
        ];
        if (!empty($this->config('search_comments', 0))) {
            $scopeList[] = PageQueryScope::COMMENT;
        }
        if (!empty($this->config('search_notes', 0))) {
            $scopeList[] = PageQueryScope::NOTE;
        }
        if (!empty($this->config('search_annotations', 0))) {
            $scopeList[] = PageQueryScope::ANNOTATION;
        }
        foreach ($dbArray as $key) {
            if ($noDatabaseSelected) {
                $href = fn() => $this->getLink(["db" => $dbNum]);
                array_push($this->entryArray, new Entry(
                    $key,
                    "db:query:{$dbNum}",
                    " ",
                    "text",
                    [ new LinkNavigation($href) ],
                    null,
                    "tt-header"
                ));
                $dbContext->setDatabase($dbNum);
            }
            foreach ($scopeList as $scope) {
                $value = $scope->value;
                if (in_array($value, $this->getIgnoredCategories())) {
                    continue;
                }
                $array = $this->searchByScope($scope, true, $dbContext);

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
                    // str_format ($this->localize("bookword", count($array))
                    // str_format ($this->localize("authorword", count($array))
                    // str_format ($this->localize("seriesword", count($array))
                    // str_format ($this->localize("tagword", count($array))
                    // str_format ($this->localize("publisherword", count($array))
                    $params = ['query' => $query, 'db' => $dbNum, 'scope' => $value];
                    if (!empty($libraryId)) {
                        $params['vl'] = $libraryId;
                    }
                    $href = fn() => $this->getRoute(self::ROUTE_SCOPE, $params);
                    array_push($this->entryArray, new Entry(
                        str_format($this->localize("search.result.{$value}"), $this->query),
                        "db:query:{$dbNum}:{$value}",
                        str_format($this->localize("{$value}word", $total), $total),
                        "text",
                        [ new LinkNavigation($href) ],
                        $database,
                        $noDatabaseSelected ? "" : "tt-header",
                        $total
                    ));
                }
                if (!$noDatabaseSelected && $this->useTypeahead()) {
                    foreach ($array as $entry) {
                        array_push($this->entryArray, $entry);
                        $i++;
                        if ($i > 4) {
                            break;
                        };
                    }
                }
            }
            if ($noDatabaseSelected) {
                $dbContext->clear();
            }
            $dbNum++;
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
            $this->title = str_format($this->localize("search.result"), $this->query);
        } else {
            $scope = PageQueryScope::from($value);
            $this->title = str_format($this->localize($scope->result()), $this->query);
        }
        $this->getEntries();
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        // Special case when we are doing a search and no database is selected
        if ($this->getDbContext()->noDatabaseSelected() && !$this->useTypeahead()) {
            $this->getDatabaseEntries();
            return;
        }

        $database = $this->getDatabaseId();
        $value = $this->request->get("scope");
        if (empty($value)) {
            $this->doSearchByCategory($database);
            return;
        }
        $scope = PageQueryScope::from($value);

        $array = $this->searchByScope($scope, false, $this->getDbContext());
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
        $dbNum = 0;
        foreach ($this->getDbContext()->getDbNameList() as $key) {
            $dbContext = new DatabaseContext($dbNum, $this->getConfig());
            $booklist = new BookList($this->request, $dbContext, 1);
            [$array, $totalNumber] = $booklist->getBooksByQueryScope(["all" => $crit], 1, $ignoredCategories);
            $this->addDatabaseEntry($key, $dbNum, $totalNumber, $query);
            $dbContext->clear();
            $dbNum++;
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
            str_format($this->localize("bookword", $count), (string) $count),
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
            str_format($this->localize("search.result.none"), $this->query),
            "db:query:{$database}:{$scope}",
            " ",
            "text",
            [ new LinkNavigation($href) ],
            null,
            "tt-header"
        );
    }
}
