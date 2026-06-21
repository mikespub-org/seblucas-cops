<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Calibre\Folder;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Handlers\ReadHandler;
use SebLucas\Cops\Handlers\ZipperHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageAbout;

class JsonRenderer extends BaseRenderer
{
    /** @var class-string */
    public static $fetcher = FetchHandler::class;
    /** @var class-string */
    public static $reader = ReadHandler::class;
    /** @var class-string */
    public static $zipper = ZipperHandler::class;

    /** @var Request */
    protected $request;
    /** @var ?int */
    protected $database = null;
    /** @var int|string */
    protected $page;
    /** @var int|string */
    protected $homepage;
    /** @var array<string, mixed> */
    protected $extraParams = [];

    /**
     * Summary of getCurrentUrl
     * @param Request $request
     * @return string
     */
    public static function getCurrentUrl($request)
    {
        // with same _route param here
        $params = $request->urlParams;
        $params['complete'] = 1;
        return JsonHandler::link($params);
    }

    /**
     * @param Book $book
     * @return array<string, mixed>
     */
    public function getBookContentArray($book)
    {
        $handler = $book->getHandler();
        $i = 0;
        $preferedData = [];
        foreach ($this->config('prefered_format') as $format) {
            if ($i == 2) {
                break;
            }
            $data = $book->getDataFormat($format);
            if ($data) {
                $i++;
                array_push($preferedData, [
                    "name" => $format,
                    "url" => $data->getHtmlLink(),
                    "viewUrl" => $data->getViewHtmlLink(),
                    "size" => $data->getHumanSize(),
                ]);
            }
        }

        $authors = [];
        foreach ($book->getAuthors() as $author) {
            $author->setHandler($handler);
            array_push($authors, [
                "name" => $author->name,
                "url" => $author->getUri(),
            ]);
        }

        $tags = [];
        foreach ($book->getTags() as $tag) {
            $tag->setHandler($handler);
            // @todo add link to parent(s) for hierarchical tags?
            if ($tag->hasChildCategories()) {
                $tagName = str_replace(".", " > ", $tag->name);
            } else {
                $tagName = $tag->name;
            }
            array_push($tags, [
                "name" => $tagName,
                "url" => $tag->getUri(),
            ]);
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
            // @todo add link to parent(s) for hierarchical series?
            if ($serie->hasChildCategories()) {
                $sn = str_replace(".", " > ", $serie->name);
            } else {
                $sn = $serie->name;
            }
            $scn = str_format($this->localize("content.series.data"), (string) $book->seriesIndex, (string) $serie->name);
            $su = $serie->getUri();
        }
        $cc = $book->getCustomColumnValues($this->config('calibre_custom_column_list'), true);

        return [
            "id" => $book->id,
            "detailurl" => $book->getUri(),
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
            "customcolumns_list" => $cc,
        ];
    }

