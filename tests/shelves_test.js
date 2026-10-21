#!/usr/bin/env node
/**
 * Functional test for the client-side virtual shelves feature
 *
 * Tests the shared core library (resources/shelves/shelves.js) and the
 * twigged template UI extensions (templates/twigged/scripts/shelves.js)
 * without a browser, by stubbing document / window / jQuery / localStorage.
 *
 * This test does not require PHP or a webserver - run it with node:
 *
 *   node tests/shelves_test.js
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

'use strict';

var fs = require('fs');
var path = require('path');

var baseDir = path.dirname(__dirname);
var coreScript = path.join(baseDir, 'resources', 'shelves', 'shelves.js');
var twiggedScript = path.join(baseDir, 'templates', 'twigged', 'scripts', 'shelves.js');

/** Simple localStorage stub backed by an in-memory object */
function createLocalStorage() {
    var store = {};
    return {
        getItem: function (key) {
            return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
        },
        setItem: function (key, value) {
            store[key] = String(value);
        },
        removeItem: function (key) {
            delete store[key];
        }
    };
}

/** Minimal test runner */
var failures = 0;
var count = 0;

function check(name, condition) {
    count++;
    if (condition) {
        console.log('PASS ' + name);
    } else {
        failures++;
        console.log('FAIL ' + name);
    }
}

function checkEquals(name, expected, actual) {
    count++;
    if (expected === actual) {
        console.log('PASS ' + name);
    } else {
        failures++;
        console.log('FAIL ' + name + ' - expected: ' + JSON.stringify(expected) + ', actual: ' + JSON.stringify(actual));
    }
}

/**
 * Set up a fresh environment and load both scripts
 * @param {object} options with optional initialShelves (object) and modalContent (fake element)
 * @return {object} with context (ShelfManager, document, etc.)
 */
function setup(options) {
    options = options || {};

    var storage = createLocalStorage();
    if (options.initialShelves) {
        storage.setItem('cops_shelves', JSON.stringify(options.initialShelves));
    }

    var fakePageContent = options.pageContent || { innerHTML: '' };

    // stub document with just what the scripts use
    var documentStub = {
        getElementById: function (id) {
            if (id === 'shelf-page-content') {
                return fakePageContent;
            }
            if (id === 'shelf-status-message') {
                return { style: {}, textContent: '', hideTimer: null, parentNode: null };
            }
            return null;
        },
        querySelectorAll: function () {
            return [];
        },
        createElement: function () {
            return {};
        }
    };

    // stub jQuery: $(document).ready() runs the callback, $(...).off().on() is a no-op chain
    var jQueryStub = function (arg) {
        if (arg === documentStub) {
            return {
                ready: function (fn) {
                    fn();
                }
            };
        }
        return {
            off: function () {
                return { on: function () {} };
            },
            modal: function () {}
        };
    };

    var sandbox = {
        localStorage: storage,
        document: documentStub,
        $: jQueryStub,
        window: { location: { href: '' } },
        setTimeout: setTimeout,
        clearTimeout: clearTimeout,
        console: console,
        JSON: JSON,
        Object: Object,
        String: String
    };

    // minimal getI18n() like util.js (fallback only)
    sandbox.getI18n = function (key, fallback) {
        return fallback;
    };

    // simple str_format like util.js: replaces {0}, {1}, etc.
    sandbox.str_format = function (fmt) {
        var args = Array.prototype.slice.call(arguments, 1);
        return fmt.replace(/{(\d+)}/g, function(match, index) {
            return typeof args[index] !== 'undefined' ? args[index] : match;
        });
    };

    sandbox.currentData = { baseurl: '', download: true };

    // evaluate scripts in the sandbox context
    var vm = require('vm');
    var context = vm.createContext(sandbox);
    vm.runInContext(fs.readFileSync(coreScript, 'utf8'), context, { filename: coreScript });
    vm.runInContext(fs.readFileSync(twiggedScript, 'utf8'), context, { filename: twiggedScript });

    sandbox.pageContent = fakePageContent;
    sandbox.storage = storage;
    return sandbox;
}

// ---------------------------------------------------------------------------
// Test fixtures
// ---------------------------------------------------------------------------

var fixtureShelves = {
    activeShelf: 'Favorites',
    shelves: {
        'Favorites': [
            {
                db: '',
                id: '17',
                title: "Alice's Adventures in Wonderland",
                author: 'Lewis Carroll',
                thumbnailurl: '/cops/index.php/thumb/html/0/17',
                detailurl: '/cops/index.php/book/0/17',
                seriesName: null,
                seriesIndex: null
            },
            {
                db: '1',
                id: '17',
                title: 'The Sign of the Four',
                author: 'Arthur Conan Doyle',
                thumbnailurl: '/cops/index.php/thumb/html/1/17',
                detailurl: '/cops/index.php/book/1/17',
                seriesName: 'Sherlock Holmes',
                seriesIndex: 2
            }
        ],
        'To Read': []
    }
};

