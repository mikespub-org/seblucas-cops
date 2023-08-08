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
use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\Link;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\Page;

class JSONRenderer
{
    public static $endpoint = Config::ENDPOINT["index"];

    /**
     * @param Book $book
     * @return array
     */
    public static function getBookContentArray($book)
    {
        global $config;
        $i = 0;
        $preferedData = [];
        foreach ($config['cops_prefered_format'] as $format) {
            if ($i == 2) {
                break;
            }
            if ($data = $book->getDataFormat($format)) {
                $i++;
                array_push($preferedData, ["url" => $data->getHtmlLink(),
                  "viewUrl" => $data->getViewHtmlLink(), "name" => $format]);
            }
        }
        $database = $book->getDatabaseId();

        $publisher = $book->getPublisher();
        if (is_null($publisher)) {
            $pn = "";
            $pu = "";
        } else {
            $pn = $publisher->name;
            $link = new LinkNavigation($publisher->getUri(), null, null, $database);
            $pu = $link->hrefXhtml();
        }

        $serie = $book->getSerie();
        if (is_null($serie)) {
            $sn = "";
            $scn = "";
            $su = "";
        } else {
            $sn = $serie->name;
            $scn = str_format(localize("content.series.data"), $book->seriesIndex, $serie->name);
            $link = new LinkNavigation($serie->getUri(), null, null, $database);
            $su = $link->hrefXhtml();
        }
        $cc = $book->getCustomColumnValues($config['cops_calibre_custom_column_list'], true);

        return ["id" => $book->id,
                      "hasCover" => $book->hasCover,
                      "preferedData" => $preferedData,
                      "preferedCount" => count($preferedData),
                      "rating" => $book->getRating(),
                      "publisherName" => $pn,
                      "publisherurl" => $pu,
                      "pubDate" => $book->getPubDate(),
                      "languagesName" => $book->getLanguages(),
                      "authorsName" => $book->getAuthorsName(),
                      "tagsName" => $book->getTagsName(),
                      "seriesName" => $sn,
                      "seriesIndex" => $book->seriesIndex,
                      "seriesCompleteName" => $scn,
                      "seriesurl" => $su,
                      "customcolumns_list" => $cc];
    }

