<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkEntry;
use SebLucas\Cops\Model\LinkFacet;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;
use XMLWriter;

class OPDSRenderer
{
    public static string $endpoint = Config::ENDPOINT["feed"];
    /** @var ?XMLWriter */
    protected $xmlStream = null;
    /** @var ?int */
    protected $updated = null;
    /** @var Request */
    protected $request;

    /**
     * Summary of getUpdatedTime
     * @return string
     */
    protected function getUpdatedTime()
    {
        if (is_null($this->updated)) {
            $this->updated = time();
        }
        return date(DATE_ATOM, $this->updated);
    }

    /**
     * Summary of getXmlStream
     * @return XMLWriter
     */
    protected function getXmlStream()
    {
        if (is_null($this->xmlStream)) {
            $this->xmlStream = new XMLWriter();
            $this->xmlStream->openMemory();
            $this->xmlStream->setIndent(true);
        }
        return $this->xmlStream;
    }

    /**
     * Summary of getOpenSearch
     * @param Request $request
     * @return string
     */
    public function getOpenSearch($request)
    {
        $database = $request->database();
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement("OpenSearchDescription");
        $xml->writeAttribute("xmlns", "http://a9.com/-/spec/opensearch/1.1/");
        $xml->startElement("ShortName");
        $xml->text("My catalog");
        $xml->endElement();
        $xml->startElement("Description");
        $xml->text("Search for ebooks");
        $xml->endElement();
        $xml->startElement("InputEncoding");
        $xml->text("UTF-8");
        $xml->endElement();
        $xml->startElement("OutputEncoding");
        $xml->text("UTF-8");
        $xml->endElement();
        $xml->startElement("Image");
        $xml->writeAttribute("type", "image/x-icon");
        $xml->writeAttribute("width", "16");
        $xml->writeAttribute("height", "16");
        $xml->text(Config::get('icon'));
        $xml->endElement();
        $xml->startElement("Url");
        $xml->writeAttribute("type", 'application/atom+xml');
        $params = ["query" => "{searchTerms}", "db" => $database];
        $url = Route::url(static::$endpoint, null, $params);
        $url = str_replace("%7B", "{", $url);
        $url = str_replace("%7D", "}", $url);
        $xml->writeAttribute("template", $url);
        $xml->endElement();
        $xml->startElement("Query");
        $xml->writeAttribute("role", "example");
        $xml->writeAttribute("searchTerms", "robot");
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory(true);
    }

    /**
     * Summary of startXmlDocument
     * @param Page $page
     * @param Request $request
     * @return void
     */
    protected function startXmlDocument($page, $request)
    {
        $database = $request->database();
        $this->getXmlStream()->startDocument('1.0', 'UTF-8');
        $this->getXmlStream()->startElement("feed");
        $this->getXmlStream()->writeAttribute("xmlns", "http://www.w3.org/2005/Atom");
        $this->getXmlStream()->writeAttribute("xmlns:xhtml", "http://www.w3.org/1999/xhtml");
        $this->getXmlStream()->writeAttribute("xmlns:opds", "http://opds-spec.org/2010/catalog");
        $this->getXmlStream()->writeAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
        $this->getXmlStream()->writeAttribute("xmlns:dcterms", "http://purl.org/dc/terms/");
        $this->getXmlStream()->writeAttribute("xmlns:thr", "http://purl.org/syndication/thread/1.0");
        $this->getXmlStream()->startElement("title");
        $this->getXmlStream()->text($page->title);
        $this->getXmlStream()->endElement();
        if ($page->subtitle != "") {
            $this->getXmlStream()->startElement("subtitle");
            $this->getXmlStream()->text($page->subtitle);
            $this->getXmlStream()->endElement();
        }
        $this->getXmlStream()->startElement("id");
        if ($page->idPage) {
            $idPage = $page->idPage;
            if (!is_null($request->database())) {
                $idPage = str_replace("cops:", "cops:" . strval($request->database()) . ":", $idPage);
            }
            $this->getXmlStream()->text($idPage);
        } else {
            $this->getXmlStream()->text($request->uri());
        }
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("updated");
        $this->getXmlStream()->text($this->getUpdatedTime());
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("icon");
        $this->getXmlStream()->text($page->favicon);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("author");
        $this->getXmlStream()->startElement("name");
        $this->getXmlStream()->text($page->authorName);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("uri");
        $this->getXmlStream()->text($page->authorUri);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("email");
        $this->getXmlStream()->text($page->authorEmail);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->endElement();
        $link = new LinkNavigation("", "start", "Home");
        $this->renderLink($link);
        if ($page->containsBook()) {
            $link = new LinkFeed(Route::query($request->query()), "self");
        } else {
            $link = new LinkNavigation(Route::query($request->query()), "self");
        }
        $this->renderLink($link);
        $params = ["db" => $database];
        if (Config::get('generate_invalid_opds_stream') == 0 || preg_match("/(MantanoReader|FBReader)/", $request->agent())) {
            // Good and compliant way of handling search
            $url = Route::url(static::$endpoint, PageId::OPENSEARCH, $params);
            $link = new LinkEntry($url, "application/opensearchdescription+xml", "search", "Search here");
        } else {
            // Bad way, will be removed when OPDS client are fixed
            $params["query"] = "{searchTerms}";
            $url = Route::url(static::$endpoint, null, $params);
            $url = str_replace("%7B", "{", $url);
            $url = str_replace("%7D", "}", $url);
            $link = new LinkEntry($url, "application/atom+xml", "search", "Search here");
        }
        $this->renderLink($link);
        if ($page->containsBook() && !is_null(Config::get('books_filter')) && count(Config::get('books_filter')) > 0) {
            $Urlfilter = $request->get("tag", "");
            foreach (Config::get('books_filter') as $lib => $filter) {
                $link = new LinkFacet(Route::query($request->query(), ["tag" => $filter]), $lib, localize("tagword.title"), $filter == $Urlfilter, null, $database);
                $this->renderLink($link);
            }
        }
    }