    /**
     * @param Book $book
     * @return array<string, mixed>
     */
    public function getFullBookContentArray($book)
    {
        $handler = $book->getHandler();
        $out = $this->getBookContentArray($book);
        $database = $book->getDatabaseId() ?? 0;

        $cover = new Cover($book);
        // set height for thumbnail here depending on opds vs. html (height x 2)
        if (!empty($handler) && in_array($handler::HANDLER, ['feed', 'opds'])) {
            $thumb = "opds2";
        } else {
            $thumb = "html2";
        }
        // Note: this will fail for books in folders /ebook/ if book title is updated based on EPUB file
        $out ["thumbnailurl"] = $cover->getThumbnailUri($thumb, false);
        $out ["coverurl"] = $cover->getCoverUri() ?? $out ["thumbnailurl"];
        // Try getting cover from .cbz or .epub file instead for folders
        if (empty($out["thumbnailurl"]) && empty($book->getCoverFileName()) && isset($book->folderId)) {
            $data = $book->getDataFormat(ComicReader::EXTENSION);
            /** */
            if (!$data) {
                $data = $book->getDataFormat(EPubReader::EXTENSION);
            }
            /** */
            if ($data) {
                $coverLink = Cover::getFolderDataLink($data, $thumb);
                if ($coverLink) {
                    $out ["thumbnailurl"] = $coverLink->getUri();
                    $out ["hasCover"] = true;
                }
                $coverLink = Cover::getFolderDataLink($data);
                if ($coverLink) {
                    $out ["coverurl"] = $coverLink->getUri();
                    $out ["hasCover"] = true;
                }
            }
        }
        $out ["content"] = $book->getComment(false);
        $out ["pages"] = $book->getPages();
        if (isset($book->folderId)) {
            $out ["folderId"] = $book->folderId ?: $this->localize("folders.root");
            $out ["folderUrl"] = JsonHandler::route(Folder::ROUTE_DETAIL, ["path" => $book->folderId]);
        } else {
            $out ["folderId"] = '';
            $out ["folderUrl"] = '';
        }
        $out ["datas"] = [];
        $dataKindle = $book->GetMostInterestingDataToSendToKindle();
        foreach ($book->getDatas() as $data) {
            $tab = [
                "id" => $data->id,
                "format" => $data->format,
                "url" => $data->getHtmlLink(),
                "viewUrl" => $data->getViewHtmlLink(),
                "size" => $data->getHumanSize(),
                "mail" => 0,
                "qrcode" => 0,
                "readerUrl" => "",
            ];
            if (!is_null($dataKindle) && $data->id == $dataKindle->id) {
                // only show QR code if we have a full url here
                if (str_contains($tab["url"], '://')) {
                    $tab ["qrcode"] = 1;
                }
                if (!empty($this->config('mail_configuration'))) {
                    $tab ["mail"] = 1;
                }
            }
            $readers = [
                'epub' => $this->config('epub_reader'),
                'comic' => $this->config('epub_reader'),
                'pdf' => $this->config('pdfjs_viewer'),
            ];
            $tab ["readerUrl"] = self::$reader::getReaderUrl($data, $readers);
            array_push($out ["datas"], $tab);
        }
        $out ["extraFiles"] = [];
        foreach ($book->getExtraFiles() as $fileName) {
            $link = $book->getExtraFileLink($fileName);
            array_push($out ["extraFiles"], [
                "name" => $link->title,
                "url" => $link->getUri(),
                "length" => $link->getSize(),
                "mtime" => $link->getLastModified(),
            ]);
        }
        if (count($out ["extraFiles"]) > 0) {
            $params = [];
            $params['id'] = $book->id;
            $params['db'] = $database;
            $params['file'] = 'zipped';
            $url = self::$fetcher::route(Book::ROUTE_FILE, $params);
            array_unshift($out ["extraFiles"], [
                "name" => " * ",
                "url" => $url,
            ]);
        }
        $out ["authors"] = [];
        foreach ($book->getAuthors() as $author) {
            $author->setHandler($handler);
            array_push($out ["authors"], [
                "name" => $author->name,
                "url" => $author->getUri(),
            ]);
        }
        $out ["tags"] = [];
        foreach ($book->getTags() as $tag) {
            $tag->setHandler($handler);
            // @todo add link to parent(s) for hierarchical tags?
            if ($tag->hasChildCategories()) {
                $tagName = str_replace(".", " > ", $tag->name);
            } else {
                $tagName = $tag->name;
            }
            array_push($out ["tags"], [
                "name" => $tagName,
                "url" => $tag->getUri(),
            ]);
        }

        $out ["identifiers"] = [];
        foreach ($book->getIdentifiers() as $ident) {
            array_push($out ["identifiers"], [
                "name" => $ident->formattedType,
                "url" => $ident->getValueUri(),
            ]);
        }

        $out ["customcolumns_preview"] = $book->getCustomColumnValues($this->config('calibre_custom_column_preview'), true);

        return $out;
    }

