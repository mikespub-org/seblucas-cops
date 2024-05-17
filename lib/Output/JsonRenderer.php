<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;
use Exception;

class JsonRenderer
{
    public static string $handler = "json";

    /**
     * @param Book $book
     * @return array<string, mixed>
     */
    public static function getBookContentArray($book)
    {
        $handler = $book->getHandler();
        $i = 0;
        $preferedData = [];
        foreach (Config::get('prefered_format') as $format) {
            if ($i == 2) {
                break;
            }
            $data = $book->getDataFormat($format);
            if ($data) {
                $i++;
                array_push($preferedData, ["url" => $data->getHtmlLink(),
                    "viewUrl" => $data->getViewHtmlLink(), "name" => $format]);
            }
        }
        $database = $book->getDatabaseId();

        $authors = [];
        foreach ($book->getAuthors() as $author) {
            $author->setHandler($handler);
            array_push($authors, ["name" => $author->name, "url" => $author->getUri()]);
        }

        $tags = [];
        foreach ($book->getTags() as $tag) {
            $tag->setHandler($handler);
            array_push($tags, ["name" => $tag->name, "url" => $tag->getUri()]);
        }

        $publisher = $book->getPublisher();
        if (empty($publisher)) {
            $pn = "";
            $pu = "";
        } else {
            $publisher->setHandler($handler);
            $pn = $publisher->name;
            $pu = $publisher->getUri();
        }

        $serie = $book->getSerie();
        if (empty($serie)) {
            $sn = "";
            $scn = "";
            $su = "";
        } else {
            $serie->setHandler($handler);
            $sn = $serie->name;
            $scn = str_format(localize("content.series.data"), $book->seriesIndex, $serie->name);
            $su = $serie->getUri();
        }
        $cc = $book->getCustomColumnValues(Config::get('calibre_custom_column_list'), true);

        return ["id" => $book->id,
            "detailurl" => $book->getDetailUrl($handler),
            "hasCover" => $book->hasCover,
            "preferedData" => $preferedData,
            "preferedCount" => count($preferedData),
            "rating" => $book->getRating(),
            "publisherName" => $pn,
            "publisherurl" => $pu,
            "pubDate" => $book->getPubDate(),
            "languagesName" => $book->getLanguages(),
            "authorsName" => $book->getAuthorsName(),
            "authors" => $authors,
            "tagsName" => $book->getTagsName(),
            "tags" => $tags,
            "seriesName" => $sn,
            "seriesIndex" => $book->seriesIndex,
            "seriesCompleteName" => $scn,
            "seriesurl" => $su,
            "customcolumns_list" => $cc];
    }

    /**
     * @param Book $book
     * @return array<string, mixed>
     */
    public static function getFullBookContentArray($book)
    {
        $handler = $book->getHandler();
        $out = static::getBookContentArray($book);
        $database = $book->getDatabaseId();

        $cover = new Cover($book);
        // set height for thumbnail here depending on opds vs. html (height x 2)
        if (in_array($handler, ['feed', 'opds'])) {
            $thumb = "opds2";
        } else {
            $thumb = "html2";
        }
        $out ["thumbnailurl"] = $cover->getThumbnailUri($thumb, false);
        $out ["coverurl"] = $cover->getCoverUri() ?? $out ["thumbnailurl"];
        $out ["content"] = $book->getComment(false);
        $out ["datas"] = [];
        $dataKindle = $book->GetMostInterestingDataToSendToKindle();
        foreach ($book->getDatas() as $data) {
            $tab = ["id" => $data->id,
                "format" => $data->format,
                "url" => $data->getHtmlLink(),
                "viewUrl" => $data->getViewHtmlLink(),
                "mail" => 0,
                "readerUrl" => ""];
            if (!empty(Config::get('mail_configuration')) && !is_null($dataKindle) && $data->id == $dataKindle->id) {
                $tab ["mail"] = 1;
            }
            if ($data->format == "EPUB") {
                $tab ["readerUrl"] = Route::link("read", null, ["data" => $data->id, "db" => ($database ?? 0)]);
            }
            array_push($out ["datas"], $tab);
        }
        $out ["authors"] = [];
        foreach ($book->getAuthors() as $author) {
            $author->setHandler($handler);
            array_push($out ["authors"], ["name" => $author->name, "url" => $author->getUri()]);
        }
        $out ["tags"] = [];
        foreach ($book->getTags() as $tag) {
            $tag->setHandler($handler);
            array_push($out ["tags"], ["name" => $tag->name, "url" => $tag->getUri()]);
        }

        $out ["identifiers"] = [];
        foreach ($book->getIdentifiers() as $ident) {
            array_push($out ["identifiers"], ["name" => $ident->formattedType, "url" => $ident->getLink()]);
        }

        $out ["customcolumns_preview"] = $book->getCustomColumnValues(Config::get('calibre_custom_column_preview'), true);

        return $out;
    }