    /**
     * Summary of endXmlDocument
     * @return string
     */
    protected function endXmlDocument()
    {
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->endDocument();
        return $this->getXmlStream()->outputMemory(true);
    }

    /**
     * Summary of renderLink
     * @param LinkEntry|LinkFeed $link
     * @param ?int $number
     * @return void
     */
    protected function renderLink($link, $number = null)
    {
        $this->getXmlStream()->startElement("link");
        $this->getXmlStream()->writeAttribute("href", $link->hrefXhtml(static::$endpoint));
        $this->getXmlStream()->writeAttribute("type", $link->type);
        if (!is_null($link->rel)) {
            $this->getXmlStream()->writeAttribute("rel", $link->rel);
        }
        if (!is_null($link->title)) {
            $this->getXmlStream()->writeAttribute("title", $link->title);
        }
        if ($link instanceof LinkFacet) {
            if (!is_null($link->facetGroup)) {
                $this->getXmlStream()->writeAttribute("opds:facetGroup", $link->facetGroup);
            }
            if ($link->activeFacet) {
                $this->getXmlStream()->writeAttribute("opds:activeFacet", "true");
            }
            if (!empty($link->threadCount)) {
                $this->getXmlStream()->writeAttribute("thr:count", (string) $link->threadCount);
            }
        } elseif ($link instanceof LinkFeed && !empty($number)) {
            $this->getXmlStream()->writeAttribute("thr:count", (string) $number);
        }
        $this->getXmlStream()->endElement();
    }

    /**
     * Summary of getPublicationDate
     * @param Book $book
     * @return string
     */
    protected function getPublicationDate($book)
    {
        $dateYmd = substr($book->pubdate, 0, 10);
        $pubdate = \DateTime::createFromFormat('Y-m-d', $dateYmd);
        if ($pubdate === false ||
            $pubdate->format("Y") == "0101" ||
            $pubdate->format("Y") == "0100") {
            return "";
        }
        return $pubdate->format("Y-m-d");
    }

    /**
     * Summary of renderEntry
     * @param Entry|EntryBook $entry
     * @return void
     */
    protected function renderEntry($entry)
    {
        $this->getXmlStream()->startElement("title");
        $this->getXmlStream()->text($entry->title);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("updated");
        $this->getXmlStream()->text($this->getUpdatedTime());
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("id");
        $this->getXmlStream()->text($entry->id);
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->startElement("content");
        $this->getXmlStream()->writeAttribute("type", $entry->contentType);
        $this->getXmlStream()->text($entry->content);
        $this->getXmlStream()->endElement();

        if (get_class($entry) != EntryBook::class) {
            foreach ($entry->linkArray as $link) {
                $this->renderLink($link, $entry->numberOfElement);
            }
            return;
        }

        foreach ($entry->linkArray as $link) {
            $this->renderLink($link);
        }

        foreach ($entry->book->getAuthors() as $author) {
            $this->getXmlStream()->startElement("author");
            $this->getXmlStream()->startElement("name");
            $this->getXmlStream()->text($author->name);
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("uri");
            $this->getXmlStream()->text(static::$endpoint . $author->getUri());
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->endElement();
        }
        foreach ($entry->book->getTags() as $category) {
            $this->getXmlStream()->startElement("category");
            $this->getXmlStream()->writeAttribute("term", $category->name);
            $this->getXmlStream()->writeAttribute("label", $category->name);
            $this->getXmlStream()->endElement();
        }
        if ($entry->book->getPubDate() != "") {
            $this->getXmlStream()->startElement("dcterms:issued");
            $this->getXmlStream()->text($this->getPublicationDate($entry->book));
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("published");
            $this->getXmlStream()->text($this->getPublicationDate($entry->book) . "T08:08:08Z");
            $this->getXmlStream()->endElement();
        }

        $lang = $entry->book->getLanguages();
        if (!empty($lang)) {
            $this->getXmlStream()->startElement("dcterms:language");
            $this->getXmlStream()->text($lang);
            $this->getXmlStream()->endElement();
        }
    }