    /**
     * Summary of getContentArray
     * @param Entry|EntryBook|null $entry
     * @param array<string, mixed> $extraParams
     * @return array<string, mixed>|bool
     */
    public function getContentArray($entry, $extraParams = [])
    {
        if (is_null($entry)) {
            return false;
        }
        if ($entry instanceof EntryBook) {
            $out = [
                "title" => $entry->title,
                "book" => $this->getBookContentArray($entry->book),
                "thumbnailurl" => $entry->getThumbnail(),
                "coverurl" => $entry->getImage(),
            ];
            $out ["coverurl"] ??= $out ["thumbnailurl"];
            return $out;
        }
        $label = match ($entry->className) {
            'Author' => $this->localize("authors.title"),
            'Identifier' => $this->localize("identifiers.title"),
            'Language' => $this->localize("languages.title"),
            'Publisher' => $this->localize("publishers.title"),
            'Rating' => $this->localize("ratings.title"),
            'Serie' => $this->localize("series.title"),
            'Tag' => $this->localize("tags.title"),
            'Folder' => $this->localize("folders.title"),
            default => $entry->className,
        };
        return [
            "class" => $label,
            "title" => $entry->title,
            "content" => $entry->content,
            "navlink" => $entry->getNavLink($extraParams),
            "number" => $entry->numberOfElement,
        ];
    }

    /**
     * Summary of getContentArrayTypeahead
     * @param Page $currentPage
     * @return array<mixed>
     */
    public function getContentArrayTypeahead($currentPage)
    {
        $out = [];
        foreach ($currentPage->entryArray as $entry) {
            if ($entry instanceof EntryBook) {
                array_push($out, [
                    "class" => $entry->className,
                    "title" => $entry->title,
                    "navlink" => $entry->book->getUri(),
                ]);
            } else {
                array_push($out, [
                    "class" => $entry->className,
                    "title" => $entry->title,
                    "navlink" => $entry->getNavLink(),
                ]);
            }
        }
        return $out;
    }

    /**
     * Summary of getCompleteArray
     * @return array<string, mixed>
     */
    public function getCompleteArray()
    {
        // check for it.c.config.ignored_categories.whatever in templates for category 'whatever'
        $ignoredCategories = ['dummy'];
        $ignoredCategories = array_merge($ignoredCategories, $this->request->option('ignored_categories'));
        $ignoredCategories = array_flip($ignoredCategories);

        $complete = [
            "version" => Config::VERSION,
            "i18n" => [
                "addedDateTitle" => $this->localize("addeddate.title"),
                "coverAlt" => $this->localize("i18n.coversection"),
                "authorsTitle" => $this->localize("authors.title"),
                "authorTitle" => $this->localize("author.title"),
                "allbooksTitle" => $this->localize("allbooks.title"),
                "bookwordTitle" => $this->localize("bookword.title"),
                "foldersTitle" => $this->localize("folders.title"),
                "recentTitle" => $this->localize("recent.title"),
                "tagsTitle" => $this->localize("tags.title"),
                "tagwordTitle" => $this->localize("tagword.title"),
                "linksTitle" => $this->localize("links.title"),
                "seriesTitle" => $this->localize("series.title"),
                "defaultTemplate" => $this->localize("default.template"),
                "customizeTitle" => $this->localize("customize.title"),
                "aboutTitle" => $this->localize("about.title"),
                "firstAlt" => $this->localize("paging.first.alternate"),
                "previousAlt" => $this->localize("paging.previous.alternate"),
                "nextAlt" => $this->localize("paging.next.alternate"),
                "lastAlt" => $this->localize("paging.last.alternate"),
                "searchAlt" => $this->localize("search.alternate"),
                "sortAlt" => $this->localize("sort.alternate"),
                "sortByTitle" => $this->localize("sortby.title"),
                "homeAlt" => $this->localize("home.alternate"),
                "cogAlt" => $this->localize("cog.alternate"),
                "permalinkAlt" => $this->localize("permalink.alternate"),
                "publisherName" => $this->localize("publisher.name"),
                "pubdateTitle" => $this->localize("pubdate.title"),
                "pagesTitle" => $this->localize("pages.title"),
                "languagesTitle" => $this->localize("languages.title"),
                "languageTitle" => $this->localize("language.title"),
                "contentTitle" => $this->localize("content.summary"),
                "filterClearAll" => $this->localize("filter.clearall"),
                "sortorderAsc" => $this->localize("search.sortorder.asc"),
                "sortorderDesc" => $this->localize("search.sortorder.desc"),
                "customizeEmail" => $this->localize("customize.email"),
                "ratingsTitle" => $this->localize("ratings.title"),
                "ratingTitle" => $this->localize("rating.title"),
                "librariesTitle" => $this->localize("libraries.title"),
                "libraryTitle" => $this->localize("library.title"),
                "linkTitle" => $this->localize("extra.link"),
                "filesTitle" => $this->localize("extra.files"),
                "folderTitle" => $this->localize("folder.title"),
                "titleTitle" => $this->localize("title.title"),
                "filtersTitle" => $this->localize("filters.title"),
                "downloadAllTitle" => $this->localize("downloadall.title"),
                "downloadAllTooltip" => $this->localize("downloadall.tooltip"),
            ],
            "url" => [
                // route urls do not accept non-numeric id or db to find match here + url does not include author or title
                "detailUrl" => str_replace(['0', '1'], ['{0}', '{1}'], $this->getRoute(Book::ROUTE_PAGEID, ['id' => '0', 'db' => '1'])),
                "coverUrl" => str_replace(['0', '1'], ['{0}', '{1}'], self::$fetcher::route(Cover::ROUTE_COVER, ['id' => '0', 'db' => '1'])),
                "thumbnailUrl" => str_replace(['0', '1'], ['{0}', '{1}'], self::$fetcher::route(Cover::ROUTE_THUMB, ['thumb' => 'html', 'id' => '0', 'db' => '1'])),
            ],
            "config" => [
                "use_fancyapps" => $this->config('use_fancyapps'),
                "max_item_per_page" => $this->config('max_item_per_page'),
                "kindleHack"        => "",
                "server_side_rendering" => $this->request->render(),
                "html_tag_filter" => $this->config('html_tag_filter'),
                "ignored_categories" => $ignoredCategories,
            ],
        ];
        if ($this->config('thumbnail_handling') == "1") {
            $complete["url"]["thumbnailUrl"] = $complete["url"]["coverUrl"];
        } elseif (!empty($this->config('thumbnail_handling'))) {
            $complete["url"]["thumbnailUrl"] = $this->config('thumbnail_handling');
        }
        if (preg_match("/./", $this->request->agent())) {
            $complete["config"]["kindleHack"] = 'style="text-decoration: none !important;"';
        }
        return $complete;
    }