    /**
     * Summary of getContentArray
     * @param Entry|EntryBook|null $entry
     * @param array<string, mixed> $extraParams
     * @return array<string, mixed>|bool
     */
    public static function getContentArray($entry, $extraParams = [])
    {
        if (is_null($entry)) {
            return false;
        }
        if ($entry instanceof EntryBook) {
            $out = [ "title" => $entry->title];
            $out ["book"] = static::getBookContentArray($entry->book);
            $out ["thumbnailurl"] = $entry->getThumbnail();
            $out ["coverurl"] = $entry->getImage() ?? $out ["thumbnailurl"];
            return $out;
        }
        switch ($entry->className) {
            case 'Author':
                $label = localize("authors.title");
                break;
            case 'Identifier':
                $label = localize("identifiers.title");
                break;
            case 'Language':
                $label = localize("languages.title");
                break;
            case 'Publisher':
                $label = localize("publishers.title");
                break;
            case 'Rating':
                $label = localize("ratings.title");
                break;
            case 'Serie':
                $label = localize("series.title");
                break;
            case 'Tag':
                $label = localize("tags.title");
                break;
            default:
                $label = $entry->className;
        }
        return [ "class" => $label, "title" => $entry->title, "content" => $entry->content, "navlink" => $entry->getNavLink($extraParams), "number" => $entry->numberOfElement ];
    }

    /**
     * Summary of getContentArrayTypeahead
     * @param Page $page
     * @return array<mixed>
     */
    public static function getContentArrayTypeahead($page)
    {
        $out = [];
        foreach ($page->entryArray as $entry) {
            if ($entry instanceof EntryBook) {
                array_push($out, ["class" => $entry->className, "title" => $entry->title, "navlink" => $entry->book->getDetailUrl()]);
            } else {
                array_push($out, ["class" => $entry->className, "title" => $entry->title, "navlink" => $entry->getNavLink()]);
            }
        }
        return $out;
    }

