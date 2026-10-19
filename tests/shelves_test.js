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
        console: console,
        JSON: JSON,
        Object: Object,
        String: String
    };

    // minimal getI18n() like util.js (fallback only)
    sandbox.getI18n = function (key, fallback) {
        return fallback;
    };

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

console.log('');
console.log(count + ' checks, ' + failures + ' failure(s)');
if (failures > 0) {
    process.exit(1);
}
console.log('ALL_CHECKS_PASSED');
