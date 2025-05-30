<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
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
use Kiwilan\Opds\Entries\OpdsEntryImage;
use Kiwilan\Opds\Entries\OpdsEntryNavigation;
use Kiwilan\Opds\Enums\OpdsVersionEnum;
use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Handlers\OpdsHandler;
use SebLucas\Cops\Input\Config as CopsConfig;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Model\Entry as CopsEntry;
use SebLucas\Cops\Model\EntryBook as CopsEntryBook;
use SebLucas\Cops\Model\LinkImage as CopsLinkImage;
use SebLucas\Cops\Pages\Page as CopsPage;
use DateTime;

class KiwilanOPDS
{
    use HasRouteTrait;

    public const ROUTE_FEED = OpdsHandler::HANDLER;
    public const ROUTE_SEARCH = OpdsHandler::SEARCH;

    public OpdsVersionEnum $version = OpdsVersionEnum::v2Dot0;
    /** @var ?DateTime */
    private $updated = null;

    public function __construct()
    {
        $this->setHandler(OpdsHandler::class);
    }

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
            authorUrl: CopsConfig::get('author_uri') ?: 'https://blog.slucas.fr',
            iconUrl: CopsConfig::get('icon'),
            startUrl: $this->getRoute(self::ROUTE_FEED),
            // @todo php-opds uses this to identify search (not page=query) and adds '?q=' without checking for existing ? params
            searchUrl: $this->getRoute(self::ROUTE_SEARCH),
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
        $image = $entry->getImageLink();
        $media = isset($image) ? $this->getOpdsEntryImage($image) : null;
        $thumbnail = $entry->getThumbnailLink();
        $mediaThumbnail = isset($thumbnail) ? $this->getOpdsEntryImage($thumbnail) : null;

        $opdsEntry = new OpdsEntryBook(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(),
            summary: OpdsEntryNavigation::handleContent($entry->content),
            content: $entry->content,
            media: $media,
            updated: new DateTime($entry->getUpdatedTime()),
            download: $download,
            mediaThumbnail: $mediaThumbnail,
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
     * Summary of getOpdsEntryImage
     * @param CopsLinkImage $link
     * @return OpdsEntryImage
     */
    private function getOpdsEntryImage($link)
    {
        $opdsEntry = new OpdsEntryImage(
            uri: $link->getUri(),
            path: $link->filepath,
            type: $link->type,
            height: $link->getHeight(),
            width: $link->getWidth(),
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
        $thumbnail = $entry->getThumbnailLink();
        $media = isset($thumbnail) ? $this->getOpdsEntryImage($thumbnail) : null;

        $opdsEntry = new OpdsEntryNavigation(
            id: $entry->id,
            title: $entry->title,
            route: $entry->getNavLink(),
            summary: $entry->content,
            media: $media,
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
            ->url($this->getRoute(self::ROUTE_SEARCH))
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
        $url = $this->getLink($request->urlParams);
        if ($page->isPaginated()) {
            $prevLink = $page->getPrevLink();
            if (!is_null($prevLink)) {
                $first = $page->getFirstLink()->getUri();
                $previous = $prevLink->getUri();
            } else {
                $first = null;
                $previous = null;
            }
            $nextLink = $page->getNextLink();
            if (!is_null($nextLink)) {
                $next = $nextLink->getUri();
                $last = $page->getLastLink()->getUri();
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
