<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\Link;
use SebLucas\Cops\Model\LinkFacet;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\Page;
use XMLWriter;

class OPDSRenderer
{
    public static $endpoint = Config::ENDPOINT["feed"];
    private $xmlStream = null;
    private $updated = null;
    /** @var Request */
    protected $request;

    private function getUpdatedTime()
    {
        if (is_null($this->updated)) {
            $this->updated = time();
        }
        return date(DATE_ATOM, $this->updated);
    }

    private function getXmlStream()
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
        global $config;
        $database = $request->get('db');
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
        $xml->text($config['cops_icon']);
        $xml->endElement();
        $xml->startElement("Url");
        $xml->writeAttribute("type", 'application/atom+xml');
        $urlparam = "?query={searchTerms}";
        $urlparam = Format::addDatabaseParam($urlparam, $database);
        $urlparam = str_replace("%7B", "{", $urlparam);
        $urlparam = str_replace("%7D", "}", $urlparam);
        $xml->writeAttribute("template", $config['cops_full_url'] . self::$endpoint . $urlparam);
        $xml->endElement();
        $xml->startElement("Query");
        $xml->writeAttribute("role", "example");
        $xml->writeAttribute("searchTerms", "robot");
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory(true);
    }

    private function startXmlDocument($page, $request)
    {
        global $config;
        $database = $request->get('db');
        self::getXmlStream()->startDocument('1.0', 'UTF-8');
        self::getXmlStream()->startElement("feed");
        self::getXmlStream()->writeAttribute("xmlns", "http://www.w3.org/2005/Atom");
        self::getXmlStream()->writeAttribute("xmlns:xhtml", "http://www.w3.org/1999/xhtml");
        self::getXmlStream()->writeAttribute("xmlns:opds", "http://opds-spec.org/2010/catalog");
        self::getXmlStream()->writeAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
        self::getXmlStream()->writeAttribute("xmlns:dcterms", "http://purl.org/dc/terms/");
        self::getXmlStream()->startElement("title");
        self::getXmlStream()->text($page->title);
        self::getXmlStream()->endElement();
        if ($page->subtitle != "") {
            self::getXmlStream()->startElement("subtitle");
            self::getXmlStream()->text($page->subtitle);
            self::getXmlStream()->endElement();
        }
        self::getXmlStream()->startElement("id");
        if ($page->idPage) {
            $idPage = $page->idPage;
            if (!is_null($request->get('db'))) {
                $idPage = str_replace("cops:", "cops:" . $request->get('db') . ":", $idPage);
            }
            self::getXmlStream()->text($idPage);
        } else {
            self::getXmlStream()->text($request->uri());
        }
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("updated");
        self::getXmlStream()->text(self::getUpdatedTime());
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("icon");
        self::getXmlStream()->text($page->favicon);
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("author");
        self::getXmlStream()->startElement("name");
        self::getXmlStream()->text($page->authorName);
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("uri");
        self::getXmlStream()->text($page->authorUri);
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("email");
        self::getXmlStream()->text($page->authorEmail);
        self::getXmlStream()->endElement();
        self::getXmlStream()->endElement();
        $link = new LinkNavigation("", "start", "Home");
        self::renderLink($link);
        $link = new LinkNavigation("?" . $request->query(), "self");
        self::renderLink($link);
        $urlparam = "?";
        $urlparam = Format::addDatabaseParam($urlparam, $database);
        if ($config['cops_generate_invalid_opds_stream'] == 0 || preg_match("/(MantanoReader|FBReader)/", $request->agent())) {
            // Good and compliant way of handling search
            $urlparam = Format::addURLParam($urlparam, "page", Page::OPENSEARCH);
            $link = new Link(self::$endpoint . $urlparam, "application/opensearchdescription+xml", "search", "Search here");
        } else {
            // Bad way, will be removed when OPDS client are fixed
            $urlparam = Format::addURLParam($urlparam, "query", "{searchTerms}");
            $urlparam = str_replace("%7B", "{", $urlparam);
            $urlparam = str_replace("%7D", "}", $urlparam);
            $link = new Link($config['cops_full_url'] . self::$endpoint . $urlparam, "application/atom+xml", "search", "Search here");
        }
        self::renderLink($link);
        if ($page->containsBook() && !is_null($config['cops_books_filter']) && count($config['cops_books_filter']) > 0) {
            $Urlfilter = $request->get("tag", "");
            foreach ($config['cops_books_filter'] as $lib => $filter) {
                $link = new LinkFacet("?" . Format::addURLParam($request->query(), "tag", $filter), $lib, localize("tagword.title"), $filter == $Urlfilter, $database);
                self::renderLink($link);
            }
        }
    }

    private function endXmlDocument()
    {
        self::getXmlStream()->endElement();
        self::getXmlStream()->endDocument();
        return self::getXmlStream()->outputMemory(true);
    }

    private function renderLink($link)
    {
        self::getXmlStream()->startElement("link");
        self::getXmlStream()->writeAttribute("href", $link->href);
        self::getXmlStream()->writeAttribute("type", $link->type);
        if (!is_null($link->rel)) {
            self::getXmlStream()->writeAttribute("rel", $link->rel);
        }
        if (!is_null($link->title)) {
            self::getXmlStream()->writeAttribute("title", $link->title);
        }
        if (!is_null($link->facetGroup)) {
            self::getXmlStream()->writeAttribute("opds:facetGroup", $link->facetGroup);
        }
        if ($link->activeFacet) {
            self::getXmlStream()->writeAttribute("opds:activeFacet", "true");
        }
        self::getXmlStream()->endElement();
    }

    private function getPublicationDate($book)
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

    private function renderEntry($entry)
    {
        self::getXmlStream()->startElement("title");
        self::getXmlStream()->text($entry->title);
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("updated");
        self::getXmlStream()->text(self::getUpdatedTime());
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("id");
        self::getXmlStream()->text($entry->id);
        self::getXmlStream()->endElement();
        self::getXmlStream()->startElement("content");
        self::getXmlStream()->writeAttribute("type", $entry->contentType);
        self::getXmlStream()->text($entry->content);
        self::getXmlStream()->endElement();
        foreach ($entry->linkArray as $link) {
            self::renderLink($link);
        }

        if (get_class($entry) != EntryBook::class) {
            return;
        }

        foreach ($entry->book->getAuthors() as $author) {
            self::getXmlStream()->startElement("author");
            self::getXmlStream()->startElement("name");
            self::getXmlStream()->text($author->name);
            self::getXmlStream()->endElement();
            self::getXmlStream()->startElement("uri");
            self::getXmlStream()->text(self::$endpoint . $author->getUri());
            self::getXmlStream()->endElement();
            self::getXmlStream()->endElement();
        }
        foreach ($entry->book->getTags() as $category) {
            self::getXmlStream()->startElement("category");
            self::getXmlStream()->writeAttribute("term", $category->name);
            self::getXmlStream()->writeAttribute("label", $category->name);
            self::getXmlStream()->endElement();
        }
        if ($entry->book->getPubDate() != "") {
            self::getXmlStream()->startElement("dcterms:issued");
            self::getXmlStream()->text(self::getPublicationDate($entry->book));
            self::getXmlStream()->endElement();
            self::getXmlStream()->startElement("published");
            self::getXmlStream()->text(self::getPublicationDate($entry->book) . "T08:08:08Z");
            self::getXmlStream()->endElement();
        }

        $lang = $entry->book->getLanguages();
        if (!empty($lang)) {
            self::getXmlStream()->startElement("dcterms:language");
            self::getXmlStream()->text($lang);
            self::getXmlStream()->endElement();
        }
    }

    /**
     * Summary of render
     * @param mixed $page
     * @param Request $request
     * @return string
     */
    public function render($page, $request)
    {
        global $config;
        self::startXmlDocument($page, $request);
        if ($page->isPaginated()) {
            self::getXmlStream()->startElement("opensearch:totalResults");
            self::getXmlStream()->text($page->totalNumber);
            self::getXmlStream()->endElement();
            self::getXmlStream()->startElement("opensearch:itemsPerPage");
            self::getXmlStream()->text($config['cops_max_item_per_page']);
            self::getXmlStream()->endElement();
            self::getXmlStream()->startElement("opensearch:startIndex");
            self::getXmlStream()->text(($page->n - 1) * $config['cops_max_item_per_page'] + 1);
            self::getXmlStream()->endElement();
            $prevLink = $page->getPrevLink();
            $nextLink = $page->getNextLink();
            if (!is_null($prevLink)) {
                self::renderLink($prevLink);
            }
            if (!is_null($nextLink)) {
                self::renderLink($nextLink);
            }
        }
        foreach ($page->entryArray as $entry) {
            self::getXmlStream()->startElement("entry");
            self::renderEntry($entry);
            self::getXmlStream()->endElement();
        }
        return self::endXmlDocument();
    }
}