    /**
     * Summary of addCompleteArray
     * @param array<string, mixed> $in
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function addCompleteArray($in, $request)
    {
        $handler = $request->getHandler();
        $out = $in;
        // check for it.c.config.ignored_categories.whatever in templates for category 'whatever'
        $ignoredCategories = ['dummy'];
        $ignoredCategories = array_merge($ignoredCategories, $request->option('ignored_categories'));
        $ignoredCategories = array_flip($ignoredCategories);

        $out ["c"] = [
            "version" => Config::VERSION,
            "i18n" => [
                "addedDateTitle" => localize("addeddate.title"),
                "coverAlt" => localize("i18n.coversection"),
                "authorsTitle" => localize("authors.title"),
                "authorTitle" => localize("author.title"),
                "allbooksTitle" => localize("allbooks.title"),
                "bookwordTitle" => localize("bookword.title"),
                "recentTitle" => localize("recent.title"),
                "tagsTitle" => localize("tags.title"),
                "tagwordTitle" => localize("tagword.title"),
                "linksTitle" => localize("links.title"),
                "seriesTitle" => localize("series.title"),
                "defaultTemplate" => localize("default.template"),
                "customizeTitle" => localize("customize.title"),
                "aboutTitle" => localize("about.title"),
                "firstAlt" => localize("paging.first.alternate"),
                "previousAlt" => localize("paging.previous.alternate"),
                "nextAlt" => localize("paging.next.alternate"),
                "lastAlt" => localize("paging.last.alternate"),
                "searchAlt" => localize("search.alternate"),
                "sortAlt" => localize("sort.alternate"),
                "sortByTitle" => localize("sortby.title"),
                "homeAlt" => localize("home.alternate"),
                "cogAlt" => localize("cog.alternate"),
                "permalinkAlt" => localize("permalink.alternate"),
                "publisherName" => localize("publisher.name"),
                "pubdateTitle" => localize("pubdate.title"),
                "languagesTitle" => localize("languages.title"),
                "languageTitle" => localize("language.title"),
                "contentTitle" => localize("content.summary"),
                "filterClearAll" => localize("filter.clearall"),
                "sortorderAsc" => localize("search.sortorder.asc"),
                "sortorderDesc" => localize("search.sortorder.desc"),
                "customizeEmail" => localize("customize.email"),
                "ratingsTitle" => localize("ratings.title"),
                "ratingTitle" => localize("rating.title"),
                "librariesTitle" => localize("libraries.title"),
                "libraryTitle" => localize("library.title"),
                "linkTitle" => localize("extra.link"),
                "titleTitle" => localize("title.title"),
                "filtersTitle" => localize("filters.title"),
                "downloadAllTitle" => localize("downloadall.title"),
                "downloadAllTooltip" => localize("downloadall.tooltip"),
            ],
            "url" => [
                "detailUrl" => Route::link($handler) . "?page=13&id={0}&db={1}",
                "coverUrl" => Route::link("fetch") . "?id={0}&db={1}",
                "thumbnailUrl" => Route::link("fetch") . "?thumb=html&id={0}&db={1}",
            ],
            "config" => [
                "use_fancyapps" => Config::get('use_fancyapps'),
                "max_item_per_page" => Config::get('max_item_per_page'),
                "kindleHack"        => "",
                "server_side_rendering" => $request->render(),
                "html_tag_filter" => Config::get('html_tag_filter'),
                "ignored_categories" => $ignoredCategories,
            ],
        ];
        if (Config::get('thumbnail_handling') == "1") {
            $out ["c"]["url"]["thumbnailUrl"] = $out ["c"]["url"]["coverUrl"];
        } elseif (!empty(Config::get('thumbnail_handling'))) {
            $out ["c"]["url"]["thumbnailUrl"] = Config::get('thumbnail_handling');
        }
        if (preg_match("/./", $request->agent())) {
            $out ["c"]["config"]["kindleHack"] = 'style="text-decoration: none !important;"';
        }
        return $out;
    }

    /**
     * Summary of getCurrentUrl
     * @param Request $request
     * @return string
     */
    public static function getCurrentUrl($request)
    {
        $pathInfo = $request->path();
        $queryString = $request->query();
        //return Route::link(static::$handler) . $pathInfo . Route::query($queryString, ['complete' => 1]);
        $uri = $pathInfo . Route::query($queryString, ['complete' => 1]);
        if (Config::get('use_front_controller')) {
            if (str_starts_with($uri, '/')) {
                return Route::base() . substr($uri, 1);
            }
            return Route::base() . $uri;
        }
        return Route::base() . Config::ENDPOINT[static::$handler] . $uri;
    }