    /**
     * Summary of addPagination
     * @param Page $currentPage
     * @return array<string, mixed>
     */
    public function addPagination($currentPage)
    {
        $out = [];
        if (!$currentPage->isPaginated()) {
            $out ["isPaginated"] = 0;
            return $out;
        }
        $prevLink = $currentPage->getPrevLink();
        $nextLink = $currentPage->getNextLink();
        $out ["isPaginated"] = 1;
        $out ["firstLink"] = "";
        $out ["prevLink"] = "";
        if (!is_null($prevLink)) {
            $out ["firstLink"] = $currentPage->getFirstLink()->getUri();
            $out ["prevLink"] = $prevLink->getUri();
        }
        $out ["nextLink"] = "";
        $out ["lastLink"] = "";
        if (!is_null($nextLink)) {
            $out ["nextLink"] = $nextLink->getUri();
            $out ["lastLink"] = $currentPage->getLastLink()->getUri();
        }
        $out ["maxPage"] = $currentPage->getMaxPage();
        $out ["currentPage"] = $currentPage->n;
        return $out;
    }

    /**
     * Summary of addSortFilter
     * @param Page $currentPage
     * @return array<string, mixed>
     */
    public function addSortFilter($currentPage)
    {
        $out = [];
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
        $out ["containsBook"] = 0;
        $out ["filterurl"] = false;
        if ($currentPage->containsBook()) {
            $out ["containsBook"] = 1;
            // support {{=str_format(it.sorturl, "pubdate")}} etc. in templates (use double quotes for sort field)
            $params = $this->request->getCleanParams();
            $params['sort'] = 'SORTED';
            $out ["sorturl"] = str_replace('SORTED', '{0}', $this->getLink($params));
            $out ["sortoptions"] = $currentPage->getSortOptions();
            if ($currentPage->canFilter()) {
                $params = $this->request->getCleanParams();
                $params['filter'] = 1;
                $out ["filterurl"] = $this->getLink($params);
            }
        } elseif (!empty($currentPage->extra)) {
            // show extra info or series in Page*Detail (without books)
            $out ["containsBook"] = 1;
            $out ["sortoptions"] = [];
            if ($currentPage->canFilter()) {
                $params = $this->request->getCleanParams();
                $params['filter'] = 1;
                $out ["filterurl"] = $this->getLink($params);
            }
        } else {
            if ($currentPage->isPaginated()) {
                // support {{=str_format(it.sorturl, "count")}} etc. in templates (use double quotes for sort field)
                $params = $this->request->getCleanParams();
                $params['sort'] = 'SORTED';
                $out ["sorturl"] = str_replace('SORTED', '{0}', $this->getLink($params));
                $out ["sortoptions"] = [
                    'name' => $this->localize("sort.names"),
                    'count' => $this->localize("sort.count"),
                ];
            } else {
                $out ["sortoptions"] = [];
            }
            if ($currentPage->canFilter()) {
                $params = $this->request->getCleanParams();
                $params['filter'] = null;
                $out ["filterurl"] = $this->getLink($params);
            }
        }
        return $out;
    }

