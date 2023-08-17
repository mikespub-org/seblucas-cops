<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use Kiwilan\Opds\Opds;
use Kiwilan\Opds\OpdsConfig;
use Kiwilan\Opds\OpdsVersionEnum;
use Kiwilan\Opds\Entries\OpdsEntry;
use Kiwilan\Opds\Entries\OpdsEntryBook;
use Kiwilan\Opds\Entries\OpdsEntryBookAuthor;
use SebLucas\Cops\Input\Config as CopsConfig;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Model\Entry as CopsEntry;
use SebLucas\Cops\Model\EntryBook as CopsEntryBook;
use DateTime;

class KiwilanOPDS
{
    public static string $endpoint = "opds.php";
    public static OpdsVersionEnum $version = OpdsVersionEnum::v1Dot2;
    /** @var DateTime|null */
    private $updated = null;

    /**
     * Summary of getUpdatedTime
     * @return DateTime
     */
    private function getUpdatedTime()
    {
        if (is_null($this->updated)) {
            $this->updated = new DateTime();
        }
        return $this->updated;
    }

    /**
     * Summary of getOpdsConfig
     * @return OpdsConfig
     */
    private function getOpdsConfig()
    {
        return new OpdsConfig(
            name: 'Calibre',  // CopsConfig::get('title_default')
            author: CopsConfig::get('author_name') ?: 'Sébastien Lucas',
            authorUrl: CopsConfig::get('author_uri') ?: 'http://blog.slucas.fr',
            iconUrl: CopsConfig::get('icon'),
            startUrl: self::$endpoint,
            // @todo php-opds uses this to identify search (not page=9) and adds '?q=' without checking for existing ? params
            //searchUrl: self::$endpoint . '?page=8',
            searchUrl: self::$endpoint . '/search',
            searchQuery: 'query',  // 'q' by default for php-opds
            updated: $this->getUpdatedTime(),
        );
    }

    /**
     * Summary of getOpdsEntryBook
     * @param CopsEntryBook $entry
     * @return OpdsEntryBook
     */
    private function getOpdsEntryBook($entry)
    {
        $authors = [];
        foreach ($entry->book->getAuthors() as $author) {
            $opdsEntryAuthor = new OpdsEntryBookAuthor(
                name: $author->name,
                uri: self::$endpoint . $author->getUri(),
            );
            array_push($authors, $opdsEntryAuthor);
        }
        $categories = [];
        foreach ($entry->book->getTags() as $category) {
            array_push($categories, $category->name);
        }
        $published = null;
        if ($entry->book->getPubDate() != "") {
            $published = new DateTime($entry->book->getPubDate());
        }
        $download = null;
        $data = $entry->book->getDataFormat('EPUB');
        if ($data) {
            $download = $data->getHtmlLink();
        }
        $serie = $entry->book->getSerie();
        if ($serie) {
            $serie = $serie->name;
        }
        $opdsEntry = new OpdsEntryBook(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(self::$endpoint),
            summary: OpdsEntryBook::handleContent($entry->content),
            content: $entry->content,
            media: $entry->getImage(self::$endpoint),
            updated: new DateTime($entry->getUpdatedTime()),
            download: $download,
            mediaThumbnail: $entry->getThumbnail(self::$endpoint),
            categories: $categories,
            authors: $authors,
            published: $published,
            // Element "volume" not allowed here; expected the element end-tag, element "author", "category", "contributor", "link", "rights" or "source" or an element from another namespace
            //volume: $entry->book->seriesIndex,
            serie: $serie,
            language: $entry->book->getLanguages(),
        );

        return $opdsEntry;
    }

    /**
     * Summary of getOpdsEntry
     * @param CopsEntry $entry
     * @return OpdsEntry
     */
    private function getOpdsEntry($entry)
    {
        $opdsEntry = new OpdsEntry(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(self::$endpoint),
            content: $entry->content,
            media: $entry->getThumbnail(self::$endpoint),
            //updated: $entry->getUpdatedTime(),
            updated: $this->getUpdatedTime(),
        );

        return $opdsEntry;
    }

    /**
     * Summary of getOpenSearch
     * @param CopsRequest $request
     * @return string
     */
    public function getOpenSearch($request)
    {
        $opds = Opds::make(
            config: $this->getOpdsConfig(),
            feeds: [], // OpdsEntry[]|OpdsEntryBook[]
            title: 'Search',
            //url: self::$endpoint . '?page=8', // Can be null to be set automatically
            url: self::$endpoint . '/search', // Can be null to be set automatically
            version: self::$version, // OPDS version
            //asString: false, // Output as string
            //isSearch: false, // Is search feed
        );

        return $opds->response(true);
    }

    /**
     * Summary of render
     * @param mixed $page
     * @param CopsRequest $request
     * @return string
     */
    public function render($page, $request)
    {
        $title = $page->title;
        $feeds = [];
        foreach ($page->entryArray as $entry) {
            if ($entry instanceof CopsEntryBook) {
                array_push($feeds, $this->getOpdsEntryBook($entry));
            } else {
                array_push($feeds, $this->getOpdsEntry($entry));
            }
        }
        $url = null;

        $opds = Opds::make(
            config: $this->getOpdsConfig(),
            feeds: $feeds, // OpdsEntry[]|OpdsEntryBook[]
            title: $title,
            url: $url, // Can be null to be set automatically
            version: self::$version, // OPDS version
            //asString: false, // Output as string
            //isSearch: false, // Is search feed
        );

        return $opds->response(true);
    }
}