    /**
     * Summary of getJson
     * @param Request $request
     * @param bool $complete
     * @return array<string, mixed>
     */
    public static function getJson($request, $complete = false)
    {
        // Use the configured home page if needed
        $homepage = PageId::getHomePage();
        $page = $request->get("page", $homepage);
        $search = $request->get("search");
        $qid = $request->getId();
        $database = $request->database();
        $libraryId = $request->getVirtualLibrary();

        $currentPage = PageId::getPage($page, $request);
        try {
            $currentPage->InitializeContent();
        } catch (Exception $e) {
            // this will call exit()
            $request->notFound(Route::link("index"), $e->getMessage(), ['page' => 'index', 'db' => 0, 'vl' => 0]);
        }

        // adapt handler based on $request e.g. for rest api
        $handler = $request->getHandler();

        if ($search) {
            return static::getContentArrayTypeahead($currentPage);
        }

        $out = [ "title" => $currentPage->title];
        $out ["parentTitle"] = $currentPage->parentTitle;
        if (!empty($out ["parentTitle"])) {
            $out ["title"] = $out ["parentTitle"] . " > " . $out ["title"];
        }
        $out ["baseurl"] = Route::link($handler);
        $entries = [];
        $extraParams = [];
        $out ["isFilterPage"] = false;
        if (!empty($request->get('filter')) && !empty($currentPage->filterParams)) {
            $extraParams = $currentPage->filterParams;
            $out ["isFilterPage"] = true;
        }
        foreach ($currentPage->entryArray as $entry) {
            array_push($entries, static::getContentArray($entry, $extraParams));
        }
        if (!is_null($currentPage->book)) {
            // setting this on Book gets cascaded down to Data if isEpubValidOnKobo()
            if (Config::get('provide_kepub') == "1" && preg_match("/Kobo/", $request->agent())) {
                $currentPage->book->updateForKepub = true;
            }
            $out ["book"] = static::getFullBookContentArray($currentPage->book);
        } elseif ($page == PageId::BOOK_DETAIL) {
            $page = PageId::INDEX;
        }
        $out ["databaseId"] = $database ?? "";
        $out ["databaseName"] = Database::getDbName($database);
        if ($out ["databaseId"] == "") {
            $out ["databaseName"] = "";
        }
        $out ["libraryId"] = $libraryId ?? "";
        $out ["libraryName"] = Config::get('title_default');
        $out ["fullTitle"] = $out ["title"];
        if ($out ["databaseId"] != "" && $out ["databaseName"] != $out ["fullTitle"]) {
            $out ["fullTitle"] = $out ["databaseName"] . " > " . $out ["fullTitle"];
        }
        $out ["page"] = $page;
        $out ["multipleDatabase"] = Database::isMultipleDatabaseEnabled() ? 1 : 0;
        $out ["entries"] = $entries;
        $out ["entriesCount"] = count($entries);
        $out ["sorted"] = $currentPage->sorted ?? '';
        $out ["sortedBy"] = explode(' ', $out ["sorted"])[0];
        $out ["sortedDir"] = '';
        if (!empty($out ["sortedBy"])) {
            if (in_array($out ["sortedBy"], ['title', 'author', 'sort', 'name', 'type', 'lang_code', 'letter', 'year', 'range', 'value', 'groupid', 'series_index'])) {
                // default ascending order for anything vaguely alphabetical or grouped
                $out ["sortedDir"] = str_contains($out ["sorted"], 'desc') ? 'desc' : 'asc';
            } elseif (in_array($out ["sortedBy"], ['pubdate', 'rating', 'timestamp', 'count', 'series'])) {
                // default descending order for anything vaguely numerical or recent
                $out ["sortedDir"] = str_contains($out ["sorted"], 'asc') ? 'asc' : 'desc';
            } else {
                // default descending order for anything else we forgot above :-)
                $out ["sortedDir"] = str_contains($out ["sorted"], 'asc') ? 'asc' : 'desc';
            }
        }
        $out ["isPaginated"] = 0;
        if ($currentPage->isPaginated()) {
            $prevLink = $currentPage->getPrevLink();
            $nextLink = $currentPage->getNextLink();
            $out ["isPaginated"] = 1;
            $out ["firstLink"] = "";
            $out ["prevLink"] = "";
            if (!is_null($prevLink)) {
                $out ["firstLink"] = $currentPage->getFirstLink()->hrefXhtml();
                $out ["prevLink"] = $prevLink->hrefXhtml();
            }
            $out ["nextLink"] = "";
            $out ["lastLink"] = "";
            if (!is_null($nextLink)) {
                $out ["nextLink"] = $nextLink->hrefXhtml();
                $out ["lastLink"] = $currentPage->getLastLink()->hrefXhtml();
            }
            $out ["maxPage"] = $currentPage->getMaxPage();
            $out ["currentPage"] = $currentPage->n;
        }
        if (!is_null($request->get("complete")) || $complete) {
            $out = static::addCompleteArray($out, $request);
        }

        $out ["containsBook"] = 0;
        $out ["filterurl"] = false;
        if ($request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
        } else {
            $filterLinks = Config::get('html_filter_links');
        }
        $skipFilterUrl = [PageId::AUTHORS_FIRST_LETTER, PageId::ALL_BOOKS_LETTER, PageId::ALL_BOOKS_YEAR, PageId::ALL_RECENT_BOOKS, PageId::BOOK_DETAIL];
        if ($currentPage->containsBook()) {
            $out ["containsBook"] = 1;
            // support {{=str_format(it.sorturl, "pubdate")}} etc. in templates (use double quotes for sort field)
            $params = $request->getCleanParams();
            $params['sort'] = '{0}';
            $out ["sorturl"] = str_replace('%7B0%7D', '{0}', Route::link($handler, null, $params));
            $out ["sortoptions"] = $currentPage->getSortOptions();
            if (!empty($qid) && !empty($filterLinks) && !in_array($page, $skipFilterUrl)) {
                $params = $request->getCleanParams();
                $params['filter'] = 1;
                $out ["filterurl"] = Route::link($handler, null, $params);
            }
        } elseif (!empty($qid) && !empty($filterLinks) && !in_array($page, $skipFilterUrl)) {
            $params = $request->getCleanParams();
            $params['filter'] = null;
            $out ["filterurl"] = Route::link($handler, null, $params);
        }

        $out["abouturl"] = Route::link($handler, PageId::ABOUT, ['db' => $database]);
        $out["customizeurl"] = Route::link($handler, PageId::CUSTOMIZE, ['db' => $database]);
        $out["filters"] = false;
        if ($request->hasFilter()) {
            $out["filters"] = [];
            foreach (Filter::getEntryArray($request, $database) as $entry) {
                array_push($out["filters"], static::getContentArray($entry, ['filter' => 1]));
            }
            if (empty($out["filters"])) {
                $out["filters"] = false;
            }
        }

        if ($page == PageId::ABOUT) {
            $temp = preg_replace("/\<h1\>About COPS\<\/h1\>/", "<h1>About COPS " . Config::VERSION . "</h1>", file_get_contents('templates/about.html'));
            $out ["fullhtml"] = $temp;
        }

        // multiple database setup
        if ($page != PageId::INDEX && !is_null($database)) {
            if ($homepage != PageId::INDEX) {
                $out ["homeurl"] = Route::link($handler, PageId::INDEX, ['db' => $database]);
            } else {
                $out ["homeurl"] = Route::link($handler, null, ['db' => $database]);
            }
        } elseif ($homepage != PageId::INDEX) {
            $out ["homeurl"] = Route::link($handler, PageId::INDEX);
        } else {
            $out ["homeurl"] = $out["baseurl"];
        }

        $out ["parenturl"] = "";
        if (!empty($out["filters"]) && !empty($currentPage->currentUri)) {
            // if filtered, use the unfiltered uri as parent first
            $out ["parenturl"] = $currentPage->currentUri;
        } elseif (!empty($currentPage->parentUri)) {
            // otherwise use the parent uri
            $out ["parenturl"] = $currentPage->parentUri;
        } elseif ($page != PageId::INDEX) {
            if ($request->hasFilter()) {
                $filterParams = $request->getFilterParams();
                $filterParams["db"] = $database;
                $out ["parenturl"] = Route::link($handler, PageId::INDEX, $filterParams);
            } else {
                $out ["parenturl"] = $out["homeurl"];
            }
        }
        $out ["hierarchy"] = false;
        if ($currentPage->hierarchy) {
            $out ["hierarchy"] = [
                "parent" => static::getContentArray($currentPage->hierarchy['parent'], $extraParams),
                "current" => static::getContentArray($currentPage->hierarchy['current'], $extraParams),
                "children" => [],
                "hastree" => $request->get('tree', false),
            ];
            foreach ($currentPage->hierarchy['children'] as $entry) {
                array_push($out ["hierarchy"]["children"], static::getContentArray($entry, $extraParams));
            }
        }
        $out ["extra"] = $currentPage->extra;
        $out ["assets"] = Route::url(Config::get('assets'));
        // avoid messy Javascript issue with empty array being truthy or falsy - see #40
        $out ["download"] = false;
        if ($currentPage->containsBook()) {
            if (!empty(Config::get('download_page'))) {
                $out ["download"] = [];
                foreach (Config::get('download_page') as $format) {
                    $params = $request->getCleanParams();
                    $params['type'] = strtolower($format);
                    $url = Route::link(Zipper::$handler, null, $params);
                    array_push($out ["download"], ['url' => $url, 'format' => $format]);
                }
            } elseif (!empty($qid)) {
                if ($page == PageId::SERIE_DETAIL && !empty(Config::get('download_series'))) {
                    $out ["download"] = [];
                    foreach (Config::get('download_series') as $format) {
                        $params = [];
                        $params['series'] = $qid;
                        $params['type'] = strtolower($format);
                        $params['db'] = $database;
                        $url = Route::link(Zipper::$handler, null, $params);
                        array_push($out ["download"], ['url' => $url, 'format' => $format]);
                    }
                }
                if ($page == PageId::AUTHOR_DETAIL && !empty(Config::get('download_author'))) {
                    $out ["download"] = [];
                    foreach (Config::get('download_author') as $format) {
                        $params = [];
                        $params['author'] = $qid;
                        $params['type'] = strtolower($format);
                        $params['db'] = $database;
                        $url = Route::link(Zipper::$handler, null, $params);
                        array_push($out ["download"], ['url' => $url, 'format' => $format]);
                    }
                }
            }
        }

        /** @phpstan-ignore-next-line */
        if (Database::KEEP_STATS) {
            $out ["dbstats"] = Database::getDbStatistics();
        }

        return $out;
    }
}
