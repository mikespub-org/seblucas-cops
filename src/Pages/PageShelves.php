<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Database\DatabaseException;
use SebLucas\Cops\Model\EntryBook;

class PageShelves extends Page
{
    protected string $template = 'shelves.html';

    public ?BookShelf $shelf = null;
    public ?string $currentShelf = null;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // Echo the requested sort back (sorted/sortedBy/sortedDir in the JSON)
        // so direct shelf links (?sort=title+asc) work
        $this->sorted = $this->request->getSorted("sort");

        $this->getEntries();
        $this->idPage = PageId::ALL_SHELVES_ID;
        $this->title = $this->localize("shelves.title");
    }

    /**
     * Check if server shelves feature is enabled
     */
    public function isServerShelvesEnabled(): bool
    {
        return BookShelf::isServerShelvesEnabled($this->getConfig());
    }

    /**
     * Get or initialize BookShelf domain model
     */
    public function getShelfModel(): BookShelf
    {
        if ($this->shelf === null) {
            $this->shelf = new BookShelf($this->request, $this->getDbContext());
        }
        return $this->shelf;
    }

    /**
     * Set BookShelf model (useful for testing)
     */
    public function setShelfModel(BookShelf $shelf): void
    {
        $this->shelf = $shelf;
    }

    /**
     * Only keys that can be sorted client-side from localStorage shelf entries.
     * title/author map 1:1, timestamp maps to insertion order ("added").
     * @return array<string, string>
     */
    public function getSortOptions()
    {
        $options = parent::getSortOptions();
        $supported = ['title', 'author', 'timestamp'];
        return array_intersect_key($options, array_flip($supported));
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        if (!$this->isServerShelvesEnabled()) {
            // Client-side shelves are stored in localStorage; server returns an empty list
            $this->entryArray = [];
            return;
        }

        $shelf = $this->getShelfModel();
        $shelf->load();

        $requestedShelf = $this->request->get(BookShelf::URL_PARAM);
        if (!empty($requestedShelf)) {
            $resolved = $shelf->resolveShelfName($requestedShelf);
            if ($resolved) {
                $this->currentShelf = $resolved;
            }
        }
        if (empty($this->currentShelf)) {
            $this->currentShelf = $shelf->getActiveShelfName();
        }

        $groupedIds = $shelf->getIdListGrouped($this->currentShelf);
        if (empty($groupedIds)) {
            $this->entryArray = [];
            $this->totalNumber = 0;
            return;
        }

        $shelfBooks = $shelf->getBooksInShelf($this->currentShelf);
        $orderMap = [];
        foreach ($shelfBooks as $idx => $b) {
            $key = BookShelf::getBookKey($b);
            $orderMap[$key] = $idx;
        }

        $allEntries = [];
        foreach ($groupedIds as $dbId => $ids) {
            $dbContext = new DatabaseContext((int) $dbId, $this->getConfig());
            $booklist = new BookList($this->request, $dbContext);
            try {
                [$entries, ] = $booklist->getBooksByIdList(array_map('intval', $ids));
                foreach ($entries as $entry) {
                    $allEntries[] = $entry;
                }
            } catch (DatabaseException) {
                continue;
            }
        }

        $this->sortEntries($allEntries, $orderMap);
        $this->totalNumber = count($allEntries);

        $numberPerPage = $this->getNumberPerPage();
        if ($numberPerPage > 0 && $this->totalNumber > $numberPerPage) {
            $start = max(0, ($this->n - 1) * $numberPerPage);
            $this->entryArray = array_slice($allEntries, $start, $numberPerPage);
        } else {
            $this->entryArray = $allEntries;
        }
    }

    /**
     * Sort loaded entries according to $this->sorted preference
     * @param array<EntryBook> $entries
     * @param array<string, int> $orderMap
     */
    protected function sortEntries(array &$entries, array $orderMap): void
    {
        $sort = strtolower(trim($this->sorted ?? 'sort'));
        $descending = str_ends_with($sort, 'desc');

        if (str_starts_with($sort, 'title')) {
            usort($entries, fn($a, $b) => $this->compareTitles($a, $b, $descending));
        } elseif (str_starts_with($sort, 'author')) {
            usort($entries, fn($a, $b) => $this->compareAuthors($a, $b, $descending));
        } else {
            usort($entries, fn($a, $b) => $this->compareShelfOrder($a, $b, $orderMap, $descending));
        }
    }

    private function compareTitles(EntryBook $first, EntryBook $second, bool $descending): int
    {
        $comparison = strcasecmp($first->title ?? '', $second->title ?? '');
        return $descending ? -$comparison : $comparison;
    }

    private function compareAuthors(EntryBook $first, EntryBook $second, bool $descending): int
    {
        $authorFirst = $first->book->getAuthorsName() ?: '';
        $authorSecond = $second->book->getAuthorsName() ?: '';
        $comparison = strcasecmp($authorFirst, $authorSecond);
        if ($comparison === 0) {
            $comparison = strcasecmp($first->title ?? '', $second->title ?? '');
        }
        return $descending ? -$comparison : $comparison;
    }

    /** @param array<string, int> $orderMap */
    private function compareShelfOrder(EntryBook $first, EntryBook $second, array $orderMap, bool $descending): int
    {
        $firstKey = BookShelf::getBookKey(['id' => $first->book->id, 'db' => $first->book->getDatabaseId()]);
        $secondKey = BookShelf::getBookKey(['id' => $second->book->id, 'db' => $second->book->getDatabaseId()]);
        $comparison = ($orderMap[$firstKey] ?? 0) <=> ($orderMap[$secondKey] ?? 0);
        return $descending ? -$comparison : $comparison;
    }

    /**
     * Get list of all shelves and their counts
     * @return array<string, int>
     */
    public function getShelvesList(): array
    {
        if (!$this->isServerShelvesEnabled()) {
            return [];
        }
        return $this->getShelfModel()->getShelves();
    }

    /**
     * Summary of containsBook
     * @return bool
     */
    public function containsBook()
    {
        // add download and sortoptions in JsonRenderer::getJson()
        return true;
    }

    /**
     * Map of JS-only camelCase shelf* keys (used via getI18n() in shelves.js)
     * to their dot-notation translation keys - see tools/i18n-inventory.csv
     * @return array<string, string>
     */
    public static function getPageI18nMap()
    {
        return [
            'shelfAddTitle' => 'shelf.addTitle',
            'shelfBooksInTitle' => 'shelf.booksInTitle',
            'shelfCreatePrompt' => 'shelf.createPrompt',
            'shelfDeleteConfirm' => 'shelf.deleteConfirm',
            'shelfDeleteError' => 'shelf.deleteError',
            'shelfDeleteTitle' => 'shelf.deleteTitle',
            'shelfDownloadDisabled' => 'shelf.downloadDisabled',
            'shelfDownloadEmpty' => 'shelf.downloadEmpty',
            'shelfDownloadStarted' => 'shelf.downloadStarted',
            'shelfDownloadZipTitle' => 'shelf.downloadZipTitle',
            'shelfEmptyTitle' => 'shelf.emptyTitle',
            'shelfExistsError' => 'shelf.existsError',
            'shelfExportError' => 'shelf.exportError',
            'shelfExportTitle' => 'shelf.exportTitle',
            'shelfImportCancel' => 'shelf.importCancel',
            'shelfImportChoicePrompt' => 'shelf.importChoicePrompt',
            'shelfImportChoose' => 'shelf.importChoose',
            'shelfImportConfirm' => 'shelf.importConfirm',
            'shelfImportError' => 'shelf.importError',
            'shelfImportMerge' => 'shelf.importMerge',
            'shelfImportNew' => 'shelf.importNew',
            'shelfImportReplace' => 'shelf.importReplace',
            'shelfImportSuccess' => 'shelf.importSuccess',
            'shelfImportTitle' => 'shelf.importTitle',
            'shelfNewTitle' => 'shelf.newTitle',
            'shelfRemoveAlt' => 'shelf.removeAlt',
            'shelfRemoveTitle' => 'shelf.removeTitle',
            'shelfRenameError' => 'shelf.renameError',
            'shelfRenamePrompt' => 'shelf.renamePrompt',
            'shelfRenameTitle' => 'shelf.renameTitle',
            'shelfShareTitle' => 'shelf.shareTitle',
            'shelfShareTooLarge' => 'shelf.shareTooLarge',
            'shelfUnknownAuthor' => 'shelf.unknownAuthor',
            'shelvesTitle' => 'shelves.title',
        ];
    }

    /**
     * Return all JS-only camelCase shelf* names with their localized translations.
     * For future use by page-specific i18n (e.g. JsonRenderer::getJson() without complete=1).
     * @return array<string, string>
     */
    public function getPageI18n()
    {
        $out = [];
        foreach (self::getPageI18nMap() as $camelKey => $dotKey) {
            $out[$camelKey] = $this->localize($dotKey);
        }
        return $out;
    }
}
