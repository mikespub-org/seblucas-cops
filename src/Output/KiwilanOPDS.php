<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use Kiwilan\Opds\Opds;
use Kiwilan\Opds\OpdsConfig;
use Kiwilan\Opds\OpdsResponse;
use Kiwilan\Opds\Engine\Paginate\OpdsPaginate;
use Kiwilan\Opds\Entries\OpdsEntryBook;
use Kiwilan\Opds\Entries\OpdsEntryBookAuthor;
use Kiwilan\Opds\Entries\OpdsEntryNavigation;
use Kiwilan\Opds\Enums\OpdsVersionEnum;
use SebLucas\Cops\Handlers\OpdsHandler;
use SebLucas\Cops\Input\Config as CopsConfig;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Model\Entry as CopsEntry;
use SebLucas\Cops\Model\EntryBook as CopsEntryBook;
use SebLucas\Cops\Pages\Page as CopsPage;
use DateTime;

class KiwilanOPDS
{
    public const ROUTE_FEED = OpdsHandler::HANDLER;
    public const ROUTE_SEARCH = OpdsHandler::SEARCH;

    /** @var class-string */
    public static $handler = OpdsHandler::class;

    public static OpdsVersionEnum $version = OpdsVersionEnum::v2Dot0;
    /** @var ?DateTime */
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
            iconUrl: CopsConfig::get(name: 'icon'),
            startUrl: self::$handler::route(self::ROUTE_FEED),
            // @todo php-opds uses this to identify search (not page=9) and adds '?q=' without checking for existing ? params
            searchUrl: self::$handler::route(self::ROUTE_SEARCH),
            //searchQuery: 'query',  // 'q' by default for php-opds
            updated: $this->getUpdatedTime(),
            maxItemsPerPage: CopsConfig::get('max_item_per_page'),
            forceJson: true,
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
            $author->setHandler($entry->book->getHandler());
            $opdsEntryAuthor = new OpdsEntryBookAuthor(
                name: $author->name,
                uri: $author->getUri(),
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
        $publisher = $entry->book->getPublisher();
        if ($publisher) {
            $publisher = $publisher->name;
        }
        $opdsEntry = new OpdsEntryBook(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(),
            summary: OpdsEntryNavigation::handleContent($entry->content),
            content: $entry->content,
            media: $entry->getImage(),
            updated: new DateTime($entry->getUpdatedTime()),
            download: $download,
            mediaThumbnail: $entry->getThumbnail(),
            categories: $categories,
            authors: $authors,
            published: $published,
            // Element "volume" not allowed here; expected the element end-tag, element "author", "category", "contributor", "link", "rights" or "source" or an element from another namespace
            volume: $entry->book->seriesIndex,  // @todo support float 1.5
            serie: $serie,
            language: $entry->book->getLanguages(),
            //isbn: $entry->book->uuid,
            identifier: $entry->id,
            publisher: $publisher,
        );

        return $opdsEntry;
    }

    /**
     * Summary of getOpdsEntry
     * @param CopsEntry $entry
     * @return OpdsEntryNavigation
     */
    private function getOpdsEntry($entry)
    {
        $opdsEntry = new OpdsEntryNavigation(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(),
            summary: $entry->content,
            media: $entry->getThumbnail(),
            relation: $entry->getRelation(),
            //updated: $entry->getUpdatedTime(),
            updated: $this->getUpdatedTime(),
        );
        if ($entry->numberOfElement) {
            $opdsEntry->properties([ "numberOfItems" => $entry->numberOfElement ]);
        }

        return $opdsEntry;
    }

    /**
     * Summary of getOpenSearch
     * @param CopsRequest $request
     * @return OpdsResponse
     */
    public function getOpenSearch($request)
    {
        $opds = Opds::make($this->getOpdsConfig())
            ->title('Search')
            ->url(self::$handler::route(self::ROUTE_SEARCH))
            ->isSearch()
            ->feeds([])
            ->get();
        return $opds->getResponse();
    }

    /**
     * Summary of render
     * @param CopsPage $page
     * @param CopsRequest $request
     * @return OpdsResponse
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
        // with same _route param here
        $url = self::$handler::link($request->urlParams);
        if ($page->isPaginated()) {
            $prevLink = $page->getPrevLink();
            if (!is_null($prevLink)) {
                $first = $page->getFirstLink()->hrefXhtml();
                $previous = $prevLink->hrefXhtml();
            } else {
                $first = null;
                $previous = null;
            }
            $nextLink = $page->getNextLink();
            if (!is_null($nextLink)) {
                $next = $nextLink->hrefXhtml();
                $last = $page->getLastLink()->hrefXhtml();
            } else {
                $next = null;
                $last = null;
            }
            //$out ["maxPage"] = $page->getMaxPage();
            //'numberOfItems' => $page->totalNumber,
            //'itemsPerPage' => $page->getNumberPerPage(),
            //'currentPage' => $page->n,
            // 'opensearch:startIndex' => (($page->n - 1) * $page->getNumberPerPage() + 1)

            $opds = Opds::make($this->getOpdsConfig())
            ->title($title)
            ->url($url)
            ->feeds($feeds)
            ->paginate(new OpdsPaginate(
                currentPage: $page->n,
                totalItems: $page->totalNumber,
                firstUrl: $first,
                lastUrl: $last,
                previousUrl: $previous,
                nextUrl: $next,
            )) // will generate pagination based on `OpdsPaginate` object
            ->get();

        } else {
            $opds = Opds::make($this->getOpdsConfig())
            ->title($title)
            ->url($url)
            ->feeds($feeds)
            ->get();
        }
        return $opds->getResponse();
    }
}