    /**
     * Summary of getFiltersArray
     * @param DatabaseContext $dbContext
     * @return array<mixed>|false
     */
    public function getFiltersArray($dbContext)
    {
        $filters = false;
        if (!$this->request->hasFilter()) {
            return $filters;
        }
        $filters = [];
        foreach (Filter::getEntryArray($this->request, $dbContext) as $entry) {
            array_push($filters, $this->getContentArray($entry, ['filter' => 1]));
        }
        if (empty($filters)) {
            $filters = false;
        }
        return $filters;
    }

    /**
     * Summary of getHomeUrl
     * @param string $baseurl
     * @return string
     */
    public function getHomeUrl($baseurl)
    {
        // multiple database setup
        if ($this->page != PageId::INDEX && !is_null($this->database)) {
            $params = [];
            $params['db'] = $this->database;
            if ($this->homepage != PageId::INDEX) {
                $homeurl = $this->getRoute(PageId::ROUTE_INDEX, $params);
            } else {
                $homeurl = $this->getLink($params);
            }
        } elseif ($this->homepage != PageId::INDEX) {
            $homeurl = $this->getRoute(PageId::ROUTE_INDEX);
        } else {
            $homeurl = $baseurl;
        }
        return $homeurl;
    }

    /**
     * Summary of getParentLink
     * @param Page $currentPage
     * @param array<mixed>|false $filters
     * @param string $homeurl
     * @return string
     */
    public function getParentUrl($currentPage, $filters, $homeurl)
    {
        $parenturl = "";
        if (!empty($filters) && !empty($currentPage->currentUri)) {
            // if filtered, use the unfiltered uri as parent first
            $parenturl = $currentPage->currentUri;
        } elseif (!empty($currentPage->parentUri)) {
            // otherwise use the parent uri
            $parenturl = $currentPage->parentUri;
        } elseif ($this->page != PageId::INDEX) {
            if ($this->request->hasFilter()) {
                $filterParams = $this->request->getFilterParams();
                $filterParams["db"] = $this->database;
                $parenturl = $this->getRoute(PageId::ROUTE_INDEX, $filterParams);
            } else {
                $parenturl = $homeurl;
            }
        }
        return $parenturl;
    }

    /**
     * Summary of getHierarchy
     * @param Page $currentPage
     * @param array<string, mixed> $extraParams
     * @return array<mixed>|false
     */
    public function getHierarchy($currentPage, $extraParams)
    {
        $hierarchy = false;
        if (!$currentPage->hierarchy) {
            return $hierarchy;
        }
        $hastree = $currentPage->hierarchy['hastree'];
        if ($hastree) {
            $current = $this->getContentArray($currentPage->hierarchy['current'], $extraParams);
        } else {
            $params = $extraParams;
            $params['tree'] = 1;
            $current = $this->getContentArray($currentPage->hierarchy['current'], $params);
        }
        $hierarchy = [
            "parents" => [],
            "current" => $current,
            "children" => [],
            "hastree" => $hastree,
        ];
        foreach ($currentPage->hierarchy['parents'] as $entry) {
            array_push($hierarchy["parents"], $this->getContentArray($entry, $extraParams));
        }
        foreach ($currentPage->hierarchy['children'] as $entry) {
            array_push($hierarchy["children"], $this->getContentArray($entry, $extraParams));
        }
        return $hierarchy;
    }

