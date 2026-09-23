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
 *   node tests/shelf_modal_test.js
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

    var fakeModalContent = options.modalContent || { innerHTML: '' };

    // stub document with just what the scripts use
    var documentStub = {
        getElementById: function (id) {
            if (id === 'shelf-modal-content') {
                return fakeModalContent;
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

    sandbox.modalContent = fakeModalContent;
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
// 5. Modal rendering: style alignment with twigged template
// ---------------------------------------------------------------------------

(function testModalRendering() {
    var env = setup({ initialShelves: fixtureShelves });

    env.ShelfManager.refreshShelfModal();
    var html = env.modalContent.innerHTML;

    check('modal header rendered', html.indexOf('modal-header') > -1);
    check('toolbar in panel panel-default + panel-body', html.indexOf('panel panel-default') > -1 && html.indexOf('panel-body') > -1);
    check('badge count in dropdown and heading', html.split('badge pull-right').length - 1 >= 2);
    check('h4 section heading', html.indexOf('<h4>') > -1);
    check('book cards use row d-flex grid', html.indexOf('row d-flex') > -1);
    check('book cards use same columns as booklist', html.indexOf('col-lg-2 col-sm-3 col-xs-6 books') > -1);
    check('covers use cover-image + img-responsive', html.indexOf('cover-image') > -1 && html.indexOf('img-responsive') > -1);
    check('metadata uses meta + ellipsis classes', html.indexOf('class="meta"') > -1 && html.indexOf('ellipsis') > -1);
    check('no list-group markup left', html.indexOf('list-group') === -1);
    check('no img-thumbnail markup left', html.indexOf('img-thumbnail') === -1);

    // XSS escaping
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
    xssEnv.ShelfManager.refreshShelfModal();
    var xssHtml = xssEnv.modalContent.innerHTML;
    check('book title is HTML-escaped', xssHtml.indexOf('&lt;script&gt;') > -1 && xssHtml.indexOf('<script>') === -1);
    check('book author is HTML-escaped', xssHtml.indexOf('Evil &amp; &lt;Co&gt;') > -1);

    // numeric seriesIndex no longer breaks escapeHtml
    check('numeric seriesIndex rendered', html.indexOf('Sherlock Holmes #2') > -1);

    // data attributes needed by bindModalEvents are present
    check('data-book-db attribute present', html.indexOf('data-book-db') > -1);
    check('data-book-id attribute present', html.indexOf('data-book-id') > -1);
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