// ---------------------------------------------------------------------------
// 1. Book identity: db normalization and getBookKey
// ---------------------------------------------------------------------------

(function testBookIdentity() {
    var env = setup();

    checkEquals('getBookKey with undefined db', ':17', env.ShelfManager.getBookKey({ id: '17' }));
    checkEquals('getBookKey with empty db', ':17', env.ShelfManager.getBookKey({ db: '', id: '17' }));
    checkEquals('getBookKey with db 0', ':17', env.ShelfManager.getBookKey({ db: 0, id: '17' }));
    checkEquals("getBookKey with db '0'", ':17', env.ShelfManager.getBookKey({ db: '0', id: '17' }));
    checkEquals('getBookKey with db null', ':17', env.ShelfManager.getBookKey({ db: null, id: '17' }));
    checkEquals("getBookKey with db '1'", '1:17', env.ShelfManager.getBookKey({ db: '1', id: '17' }));
    checkEquals('getBookKey with numeric db', '2:17', env.ShelfManager.getBookKey({ db: 2, id: '17' }));
})();

// ---------------------------------------------------------------------------
// 2. localStorage persistence: load, save, toggleBook
// ---------------------------------------------------------------------------

(function testPersistenceAndToggle() {
    var env = setup();

    // empty storage -> defaults
    checkEquals('default active shelf', 'Favorites', env.ShelfManager.getActiveShelfName());
    checkEquals('default shelf names', 1, env.ShelfManager.getShelfNames().length);

    // toggle add
    checkEquals('toggleBook adds book', true, env.ShelfManager.toggleBook({ db: '', id: '18', title: 'Through the Looking Glass' }));
    checkEquals('book stored in localStorage', true, env.storage.getItem('cops_shelves').indexOf('Through the Looking Glass') > -1);

    // identity is (db, id): same id on another db is NOT in shelf
    checkEquals('isInShelf matches undefined db', true, env.ShelfManager.isInShelf({ db: undefined, id: '18' }));
    checkEquals('isInShelf matches db 0', true, env.ShelfManager.isInShelf({ db: 0, id: '18' }));
    checkEquals('isInShelf rejects other db', false, env.ShelfManager.isInShelf({ db: '1', id: '18' }));

    // toggle remove
    checkEquals('toggleBook removes book', false, env.ShelfManager.toggleBook({ db: '0', id: '18' }));
    checkEquals('shelf empty after remove', 0, env.ShelfManager.getBooks().length);
})();

// ---------------------------------------------------------------------------
// 2b. Attribute sanitizing: literal "null"/"undefined" strings treated as empty
// ---------------------------------------------------------------------------

(function testAttributeSanitizing() {
    var env = setup();

    function fakeButton(attrs) {
        return {
            getAttribute: function (name) {
                return Object.prototype.hasOwnProperty.call(attrs, name) ? attrs[name] : null;
            }
        };
    }

    // simulate a button whose thumbnail rendered as the literal string "null"
    // (as doT templates do for null values without an || '' fallback)
    var nullBtn = fakeButton({
        'data-book-db': 'null',
        'data-book-id': '19',
        'data-book-title': 'Book With Null Thumbnail',
        'data-book-author': 'undefined',
        'data-book-thumbnail': 'null',
        'data-book-detailurl': 'null'
    });
    env.ShelfManager.handleToggle(nullBtn);

    var books = env.ShelfManager.getBooks();
    checkEquals('one book stored from null-attribute button', 1, books.length);
    checkEquals('db "null" sanitized to empty', '', books[0].db);
    checkEquals('author "undefined" sanitized to empty', '', books[0].author);
    checkEquals('thumbnailurl "null" sanitized to empty', '', books[0].thumbnailurl);
    checkEquals('detailurl "null" sanitized to empty', '', books[0].detailurl);
    checkEquals('title kept as-is', 'Book With Null Thumbnail', books[0].title);

    // normal values still pass through unchanged
    var okBtn = fakeButton({
        'data-book-db': '',
        'data-book-id': '20',
        'data-book-title': 'Normal Book',
        'data-book-author': 'An Author',
        'data-book-thumbnail': '/thumb/20',
        'data-book-detailurl': '/book/20'
    });
    env.ShelfManager.handleToggle(okBtn);
    var after = env.ShelfManager.getBooks();
    checkEquals('normal thumbnailurl kept as-is', '/thumb/20', after[1].thumbnailurl);
})();

