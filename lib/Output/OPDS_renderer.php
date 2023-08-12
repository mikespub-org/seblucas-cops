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
        $xml->text(Config::get('icon'));
        $xml->endElement();
        $xml->startElement("Url");
        $xml->writeAttribute("type", 'application/atom+xml');
        $urlparam = "?query={searchTerms}";
        $urlparam = Format::addDatabaseParam($urlparam, $database);
        $urlparam = str_replace("%7B", "{", $urlparam);
        $urlparam = str_replace("%7D", "}", $urlparam);
        $xml->writeAttribute("template", Config::get('full_url') . self::$endpoint . $urlparam);
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
        $database = $request->get('db');
        $this->getXmlStream()->startDocument('1.0', 'UTF-8');
        $this->getXmlStream()->startElement("feed");
        $this->getXmlStream()->writeAttribute("xmlns", "http://www.w3.org/2005/Atom");
        $this->getXmlStream()->writeAttribute("xmlns:xhtml", "http://www.w3.org/1999/xhtml");
        $this->getXmlStream()->writeAttribute("xmlns:opds", "http://opds-spec.org/2010/catalog");
        $this->getXmlStream()->writeAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
        $this->getXmlStream()->writeAttribute("xmlns:dcterms", "http://purl.org/dc/terms/");
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
            if (!is_null($request->get('db'))) {
                $idPage = str_replace("cops:", "cops:" . $request->get('db') . ":", $idPage);
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
        $link = new LinkNavigation("?" . $request->query(), "self");
        $this->renderLink($link);
        $urlparam = "?";
        $urlparam = Format::addDatabaseParam($urlparam, $database);
        if (Config::get('generate_invalid_opds_stream') == 0 || preg_match("/(MantanoReader|FBReader)/", $request->agent())) {
            // Good and compliant way of handling search
            $urlparam = Format::addURLParam($urlparam, "page", Page::OPENSEARCH);
            $link = new Link(self::$endpoint . $urlparam, "application/opensearchdescription+xml", "search", "Search here");
        } else {
            // Bad way, will be removed when OPDS client are fixed
            $urlparam = Format::addURLParam($urlparam, "query", "{searchTerms}");
            $urlparam = str_replace("%7B", "{", $urlparam);
            $urlparam = str_replace("%7D", "}", $urlparam);
            $link = new Link(Config::get('full_url') . self::$endpoint . $urlparam, "application/atom+xml", "search", "Search here");
        }
        $this->renderLink($link);
        if ($page->containsBook() && !is_null(Config::get('books_filter')) && count(Config::get('books_filter')) > 0) {
            $Urlfilter = $request->get("tag", "");
            foreach (Config::get('books_filter') as $lib => $filter) {
                $link = new LinkFacet("?" . Format::addURLParam($request->query(), "tag", $filter), $lib, localize("tagword.title"), $filter == $Urlfilter, $database);
                $this->renderLink($link);
            }
        }
    }

    private function endXmlDocument()
    {
        $this->getXmlStream()->endElement();
        $this->getXmlStream()->endDocument();
        return $this->getXmlStream()->outputMemory(true);
    }

    private function renderLink($link)
    {
        $this->getXmlStream()->startElement("link");
        $this->getXmlStream()->writeAttribute("href", $link->hrefXhtml(self::$endpoint));
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
        }
        $this->getXmlStream()->endElement();
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
        foreach ($entry->linkArray as $link) {
            $this->renderLink($link);
        }

        if (get_class($entry) != EntryBook::class) {
            return;
        }

        foreach ($entry->book->getAuthors() as $author) {
            $this->getXmlStream()->startElement("author");
            $this->getXmlStream()->startElement("name");
            $this->getXmlStream()->text($author->name);
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("uri");
            $this->getXmlStream()->text(self::$endpoint . $author->getUri());
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
        $this->startXmlDocument($page, $request);
        if ($page->isPaginated()) {
            $this->getXmlStream()->startElement("opensearch:totalResults");
            $this->getXmlStream()->text($page->totalNumber);
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("opensearch:itemsPerPage");
            $this->getXmlStream()->text(Config::get('max_item_per_page'));
            $this->getXmlStream()->endElement();
            $this->getXmlStream()->startElement("opensearch:startIndex");
            $this->getXmlStream()->text(($page->n - 1) * Config::get('max_item_per_page') + 1);
            $this->getXmlStream()->endElement();
            $prevLink = $page->getPrevLink();
            $nextLink = $page->getNextLink();
            if (!is_null($prevLink)) {
                $this->renderLink($prevLink);
            }
            if (!is_null($nextLink)) {
                $this->renderLink($nextLink);
            }
        }
        foreach ($page->entryArray as $entry) {
            $this->getXmlStream()->startElement("entry");
            $this->renderEntry($entry);
            $this->getXmlStream()->endElement();
        }
        return $this->endXmlDocument();
    }
}