    /**
     * Summary of getSeries
     * @param Page $currentPage
     * @param array<string, mixed> $extraParams
     * @return array<mixed>|false
     */
    public function getSeries($currentPage, $extraParams)
    {
        $series = false;
        if (empty($currentPage->extra['series'])) {
            return $series;
        }
        $series = [];
        foreach ($currentPage->extra['series'] as $entry) {
            // @todo add link to parent(s) for hierarchical series?
            if (!empty($entry->instance) && $entry->instance->hasChildCategories()) {
                $entry->title = str_replace(".", " > ", $entry->title);
            }
            array_push($series, $this->getContentArray($entry, $extraParams));
        }
        return $series;
    }

    /**
     * Summary of getDownloadLinks
     * @param Page $currentPage
     * @param ?int $qid
     * @return array<mixed>|false
     */
    public function getDownloadLinks($currentPage, $qid)
    {
        // avoid messy Javascript issue with empty array being truthy or falsy - see #40
        $download = false;
        if (!$currentPage->containsBook()) {
            return $download;
        }
        // download per page
        if (empty($this->config('download_page'))) {
            return $download;
        }
        $download = [];
        foreach ($this->config('download_page') as $format) {
            $params = $this->request->getCleanParams();
            $params['type'] = strtolower((string) $format);
            unset($params['title']);
            if (!empty($params['id'])) {
                $url = self::$zipper::route('zipper-page-id-type', $params);
            } else {
                $url = self::$zipper::route('zipper-page-type', $params);
            }
            array_push($download, ['url' => $url, 'format' => $format]);
        }
        return $download;
    }

    /**
     * Summary of getFilterGroups
     * @param array<mixed> $entries
     * @return array<mixed>
     */
    public function getFilterGroups($entries)
    {
        $filterGroups = [];
        $group = ['header' => '', 'entries' => []];
        foreach ($entries as $entry) {
            if (!empty($entry["class"])) {
                array_push($group['entries'], $entry);
                continue;
            }
            if (!empty($group['header'])) {
                array_push($filterGroups, $group);
            }
            $group = [
                'header' => $entry,
                'entries' => [],
            ];
        }
        if (!empty($group['header'])) {
            array_push($filterGroups, $group);
        }
        return $filterGroups;
    }