    /**
     * Summary of render
     * @param Page $page
     * @param Request $request
     * @return string
     */
    public function render($page, $request)
    {
        $database = $request->database();
        $this->startXmlDocument($page, $request);
        if ($page->isPaginated()) {
            $this->getXmlStream()->startElement("opensearch:totalResults");
            $this->getXmlStream()->text((string) $page->totalNumber);
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("opensearch:itemsPerPage");
            $this->getXmlStream()->text(Config::get('max_item_per_page'));
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("opensearch:startIndex");
            $this->getXmlStream()->text((string) (($page->n - 1) * Config::get('max_item_per_page') + 1));
            $this->getXmlStream()->endElement();
            $prevLink = $page->getPrevLink();
            $nextLink = $page->getNextLink();
            if (!is_null($prevLink)) {
                $this->renderLink($page->getFirstLink());
                $this->renderLink($prevLink);
            }
            if (!is_null($nextLink)) {
                $this->renderLink($nextLink);
                $this->renderLink($page->getLastLink());
            }
            // only show sorting when paginating
            if ($page->containsBook() && !empty(Config::get('opds_sort_links'))) {
                $sortUrl = Route::query($page->getCleanQuery(), ['sort' => null]) . "&sort={0}";
                $sortLabel = localize("sort.alternate");
                $sortParam = $request->get('sort');
                $sortOptions = $page->getSortOptions();
                // @todo we can't use really facetGroups here, or OPDS reader thinks we're drilling down :-()
                foreach ($sortOptions as $field => $title) {
                    $url = str_format($sortUrl, $field);
                    $link = new LinkFacet($url, $title, $sortLabel, $field == $sortParam, null, $database);
                    //$link = new LinkNavigation($url, 'http://opds-spec.org/sort/' . $field, $sortLabel . ' ' . $title);
                    //$link = new LinkFeed($url, 'http://opds-spec.org/sort/' . $field, $sortLabel . ' ' . $title);
                    $this->renderLink($link);
                }
            }
        }
        // always show filters even when not paginating
        if ($page->containsBook()) {
            $skipFilterUrl = [PageId::AUTHORS_FIRST_LETTER, PageId::ALL_BOOKS_LETTER, PageId::ALL_BOOKS_YEAR, PageId::ALL_RECENT_BOOKS, PageId::BOOK_DETAIL];
            if (!empty($request->getId()) && !empty(Config::get('opds_filter_links')) && !in_array($page, $skipFilterUrl)) {
                //$url = Route::query($page->getCleanQuery(), ['filter' => 1]);
                //$filterLabel = localize("cog.alternate");
                //$title = localize("links.title");
                //$link = new LinkFacet($url, $title, $filterLabel, false, null, $database);
                //$this->renderLink($link);
                // Note: facets are only shown if there are books available, so we need to get a filter page here
                $request->set('filter', 1);
                $filterPage = PageId::getPage($request->get('page'), $request);
                $filterPage->InitializeContent();
                $request->set('filter', null);
                $extraUri = $filterPage->filterUri;
                if ($request->get('sort')) {
                    $extraUri .= '&sort=' . $request->get('sort');
                }
                foreach ($filterPage->entryArray as $entry) {
                    if (empty($entry->className)) {
                        continue;
                    }
                    $group = strtolower($entry->className);
                    $group = localize($group . 's.title');
                    // @todo handle special case of OPDS not expecting filter while HTML does better
                    $url = str_replace('&filter=1', '', $entry->getNavLink('', $extraUri));
                    $link = new LinkFacet($url, $entry->title, $group, false, $entry->numberOfElement, $database);
                    $this->renderLink($link);
                }
            }
        }
        foreach ($page->entryArray as $entry) {
            if (!$entry->isValidForOPDS()) {
                continue;
            }
            $this->getXmlStream()->startElement("entry");
            $this->renderEntry($entry);
            $this->getXmlStream()->endElement();
        }
        return $this->endXmlDocument();
    }
}