// ---------------------------------------------------------------------------
// 3. Multi-database shelves: addBook / removeBook
// ---------------------------------------------------------------------------

(function testMultiDatabase() {
    var env = setup();

    env.ShelfManager.createShelf('Multi');

    // same id, different databases = different books
    checkEquals('addBook db default', true, env.ShelfManager.addBook('Multi', { db: '', id: '17', title: 'Alice' }));
    checkEquals('addBook db 1 (same id)', true, env.ShelfManager.addBook('Multi', { db: '1', id: '17', title: 'The Sign of the Four' }));
    checkEquals('duplicate add rejected', false, env.ShelfManager.addBook('Multi', { db: 0, id: '17' }));

    var books = env.ShelfManager.getBooksInShelf('Multi');
    checkEquals('both books stored', 2, books.length);

    // remove only the db '1' copy
    checkEquals('removeBook db 1', true, env.ShelfManager.removeBook('Multi', { db: '1', id: '17' }));
    books = env.ShelfManager.getBooksInShelf('Multi');
    checkEquals('only default db book left', 1, books.length);
    checkEquals('remaining book is default db', ':17', env.ShelfManager.getBookKey(books[0]));
})();

// ---------------------------------------------------------------------------
// 4. Shelf management: create / rename / delete
// ---------------------------------------------------------------------------

(function testShelfManagement() {
    var env = setup();

    checkEquals('createShelf', true, env.ShelfManager.createShelf('Later'));
    checkEquals('duplicate createShelf rejected', false, env.ShelfManager.createShelf('Later'));

    checkEquals('renameShelf', true, env.ShelfManager.renameShelf('Later', 'Someday'));
    checkEquals('renamed shelf exists', true, env.ShelfManager.getShelfNames().indexOf('Someday') > -1);

    checkEquals('deleteShelf', true, env.ShelfManager.deleteShelf('Someday'));
    checkEquals('cannot delete last shelf', false, env.ShelfManager.deleteShelf('Favorites'));
    checkEquals('deleteShelf missing', false, env.ShelfManager.deleteShelf('Nonexistent'));
})();

// ---------------------------------------------------------------------------
// 5. Export/Import: JSON round-trip and validation
// ---------------------------------------------------------------------------

(function testExportImport() {
    var env = setup();
    var sm = env.ShelfManager;

    // Add books to To Read shelf and make it active
    sm.createShelf('To Read');
    sm.addBook('To Read', { db: '', id: '17', title: 'Book One', author: 'Author A' });
    sm.addBook('To Read', { db: '1', id: '42', title: 'Book Two', author: 'Author B' });
    sm.setActiveShelf('To Read');

    var exported = sm.exportData();
    check('exportData returns a string', typeof exported === 'string');

    try {
        var parsed = JSON.parse(exported);
        check('exportData is valid JSON', true);
        check('exported contains shelves object', typeof parsed.shelves === 'object');
        check('exported contains activeShelf', typeof parsed.activeShelf === 'string');
        check('exported activeShelf is To Read', parsed.activeShelf === 'To Read');
        // Only the active shelf should be exported
        check('exported has only To Read shelf', Object.keys(parsed.shelves).length === 1);
        check('exported has To Read shelf', parsed.shelves['To Read'] && parsed.shelves['To Read'].length === 2);
        check('exported does not have Favorites shelf', parsed.shelves['Favorites'] === undefined);
    } catch (e) {
        check('exportData is valid JSON', false);
        check('exported contains shelves object', false);
        check('exported contains activeShelf', false);
        check('exported activeShelf is To Read', false);
        check('exported has only To Read shelf', false);
        check('exported has To Read shelf', false);
        check('exported does not have Favorites shelf', false);
    }

    // Import into a fresh environment - replace active shelf
    var env2 = setup();
    var sm2 = env2.ShelfManager;
    var imported = sm2.importData(exported, 'replace');
    check('importData accepts exported JSON in replace mode', imported === true);
    check('import replaced active shelf has 2 books', sm2.getBooks().length === 2);
    check('import replaced active shelf book title', sm2.getBooks()[0] && sm2.getBooks()[0].title === 'Book One');

    // Import into a fresh environment - create new "Imported" shelf
    var env3 = setup();
    var sm3 = env3.ShelfManager;
    var importedNew = sm3.importData(exported, 'new');
    check('importData accepts exported JSON in new mode', importedNew === true);
    check('import new - Imported shelf exists', sm3.getShelfNames().indexOf('Imported') > -1);
    check('import new - Imported shelf has 2 books', sm3.getBooksInShelf('Imported').length === 2);

    // Import into a fresh environment - merge into active shelf
    var env4 = setup();
    var sm4 = env4.ShelfManager;
    sm4.addBook('Favorites', { db: '', id: '99', title: 'Existing Book', author: 'Existing Author' });
    var importedMerge = sm4.importData(exported, 'merge');
    check('importData accepts exported JSON in merge mode', importedMerge === true);
    var mergedBooks = sm4.getBooks();
    check('import merge - active shelf has existing + imported books', mergedBooks.length === 3);
    check('import merge - existing book kept', mergedBooks.some(function(b) { return b.id === '99'; }));
    check('import merge - imported book added', mergedBooks.some(function(b) { return b.id === '17'; }));

    // Import merge with duplicate book (should not duplicate)
    var env5 = setup();
    var sm5 = env5.ShelfManager;
    sm5.addBook('Favorites', { db: '', id: '17', title: 'Already There', author: 'Someone' });
    sm5.importData(exported, 'merge');
    var mergeBooks = sm5.getBooks();
    var count17 = 0;
    for (var m = 0; m < mergeBooks.length; m++) {
        if (mergeBooks[m].id === '17') { count17++; }
    }
    check('import merge - no duplicate books', count17 === 1);

    // Import invalid JSON
    check('importData rejects invalid JSON', sm3.importData('not json') === false);
    check('importData rejects empty string', sm3.importData('') === false);

    // Import missing shelves structure
    check('importData rejects missing shelves', sm3.importData(JSON.stringify({ foo: 'bar' })) === false);
})();