    /**
     * Summary of getJson
     * @param Request $request
     * @param bool $complete
     * @return array<string, mixed>
     */
    public function getJson($request, $complete = false)
    {
        $this->setRequest($request);
        $search = $request->get("search");
        $qid = $request->getId();
        $libraryId = $request->getVirtualLibrary();

        $currentPage = PageId::getPage($this->page, $request);
        $dbContext = $currentPage->getDbContext();

        // handle folder book as book page
        if ($this->page == "folder" && !empty($currentPage->book)) {
            $this->page = "book";
        }

        if ($search) {
            return $this->getContentArrayTypeahead($currentPage);
        }

        $out = [ "title" => $currentPage->title];
        $out ["parentTitle"] = $currentPage->parentTitle;
        if (!empty($out ["parentTitle"])) {
            if ($currentPage->hierarchy) {
                $separator = $currentPage->hierarchy["separator"];
                // @todo add link to parent(s) for hierarchical series, tags etc.
                $out ["title"] = $out ["parentTitle"] . " > " . str_replace($separator, " > ", $out ["title"]);
            } else {
                $out ["title"] = $out ["parentTitle"] . " > " . $out ["title"];
            }
        }
        $out ["baseurl"] = $this->getLink();
        $entries = [];
        $extraParams = [];
        $out ["isFilterPage"] = false;
        if (!empty($request->get('filter')) && !empty($currentPage->filterParams)) {
            // @todo get rid of extraParams as filters should be included in navlink now
            $extraParams = $currentPage->filterParams;
            $out ["isFilterPage"] = true;
        } elseif ($currentPage->idPage == PageId::FILTER_ID) {
            $out ["isFilterPage"] = true;
            /** @var \SebLucas\Cops\Pages\PageFilter $currentPage */
            $out ["checked"] = $currentPage->filter;
        }
        foreach ($currentPage->entryArray as $entry) {
            array_push($entries, $this->getContentArray($entry, $extraParams));
        }
        // group entries by filter group for twigged template - see filters.html
        if ($out["isFilterPage"] && $this->request->template() === "twigged") {
            $out["filterGroups"] = $this->getFilterGroups($entries);
        }
        if (!is_null($currentPage->book)) {
            // setting this on Book gets cascaded down to Data if isEpubValidOnKobo()
            if ($this->config('provide_kepub') == "1" && preg_match("/Kobo/", $request->agent())) {
                $currentPage->book->updateForKepub = true;
            }
            $out ["book"] = $this->getFullBookContentArray($currentPage->book);
        } elseif ($this->page == PageId::BOOK_DETAIL) {
            $this->page = PageId::INDEX;
        }

        $out ["databaseId"] = $this->database ?? "";
        $out ["databaseName"] = $dbContext->getDbName();
        if ($out ["databaseId"] == "") {
            $out ["databaseName"] = "";
        }
        $out ["libraryId"] = $libraryId ?? "";
        $out ["libraryName"] = $this->config('title_default');
        $out ["fullTitle"] = $out ["title"];
        $out ["multipleDatabase"] = $dbContext->isMultipleDatabaseEnabled() ? 1 : 0;
        if (!empty($out ["multipleDatabase"]) && $out ["databaseId"] != "" && $out ["databaseName"] != $out ["fullTitle"]) {
            $out ["fullTitle"] = $out ["databaseName"] . " > " . $out ["fullTitle"];
        }
        $out ["page"] = $this->page;
        $out ["entries"] = $entries;
        $out ["entriesCount"] = count($entries);
        $out = array_replace($out, $this->addPagination($currentPage));
        if (!is_null($request->get("complete")) || $complete) {
            $out ["c"] = $this->getCompleteArray();
        }

        $out = array_replace($out, $this->addSortFilter($currentPage));
        $out["filters"] = $this->getFiltersArray($dbContext);

        $params = [];
        $params['db'] = $this->database;
        $out["abouturl"] = $this->getRoute(PageId::ROUTE_ABOUT, $params);
        $out["customizeurl"] = $this->getRoute(PageId::ROUTE_CUSTOMIZE, $params);

        if ($this->page == PageId::ABOUT) {
            /** @var PageAbout $currentPage */
            $out ["fullhtml"] = $currentPage->getContent();
        }

        $out ["homeurl"] = $this->getHomeUrl($out["baseurl"]);
        // @todo add link to parent(s) for hierarchical series, tags etc.
        $out ["parenturl"] = $this->getParentUrl($currentPage, $out["filters"], $out["homeurl"]);
        $out ["hierarchy"] = $this->getHierarchy($currentPage, $extraParams);
        $out ["extra"] = $currentPage->extra;
        if (!empty($currentPage->extra['series'])) {
            $out ["extra"]["series"] = $this->getSeries($currentPage, $extraParams);
        }
        $out ["assets"] = $this->getPath($this->config('assets'));
        // $out ["templates"] = $this->getPath('templates');
        $out ["download"] = $this->getDownloadLinks($currentPage, $qid);

        /** @phpstan-ignore-next-line */
        if (Database::KEEP_STATS) {
            $out ["dbstats"] = Database::getDbStatistics();
        }
        $out ["locale"] = $this->request->locale();
        $out ["template"] = $this->request->template();

        return $out;
    }

    /**
     * Summary of setRequest
     * @param Request $request
     * @return void
     */
    public function setRequest($request)
    {
        parent::setRequest($request);
        $this->database = $request->database();
        // Adapt handler based on $request e.g. for rest api
        $this->setHandler($request->getHandler());
        // Use the configured home page if needed - call after parent::setRequest()
        $this->homepage = PageId::getHomePage($this->getConfig());
        $this->page = $request->get("page", $this->homepage);
    }
}