    /**
     * @param Book $book
     * @return array
     */
    public static function getFullBookContentArray($book)
    {
        global $config;
        $out = self::getBookContentArray($book);
        $database = $book->getDatabaseId();

        $out ["coverurl"] = Data::getLink($book, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", null)->hrefXhtml();
        $out ["thumbnailurl"] = Data::getLink($book, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", null, null, $config['cops_html_thumbnail_height'] * 2)->hrefXhtml();
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
            if (!empty($config['cops_mail_configuration']) && !is_null($dataKindle) && $data->id == $dataKindle->id) {
                $tab ["mail"] = 1;
            }
            if ($data->format == "EPUB") {
                $tab ["readerUrl"] = Config::ENDPOINT["read"] . "?data={$data->id}&db={$database}";
            }
            array_push($out ["datas"], $tab);
        }
        $out ["authors"] = [];
        foreach ($book->getAuthors() as $author) {
            $link = new LinkNavigation($author->getUri(), null, null, $database);
            array_push($out ["authors"], ["name" => $author->name, "url" => $link->hrefXhtml()]);
        }
        $out ["tags"] = [];
        foreach ($book->getTags() as $tag) {
            $link = new LinkNavigation($tag->getUri(), null, null, $database);
            array_push($out ["tags"], ["name" => $tag->name, "url" => $link->hrefXhtml()]);
        }

        $out ["identifiers"] = [];
        foreach ($book->getIdentifiers() as $ident) {
            array_push($out ["identifiers"], ["name" => $ident->formattedType, "url" => $ident->getUri()]);
        }

        $out ["customcolumns_preview"] = $book->getCustomColumnValues($config['cops_calibre_custom_column_preview'], true);

        return $out;
    }

    public static function getContentArray($entry, $extraUri = "")
    {
        /** @var Entry|EntryBook $entry */
        if ($entry instanceof EntryBook) {
            $out = [ "title" => $entry->title];
            $out ["book"] = self::getBookContentArray($entry->book);
            return $out;
        }
        return [ "class" => $entry->className, "title" => $entry->title, "content" => $entry->content, "navlink" => $entry->getNavLink($extraUri), "number" => $entry->numberOfElement ];
    }

    public static function getContentArrayTypeahead($page)
    {
        /** @var Page $page */
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

    public static function addCompleteArray($in, $request)
    {
        global $config;
        $out = $in;

        $out ["c"] = [
            "version" => Config::VERSION,
            "i18n" => [
                "coverAlt" => localize("i18n.coversection"),
                "authorsTitle" => localize("authors.title"),
                "allbooksTitle" => localize("allbooks.title"),
                "bookwordTitle" => localize("bookword.title"),
                "recentTitle" => localize("recent.title"),
                "tagsTitle" => localize("tags.title"),
                "tagwordTitle" => localize("tagword.title"),
                "linksTitle" => localize("links.title"),
                "seriesTitle" => localize("series.title"),
                "customizeTitle" => localize("customize.title"),
                "aboutTitle" => localize("about.title"),
                "previousAlt" => localize("paging.previous.alternate"),
                "nextAlt" => localize("paging.next.alternate"),
                "searchAlt" => localize("search.alternate"),
                "sortAlt" => localize("sort.alternate"),
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
            ],
            "url" => [
                "detailUrl" => self::$endpoint . "?page=13&id={0}&db={1}",
                "coverUrl" => Config::ENDPOINT["fetch"] . "?id={0}&db={1}",
                "thumbnailUrl" => Config::ENDPOINT["fetch"] . "?height=" . $config['cops_html_thumbnail_height'] . "&id={0}&db={1}",
            ],
            "config" => [
                "use_fancyapps" => $config ["cops_use_fancyapps"],
                "max_item_per_page" => $config['cops_max_item_per_page'],
                "kindleHack"        => "",
                "server_side_rendering" => $request->render(),
                "html_tag_filter" => $config['cops_html_tag_filter'],
            ],
        ];
        if ($config['cops_thumbnail_handling'] == "1") {
            $out ["c"]["url"]["thumbnailUrl"] = $out ["c"]["url"]["coverUrl"];
        } elseif (!empty($config['cops_thumbnail_handling'])) {
            $out ["c"]["url"]["thumbnailUrl"] = $config['cops_thumbnail_handling'];
        }
        if (preg_match("/./", $request->agent())) {
            $out ["c"]["config"]["kindleHack"] = 'style="text-decoration: none !important;"';
        }
        return $out;
    }

    public static function getCurrentUrl($queryString)
    {
        return Config::ENDPOINT["json"] . '?' . Format::addURLParam($queryString, 'complete', 1);
    }

    /**
     * Summary of getJson
     * @param Request $request
     * @param bool $complete
     * @return array
     */
    public static function getJson($request, $complete = false)
    {
        global $config;
        // Use the configured home page if needed
        $homepage = Page::INDEX;
        if (!empty($config['cops_home_page']) && defined('SebLucas\Cops\Pages\Page::' . $config['cops_home_page'])) {
            $homepage = constant('SebLucas\Cops\Pages\Page::' . $config['cops_home_page']);
        }
        $page = $request->get("page", $homepage);
        $query = $request->get("query");
        $search = $request->get("search");
        $qid = $request->get("id");
        $n = $request->get("n", "1");
        $database = $request->get('db');

        $currentPage = Page::getPage($page, $qid, $query, $n, $request);
        $currentPage->InitializeContent();

        if ($search) {
            return self::getContentArrayTypeahead($currentPage);
        }

        $out = [ "title" => $currentPage->title];
        $out ["parentTitle"] = $currentPage->parentTitle;
        if (!empty($out ["parentTitle"])) {
            $out ["title"] = $out ["parentTitle"] . " > " . $out ["title"];
        }
        $entries = [];
        $extraUri = "";
        if (!empty($request->get('filter')) && !empty($currentPage->filterUri)) {
            $extraUri = $currentPage->filterUri;
        }
        foreach ($currentPage->entryArray as $entry) {
            array_push($entries, self::getContentArray($entry, $extraUri));
        }
        if (!is_null($currentPage->book)) {
            // setting this on Book gets cascaded down to Data if isEpubValidOnKobo()
            if ($config['cops_provide_kepub'] == "1" && preg_match("/Kobo/", $request->agent())) {
                $currentPage->book->updateForKepub = true;
            }
            $out ["book"] = self::getFullBookContentArray($currentPage->book);
        } elseif ($page == Page::BOOK_DETAIL) {
            $page = Page::INDEX;
        }
        $out ["databaseId"] = $database ?? "";
        $out ["databaseName"] = Database::getDbName($database);
        if ($out ["databaseId"] == "") {
            $out ["databaseName"] = "";
        }
        $out ["libraryName"] = $config['cops_title_default'];
        $out ["fullTitle"] = $out ["title"];
        if ($out ["databaseId"] != "" && $out ["databaseName"] != $out ["fullTitle"]) {
            $out ["fullTitle"] = $out ["databaseName"] . " > " . $out ["fullTitle"];
        }
        $out ["page"] = $page;
        $out ["multipleDatabase"] = Database::isMultipleDatabaseEnabled() ? 1 : 0;
        $out ["entries"] = $entries;
        $out ["sorted"] = $currentPage->sorted;
        $out ["isPaginated"] = 0;
        if ($currentPage->isPaginated()) {
            $prevLink = $currentPage->getPrevLink();
            $nextLink = $currentPage->getNextLink();
            $out ["isPaginated"] = 1;
            $out ["prevLink"] = "";
            if (!is_null($prevLink)) {
                $out ["prevLink"] = $prevLink->hrefXhtml();
            }
            $out ["nextLink"] = "";
            if (!is_null($nextLink)) {
                $out ["nextLink"] = $nextLink->hrefXhtml();
            }
            $out ["maxPage"] = $currentPage->getMaxPage();
            $out ["currentPage"] = $currentPage->n;
        }
        if (!is_null($request->get("complete")) || $complete) {
            $out = self::addCompleteArray($out, $request);
        }

        $out ["containsBook"] = 0;
        $out ["filterurl"] = false;
        $skipFilterUrl = [Page::AUTHORS_FIRST_LETTER, Page::ALL_BOOKS_LETTER, Page::ALL_BOOKS_YEAR, Page::ALL_RECENT_BOOKS, Page::BOOK_DETAIL, Page::CUSTOM_DETAIL];
        if ($currentPage->containsBook()) {
            $out ["containsBook"] = 1;
            // support {{=str_format(it.sorturl, "pubdate")}} etc. in templates (use double quotes for sort field)
            $out ["sorturl"] = self::$endpoint . Format::addURLParam("?" . $currentPage->getCleanQuery(), 'sort', null) . "&sort={0}";
            $out ["sortoptions"] = $currentPage->getSortOptions();
            if ($config['cops_show_filter_links'] == 1 && !in_array($page, $skipFilterUrl)) {
                $out ["filterurl"] = self::$endpoint . Format::addURLParam("?" . $currentPage->getCleanQuery(), 'filter', 1);
            }
        } elseif (!empty($qid) && $config['cops_show_filter_links'] == 1 && !in_array($page, $skipFilterUrl)) {
            $out ["filterurl"] = self::$endpoint . Format::addURLParam("?" . $currentPage->getCleanQuery(), 'filter', null);
        }

        $out["abouturl"] = self::$endpoint . Format::addURLParam("?page=" . Page::ABOUT, 'db', $database);
        $out["customizeurl"] = self::$endpoint . Format::addURLParam("?page=" . Page::CUSTOMIZE, 'db', $database);
        $out["filters"] = false;
        if ($request->hasFilter()) {
            $out["filters"] = [];
            foreach (Filter::getEntryArray($request, $database) as $entry) {
                array_push($out["filters"], self::getContentArray($entry));
            }
        }

        if ($page == Page::ABOUT) {
            $temp = preg_replace("/\<h1\>About COPS\<\/h1\>/", "<h1>About COPS " . Config::VERSION . "</h1>", file_get_contents('about.html'));
            $out ["fullhtml"] = $temp;
        }

        // multiple database setup
        if ($page != Page::INDEX && !is_null($database)) {
            if ($homepage != Page::INDEX) {
                $out ["homeurl"] = self::$endpoint .  "?" . Format::addURLParam("page=" . Page::INDEX, 'db', $database);
            } else {
                $out ["homeurl"] = self::$endpoint .  "?" . Format::addURLParam("", 'db', $database);
            }
        } elseif ($homepage != Page::INDEX) {
            $out ["homeurl"] = self::$endpoint . "?page=" . Page::INDEX;
        } else {
            $out ["homeurl"] = self::$endpoint;
        }

        $out ["parenturl"] = "";
        if (!empty($out["filters"]) && !empty($currentPage->currentUri)) {
            // if filtered, use the unfiltered uri as parent first
            $out ["parenturl"] = self::$endpoint . Format::addURLParam($currentPage->currentUri, 'db', $database);
        } elseif (!empty($currentPage->parentUri)) {
            // otherwise use the parent uri
            $out ["parenturl"] = self::$endpoint . Format::addURLParam($currentPage->parentUri, 'db', $database);
        } elseif ($page != Page::INDEX) {
            $out ["parenturl"] = $out ["homeurl"];
        }

        return $out;
    }
}
