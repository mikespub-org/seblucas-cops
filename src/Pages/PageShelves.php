<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

class PageShelves extends Page
{
    protected string $template = 'shelves.html';

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = PageId::ALL_SHELVES_ID;
        $this->title = $this->localize("shelves.title");
        // Echo the requested sort back (sorted/sortedBy/sortedDir in the JSON)
        // so direct shelf links (?sort=title+asc) work: the client shelves page
        // reads them and sorts its localStorage books accordingly
        $this->sorted = $this->request->getSorted("sort");
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
        // Client-side shelves are stored in localStorage; server returns an empty list
        $this->entryArray = [];
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