// ---------------------------------------------------------------------------
// 6. Page rendering: dedicated shelf page content
// ---------------------------------------------------------------------------

(function testPageRendering() {
    var env = setup({ initialShelves: fixtureShelves });

    env.ShelfManager.renderShelfPage();
    var html = env.pageContent.innerHTML;

    check('page toolbar in panel panel-default + panel-body', html.indexOf('panel panel-default') > -1 && html.indexOf('panel-body') > -1);
    check('page badge count in dropdown and heading', html.split('badge pull-right').length - 1 >= 2);
    check('page h4 section heading', html.indexOf('<h4>') > -1);
    check('page book cards use row d-flex grid', html.indexOf('row d-flex') > -1);
    check('page book cards use same columns as booklist', html.indexOf('col-lg-2 col-sm-3 col-xs-6 books') > -1);
    check('page covers use cover-image + img-responsive', html.indexOf('cover-image') > -1 && html.indexOf('img-responsive') > -1);
    check('page metadata uses meta + ellipsis classes', html.indexOf('class="meta"') > -1 && html.indexOf('ellipsis') > -1);
    check('page remove button present', html.indexOf('shelf-remove-btn') > -1);
    check('page does not include modal-header or modal-footer', html.indexOf('modal-header') === -1 && html.indexOf('modal-footer') === -1);

    // XSS escaping via page content
    var xssShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                {
                    db: '',
                    id: '9',
                    title: '<script>alert("x")</script>',
                    author: 'Evil & <Co>',
                    thumbnailurl: '/thumb',
                    detailurl: '/detail'
                }
            ]
        }
    };
    var xssEnv = setup({ initialShelves: xssShelves });
    xssEnv.ShelfManager.renderShelfPage();
    var xssHtml = xssEnv.pageContent.innerHTML;
    check('book title is HTML-escaped', xssHtml.indexOf('&lt;script&gt;') > -1 && xssHtml.indexOf('<script>') === -1);
    check('book author is HTML-escaped', xssHtml.indexOf('Evil &amp; &lt;Co&gt;') > -1);

    // numeric seriesIndex no longer breaks escapeHtml
    check('numeric seriesIndex rendered', html.indexOf('Sherlock Holmes #2') > -1);

    // data attributes needed by bindPageEvents are present
    check('data-book-db attribute present', html.indexOf('data-book-db') > -1);
    check('data-book-id attribute present', html.indexOf('data-book-id') > -1);
})();

// ---------------------------------------------------------------------------
// 5c. Empty thumbnail handling: no  with empty src
// ---------------------------------------------------------------------------

(function testEmptyThumbnail() {
    var noThumbShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                {
                    db: '',
                    id: '42',
                    title: 'Book Without Cover',
                    author: 'An Author',
                    thumbnailurl: '',
                    detailurl: '/book/0/42'
                }
            ]
        }
    };
    var env = setup({ initialShelves: noThumbShelves });
    env.ShelfManager.renderShelfPage();
    var html = env.pageContent.innerHTML;

    // no img tag with empty src (which would trigger a browser request to current page)
    check('no img with empty src when thumbnailurl missing', html.indexOf('<img') === -1 || html.indexOf('src=""') === -1);
    // book metadata still rendered
    check('book title still rendered', html.indexOf('Book Without Cover') > -1);
    check('book author still rendered', html.indexOf('An Author') > -1);
    // remove button still present
    check('remove button present for book without cover', html.indexOf('shelf-remove-btn') > -1);
})();

// ---------------------------------------------------------------------------
// 6. Error handling: corrupted / missing localStorage data
// ---------------------------------------------------------------------------

(function testErrorHandling() {
    var env = setup();
    env.storage.setItem('cops_shelves', 'this is not json');
    checkEquals('corrupted json falls back to defaults', 'Favorites', env.ShelfManager.getActiveShelfName());

    env = setup();
    env.storage.setItem('cops_shelves', JSON.stringify({ foo: 'bar' }));
    checkEquals('invalid structure falls back to defaults', 'Favorites', env.ShelfManager.getActiveShelfName());

    env = setup();
    env.storage.setItem('cops_shelves', JSON.stringify({ shelves: { A: [], B: [{ db: '', id: '1' }] }, activeShelf: 'Deleted' }));
    checkEquals('missing activeShelf falls back to first shelf', 'A', env.ShelfManager.getActiveShelfName());
})();

// ---------------------------------------------------------------------------
// 7. Download ZIP
// ---------------------------------------------------------------------------

(function testDownloadZip() {
    var singleDbShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { db: '', id: '17', title: 'Book 1', author: 'Author 1', thumbnailurl: '', detailurl: '/b/17' },
                { db: '', id: '19', title: 'Book 2', author: 'Author 2', thumbnailurl: '', detailurl: '/b/19' }
            ]
        }
    };
    var env = setup({ initialShelves: singleDbShelves });

    var url = env.ShelfManager.getShelfZipUrl('any');
    check('single-db zip url has idlist', url.indexOf('idlist[') > -1);
    check('single-db zip url has book ids', url.indexOf('17%2C19') > -1 || url.indexOf('19%2C17') > -1);
    check('single-db zip url has format', url.indexOf('.zip') > -1);
    check('single-db zip url is not null', url !== null);

    // multi-database shelf -> nested idlist
    var multiDbShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { db: '', id: '17', title: 'Book 1', author: 'Author 1', thumbnailurl: '', detailurl: '/b/17' },
                { db: '1', id: '19', title: 'Book 2', author: 'Author 2', thumbnailurl: '', detailurl: '/b/19' }
            ]
        }
    };
    var env2 = setup({ initialShelves: multiDbShelves });
    env2.currentData = { baseurl: '', download: true };
    var url2 = env2.ShelfManager.getShelfZipUrl('epub');
    check('multi-db zip url has idlist[0]', url2.indexOf('idlist[0]=') > -1);
    check('multi-db zip url has idlist[1]', url2.indexOf('idlist[1]=') > -1);
    check('multi-db zip url has format epub', url2.indexOf('epub.zip') > -1);

    // empty shelf -> null
    var emptyShelves = {
        activeShelf: 'Favorites',
        shelves: { 'Favorites': [] }
    };
    var env3 = setup({ initialShelves: emptyShelves });
    checkEquals('empty shelf zip url is null', null, env3.ShelfManager.getShelfZipUrl('any'));

    // downloadShelf with download disabled -> status message
    var env4 = setup({ initialShelves: singleDbShelves });
    env4.currentData = { baseurl: '', download: false };
    env4.window.location.href = '';
    env4.ShelfManager.downloadShelf('any');
    check('download disabled does not navigate', env4.window.location.href === '');

    // downloadShelf with empty shelf -> status message, no navigation
    var env5 = setup({ initialShelves: emptyShelves });
    env5.currentData = { baseurl: '', download: true };
    env5.window.location.href = '';
    env5.ShelfManager.downloadShelf('any');
    check('empty shelf does not navigate', env5.window.location.href === '');

    // downloadShelf navigates to zip url
    var env6 = setup({ initialShelves: singleDbShelves });
    env6.currentData = { baseurl: '', download: true };
    env6.window.location.href = '';
    env6.ShelfManager.downloadShelf('any');
    check('download navigates to zip url', env6.window.location.href.indexOf('zipper/books/any.zip') > -1);
    check('download navigates with idlist', env6.window.location.href.indexOf('idlist[') > -1);
})();

// ---------------------------------------------------------------------------
// 8. Sorting
// ---------------------------------------------------------------------------

(function testSorting() {
    var shelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { id: '1', db: '', title: 'Zebra Tales', author: 'Smith' },
                { id: '2', db: '', title: 'apple pie', author: 'Adams' },
                { id: '3', db: '', title: 'Mango', author: 'Smith' },
                { id: '4', db: '', title: 'Banana', author: null }
            ]
        }
    };
    var env = setup({ initialShelves: shelves });
    var books = shelves.shelves.Favorites;

    // default sort key keeps insertion order
    checkEquals('default sort key', 'added+asc', env.ShelfManager.getSortKey());

    // parseSortKey validation
    var parsed = env.ShelfManager.parseSortKey('title+desc');
    checkEquals('parse valid name', 'title', parsed.name);
    checkEquals('parse valid dir', 'desc', parsed.dir);
    parsed = env.ShelfManager.parseSortKey('bogus+sideways');
    checkEquals('unknown name falls back to added', 'added', parsed.name);
    checkEquals('unknown dir falls back to asc', 'asc', parsed.dir);
    parsed = env.ShelfManager.parseSortKey('author');
    checkEquals('missing dir defaults to asc', 'asc', parsed.dir);

    // title asc / desc
    var byTitle = env.ShelfManager.sortBooks(books, 'title+asc');
    checkEquals('title asc first', 'apple pie', byTitle[0].title);
    checkEquals('title asc last', 'Zebra Tales', byTitle[3].title);
    var byTitleDesc = env.ShelfManager.sortBooks(books, 'title+desc');
    checkEquals('title desc first', 'Zebra Tales', byTitleDesc[0].title);
    check('sortBooks returns new array', byTitle !== books);

    // author asc: null author first, then Adams, equal authors tie-break on title
    var byAuthor = env.ShelfManager.sortBooks(books, 'author+asc');
    checkEquals('author asc null first', '4', byAuthor[0].id);
    checkEquals('author asc adams second', '2', byAuthor[1].id);
    checkEquals('author asc smith tie-break 1', '3', byAuthor[2].id);
    checkEquals('author asc smith tie-break 2', '1', byAuthor[3].id);

    // added = insertion order, desc reverses it
    var byAdded = env.ShelfManager.sortBooks(books, 'added+asc');
    checkEquals('added asc keeps order', '1', byAdded[0].id);
    checkEquals('added asc keeps order 2', '4', byAdded[3].id);
    var byAddedDesc = env.ShelfManager.sortBooks(books, 'added+desc');
    checkEquals('added desc reverses', '4', byAddedDesc[0].id);

    // setSortKey persists and resets to first page
    env.ShelfManager.setPage(3);
    env.ShelfManager.setSortKey('title+asc');
    checkEquals('sort key persisted', 'title+asc', JSON.parse(env.localStorage.getItem('cops_shelves')).sortKey);
    checkEquals('page reset on sort change', 1, env.ShelfManager.getPagedBooks().page);
})();

// ---------------------------------------------------------------------------
// 9. Pagination
// ---------------------------------------------------------------------------

(function testPaginationConfig() {
    var env = setup({});

    checkEquals('no c.config means no page size', 0, env.ShelfManager.getPageSize());

    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: '10' } } };
    checkEquals('string config parsed', 10, env.ShelfManager.getPageSize());

    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 25 } } };
    checkEquals('numeric config kept', 25, env.ShelfManager.getPageSize());

    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: -1 } } };
    checkEquals('-1 means no pagination', 0, env.ShelfManager.getPageSize());

    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 0 } } };
    checkEquals('0 means no pagination', 0, env.ShelfManager.getPageSize());

    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 'junk' } } };
    checkEquals('invalid config means no pagination', 0, env.ShelfManager.getPageSize());

    checkEquals('page count for empty shelf', 1, env.ShelfManager.getPageCount(0, 10));
    checkEquals('page count exact fit', 2, env.ShelfManager.getPageCount(20, 10));
    checkEquals('page count with remainder', 3, env.ShelfManager.getPageCount(21, 10));
    checkEquals('page count without page size', 1, env.ShelfManager.getPageCount(100, 0));
})();

(function testPagedBooks() {
    var many = [];
    for (var i = 1; i <= 25; i++) {
        many.push({ id: String(i), db: '', title: 'Book ' + i, author: 'Author ' + i });
    }
    var env = setup({
        initialShelves: { activeShelf: 'A', shelves: { 'A': many, 'B': [] } }
    });
    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 10 } } };

    var p1 = env.ShelfManager.getPagedBooks();
    checkEquals('page count for 25 books', 3, p1.maxPage);
    checkEquals('total is shelf size', 25, p1.total);
    checkEquals('page 1 size', 10, p1.books.length);
    checkEquals('page 1 first id', '1', p1.books[0].id);

    env.ShelfManager.setPage(3);
    var p3 = env.ShelfManager.getPagedBooks();
    checkEquals('page 3 size', 5, p3.books.length);
    checkEquals('page 3 first id', '21', p3.books[0].id);

    // clamping
    env.ShelfManager.setPage(99);
    checkEquals('page clamped to maxPage', 3, env.ShelfManager.getPagedBooks().page);
    env.ShelfManager.setPage(-5);
    checkEquals('page clamped to 1', 1, env.ShelfManager.getPagedBooks().page);

    // shelf switch resets page
    env.ShelfManager.setPage(2);
    env.ShelfManager.setActiveShelf('B');
    checkEquals('shelf switch resets page', 1, env.ShelfManager.getPagedBooks().page);

    // no page size -> everything on a single page (switch back to shelf A first)
    env.ShelfManager.setActiveShelf('A');
    env.currentData = { baseurl: '', download: true };
    var all = env.ShelfManager.getPagedBooks();
    checkEquals('no page size returns all books', 25, all.books.length);
    checkEquals('no page size maxPage', 1, all.maxPage);
})();

(function testSortAndPagerRendering() {
    var many = [];
    for (var i = 1; i <= 25; i++) {
        many.push({ id: String(i), db: '', title: 'Book ' + i, author: 'Author ' + i });
    }
    var env = setup({
        initialShelves: { activeShelf: 'Favorites', shelves: { 'Favorites': many } }
    });
    env.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 10 } } };

    env.ShelfManager.renderShelfPage();
    var html = env.pageContent.innerHTML;

    check('sortlinks rendered', html.indexOf('id="shelf-sortlinks"') > -1);
    check('title sort link', html.indexOf('data-sort="title"') > -1);
    check('author sort link', html.indexOf('data-sort="author"') > -1);
    check('added sort link', html.indexOf('data-sort="added"') > -1);
    check('added active by default', html.indexOf('<li class="active"><a href="javascript:void(0);" data-sort="added"') > -1);
    check('default direction in tooltip', html.indexOf('title="Added (Asc)"') > -1);
    check('pager rendered', html.indexOf('id="shelf-pager"') > -1);
    check('pager shows 1 / 3', html.indexOf(' 1 / 3 ') > -1);
    check('prev disabled on first page', html.indexOf('<li class="disabled"><a href="javascript:void(0);" data-page="prev"') > -1);
    check('first link hidden on 3 pages', html.indexOf('data-page="first"') === -1);
    check('heading badge shows total', html.indexOf('<span class="badge pull-right">25</span>') > -1);

    // change sort -> active state and direction tooltip move
    env.ShelfManager.setSortKey('title+desc');
    env.ShelfManager.renderShelfPage();
    var sortedHtml = env.pageContent.innerHTML;
    check('title becomes active', sortedHtml.indexOf('<li class="active"><a href="javascript:void(0);" data-sort="title"') > -1);
    check('desc direction in tooltip', sortedHtml.indexOf('title="Title (Desc)"') > -1);

    // last page: 5 books, next disabled
    env.ShelfManager.setPage(3);
    env.ShelfManager.renderShelfPage();
    var lastHtml = env.pageContent.innerHTML;
    var removeCount = lastHtml.split('shelf-remove-btn').length - 1;
    checkEquals('last page renders remaining books', 5, removeCount);
    check('next disabled on last page', lastHtml.indexOf('<li class="disabled"><a href="javascript:void(0);" data-page="next"') > -1);

    // without page size: no pager, sortlinks still present, all books shown
    var env2 = setup({
        initialShelves: { activeShelf: 'Favorites', shelves: { 'Favorites': many } }
    });
    env2.currentData = { baseurl: '', download: true };
    env2.ShelfManager.renderShelfPage();
    var noPagerHtml = env2.pageContent.innerHTML;
    check('no pager without page size', noPagerHtml.indexOf('id="shelf-pager"') === -1);
    check('sortlinks without page size', noPagerHtml.indexOf('id="shelf-sortlinks"') > -1);
    checkEquals('all books on one page', 25, noPagerHtml.split('shelf-remove-btn').length - 1);

    // empty shelf: empty message, no pager
    var env3 = setup({
        initialShelves: { activeShelf: 'Favorites', shelves: { 'Favorites': [] } }
    });
    env3.currentData = { baseurl: '', download: true, c: { config: { max_item_per_page: 10 } } };
    env3.ShelfManager.renderShelfPage();
    var emptyHtml = env3.pageContent.innerHTML;
    check('empty message shown', emptyHtml.indexOf('This shelf is empty') > -1);
    check('no pager on empty shelf', emptyHtml.indexOf('id="shelf-pager"') === -1);
})();

(function testUrlSortAdoption() {
    var shelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { id: '1', db: '', title: 'Zebra Tales', author: 'Smith' },
                { id: '2', db: '', title: 'apple pie', author: 'Adams' }
            ]
        }
    };
    var env = setup({ initialShelves: shelves });

    // bare shelves visit: sortedBy="sort" echoed, ignored -> default
    env.currentData = { baseurl: '', download: true, sortedBy: 'sort', sortedDir: 'asc' };
    checkEquals('url sort key null for default', null, env.ShelfManager.getUrlSortKey());
    checkEquals('falls back to persisted default', 'added+asc', env.ShelfManager.getSortKey());
    checkEquals('nothing persisted on ignore', undefined, JSON.parse(env.localStorage.getItem('cops_shelves')).sortKey);

    // direct link ?sort=title+desc: adopted once, persisted, page reset
    env.ShelfManager.setPage(5);
    env.currentData = { baseurl: '', download: true, sortedBy: 'title', sortedDir: 'desc' };
    checkEquals('url sort key mapped', 'title+desc', env.ShelfManager.getUrlSortKey());
    checkEquals('direct link adopted', 'title+desc', env.ShelfManager.getSortKey());
    var adopted = JSON.parse(env.localStorage.getItem('cops_shelves')).sortKey;
    checkEquals('adopted sort persisted', 'title+desc', adopted);
    checkEquals('page reset on adoption', 1, env.ShelfManager.getPagedBooks().page);
    var first = env.ShelfManager.getSortedBooks();
    checkEquals('sorted by title desc', '2', first[1].id);

    // same URL on re-render: in-content state rules (adopted only once)
    env.ShelfManager.setSortKey('author+asc');
    checkEquals('click wins after adoption', 'author+asc', env.ShelfManager.getSortKey());

    // timestamp maps to added
    var env2 = setup({ initialShelves: shelves });
    env2.currentData = { baseurl: '', download: true, sortedBy: 'timestamp', sortedDir: 'desc' };
    checkEquals('timestamp maps to added', 'added+desc', env2.ShelfManager.getSortKey());

    // unsupported key (pubdate/rating) ignored
    var env3 = setup({ initialShelves: shelves });
    env3.currentData = { baseurl: '', download: true, sortedBy: 'rating', sortedDir: 'desc' };
    checkEquals('unsupported key returns null', null, env3.ShelfManager.getUrlSortKey());
    checkEquals('unsupported key ignored', 'added+asc', env3.ShelfManager.getSortKey());
})();

// ---------------------------------------------------------------------------
// 8. Shelf sharing URL
// ---------------------------------------------------------------------------

(function testShelfShareUrl() {
    var singleDbShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { db: '', id: '17', title: 'Book 1', author: 'Author 1', thumbnailurl: '', detailurl: '/b/17' },
                { db: '', id: '19', title: 'Book 2', author: 'Author 2', thumbnailurl: '', detailurl: '/b/19' }
            ]
        }
    };
    var env = setup({ initialShelves: singleDbShelves });
    env.currentData = { baseurl: '/cops', shelvesurl: '/cops/shelves', download: true };

    var url = env.ShelfManager.getShelfShareUrl();
    check('share url not null', url !== null);
    check('share url has idlist', url.indexOf('idlist[') > -1);
    check('share url has book ids', url.indexOf('17%2C19') > -1 || url.indexOf('19%2C17') > -1);
    check('share url has name', url.indexOf('name=') > -1);
    check('share url has share=1', url.indexOf('share=1') > -1);
    check('share url uses shelvesurl base', url.indexOf('/cops/shelves?') > -1);

    // multi-database shelf -> nested idlist
    var multiDbShelves = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': [
                { db: '', id: '17', title: 'Book 1', author: 'Author 1', thumbnailurl: '', detailurl: '/b/17' },
                { db: '1', id: '19', title: 'Book 2', author: 'Author 2', thumbnailurl: '', detailurl: '/b/19' }
            ]
        }
    };
    var env2 = setup({ initialShelves: multiDbShelves });
    env2.currentData = { baseurl: '/cops', shelvesurl: '/cops/shelves', download: true };
    var url2 = env2.ShelfManager.getShelfShareUrl();
    check('multi-db share url has idlist[0]', url2.indexOf('idlist[0]=') > -1);
    check('multi-db share url has idlist[1]', url2.indexOf('idlist[1]=') > -1);

    // empty shelf -> null
    var emptyShelves = { activeShelf: 'Favorites', shelves: { 'Favorites': [] } };
    var env3 = setup({ initialShelves: emptyShelves });
    checkEquals('empty shelf share url is null', null, env3.ShelfManager.getShelfShareUrl());
})();

// ---------------------------------------------------------------------------

console.log('');
console.log(count + ' checks, ' + failures + ' failure(s)');
if (failures > 0) {
    process.exit(1);
}
console.log('ALL_CHECKS_PASSED');
