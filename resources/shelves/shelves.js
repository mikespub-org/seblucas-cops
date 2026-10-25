/**
 * ShelfManager - Shared client-side virtual shelves using localStorage
 * ES5-compatible core library - templates extend this
 */
var ShelfManager = (function() {
    var STORAGE_KEY = 'cops_shelves';
    
    // Ephemeral shelf page number (1-based) for pagination - not persisted
    var shelfPage = 1;
    // Default sort: insertion order ("date added"), ascending
    var DEFAULT_SORT_KEY = 'added+asc';
    // Last URL-echoed sort consumed from currentData (to detect fresh direct links)
    var lastUrlSortKey = null;
    
    var defaults = {
        activeShelf: 'Favorites',
        shelves: {
            'Favorites': []
        }
    };
    
    // Load data from localStorage with error handling
    function load() {
        try {
            if (typeof localStorage === 'undefined') {
                return defaults;
            }
            var data = localStorage.getItem(STORAGE_KEY);
            if (!data) {
                save(defaults);
                return defaults;
            }
            var parsed = JSON.parse(data);
            if (!parsed.shelves || typeof parsed.shelves !== 'object') {
                return defaults;
            }
            if (!parsed.activeShelf || !parsed.shelves[parsed.activeShelf]) {
                parsed.activeShelf = Object.keys(parsed.shelves)[0] || 'Favorites';
            }
            return parsed;
        } catch (e) {
            return defaults;
        }
    }
    
    // Save data to localStorage with error handling
    function save(data) {
        try {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            }
        } catch (e) {
            // localStorage unavailable (private browsing, quota exceeded)
        }
    }
    
    // Get all shelf data
    function getAll() {
        return load();
    }
    
    // Get the name of the currently active shelf
    function getActiveShelfName() {
        return load().activeShelf;
    }
    
    // Set the active shelf by name
    function setActiveShelf(name) {
        var data = load();
        if (data.shelves[name]) {
            data.activeShelf = name;
            shelfPage = 1;
            save(data);
            return true;
        }
        return false;
    }
    
    // Get books in the active shelf
    function getBooks() {
        var data = load();
        var shelfName = data.activeShelf;
        if (data.shelves[shelfName]) {
            return data.shelves[shelfName];
        }
        return [];
    }
    
    // Get books in a specific shelf
    function getBooksInShelf(shelfName) {
        var data = load();
        if (data.shelves[shelfName]) {
            return data.shelves[shelfName];
        }
        return [];
    }
    
    // Get all shelf names
    function getShelfNames() {
        return Object.keys(load().shelves);
    }
    
    // Normalize database id: undefined, null, '' and 0 are equivalent to ''
    function normalizeDb(db) {
        if (db === undefined || db === null || db === '' || db === 0 || db === '0') {
            return '';
        }
        return String(db);
    }
    
    // Get the unique key of a book: "db:id" (db may be '')
    function getBookKey(book) {
        if (!book) return '';
        return normalizeDb(book.db) + ':' + book.id;
    }
    
    // Check if a book is in the active shelf
    function isInShelf(book) {
        var key = getBookKey(book);
        var books = getBooks();
        for (var i = 0; i < books.length; i++) {
            if (getBookKey(books[i]) === key) {
                return true;
            }
        }
        return false;
    }
    
    // Toggle a book in the active shelf (add if not present, remove if present)
    function toggleBook(book) {
        var data = load();
        var shelfName = data.activeShelf;
        var books = data.shelves[shelfName];
        var key = getBookKey(book);
        var index = -1;
        
        for (var i = 0; i < books.length; i++) {
            if (getBookKey(books[i]) === key) {
                index = i;
                break;
            }
        }
        
        if (index > -1) {
            books.splice(index, 1);
            save(data);
            return false;
        } else {
            books.push(book);
            save(data);
            return true;
        }
    }
    
    // Add a book to a specific shelf
    function addBook(shelfName, book) {
        var data = load();
        if (!data.shelves[shelfName]) {
            data.shelves[shelfName] = [];
        }
        var books = data.shelves[shelfName];
        var key = getBookKey(book);
        
        for (var i = 0; i < books.length; i++) {
            if (getBookKey(books[i]) === key) {
                return false;
            }
        }
        
        books.push(book);
        save(data);
        return true;
    }
    
    // Remove a book from a specific shelf
    function removeBook(shelfName, book) {
        var data = load();
        if (!data.shelves[shelfName]) {
            return false;
        }
        var books = data.shelves[shelfName];
        var key = getBookKey(book);
        var index = -1;
        
        for (var i = 0; i < books.length; i++) {
            if (getBookKey(books[i]) === key) {
                index = i;
                break;
            }
        }
        
        if (index > -1) {
            books.splice(index, 1);
            save(data);
            return true;
        }
        return false;
    }
    
    // Create a new shelf
    function createShelf(name) {
        var data = load();
        if (data.shelves[name]) {
            return false;
        }
        data.shelves[name] = [];
        save(data);
        return true;
    }
    
    // Delete a shelf
    function deleteShelf(name) {
        var data = load();
        if (!data.shelves[name]) {
            return false;
        }
        if (Object.keys(data.shelves).length <= 1) {
            return false;
        }
        
        delete data.shelves[name];
        
        if (data.activeShelf === name) {
            var remaining = Object.keys(data.shelves);
            data.activeShelf = remaining[0];
        }
        
        save(data);
        return true;
    }
    
    // Rename a shelf
    function renameShelf(oldName, newName) {
        var data = load();
        if (!data.shelves[oldName] || data.shelves[newName]) {
            return false;
        }
        
        data.shelves[newName] = data.shelves[oldName];
        delete data.shelves[oldName];
        
        if (data.activeShelf === oldName) {
            data.activeShelf = newName;
        }
        
        save(data);
        return true;
    }

    // ---------------------------------------------------------------------------
    // Sort (persisted per user, applied client-side on the shelf page)
    // ---------------------------------------------------------------------------

    // Sort key echoed by the server for direct links (?sort=title+asc):
    // JsonRenderer::getJson() outputs sortedBy/sortedDir, PageShelves sets
    // $this->sorted from the request. Only client-sortable keys count -
    // anything else (incl. the "sort" default) returns null = ignore.
    // timestamp maps to insertion order ("added").
    function getUrlSortKey() {
        try {
            if (typeof currentData !== 'undefined' && currentData.sortedBy) {
                var dir = currentData.sortedDir === 'desc' ? 'desc' : 'asc';
                if (currentData.sortedBy === 'title') {
                    return 'title+' + dir;
                }
                if (currentData.sortedBy === 'author') {
                    return 'author+' + dir;
                }
                if (currentData.sortedBy === 'timestamp') {
                    return 'added+' + dir;
                }
            }
        } catch (e) {
            // fall through to persisted sort
        }
        return null;
    }

    // Get the persisted sort key, e.g. "title+asc", "author+desc", "added+asc"
    function getSortKey() {
        var urlKey = getUrlSortKey();
        if (urlKey && urlKey !== lastUrlSortKey) {
            // fresh direct link: adopt it once (persist + reset page),
            // then in-content sort clicks rule until the URL sort changes
            lastUrlSortKey = urlKey;
            var data = load();
            data.sortKey = urlKey;
            shelfPage = 1;
            save(data);
            return urlKey;
        }
        var stored = load();
        if (stored.sortKey && typeof stored.sortKey === 'string') {
            return stored.sortKey;
        }
        return DEFAULT_SORT_KEY;
    }

    // Persist the sort key and reset to the first page
    function setSortKey(key) {
        var data = load();
        data.sortKey = key;
        shelfPage = 1;
        save(data);
    }

    // Split a sort key into { name, dir } with validation - unknown names fall
    // back to "added" (insertion order), unknown dirs to "asc"
    function parseSortKey(key) {
        var name = key;
        var dir = 'asc';
        if (key && key.indexOf('+') > -1) {
            var parts = key.split('+');
            name = parts[0];
            dir = parts[1] === 'desc' ? 'desc' : 'asc';
        }
        if (name !== 'title' && name !== 'author' && name !== 'added') {
            name = 'added';
        }
        return { name: name, dir: dir };
    }

    // Null-safe string comparison (ES5 localeCompare)
    function compareStrings(a, b) {
        var sa = (a === undefined || a === null) ? '' : String(a);
        var sb = (b === undefined || b === null) ? '' : String(b);
        return sa.localeCompare(sb);
    }

    // Return a sorted copy of a book array (original storage order untouched).
    // Keys: "title", "author" (title as tie-break), "added" (insertion order).
    // Ties are broken by insertion index so the sort is stable across engines.
    function sortBooks(books, sortKey) {
        var parsed = parseSortKey(sortKey);
        var items = [];
        var i;
        for (i = 0; i < books.length; i++) {
            items.push({ book: books[i], index: i });
        }
        items.sort(function (x, y) {
            var r = 0;
            if (parsed.name === 'title') {
                r = compareStrings(x.book.title, y.book.title);
            } else if (parsed.name === 'author') {
                r = compareStrings(x.book.author, y.book.author);
                if (r === 0) {
                    r = compareStrings(x.book.title, y.book.title);
                }
            } else {
                r = x.index - y.index;
            }
            if (r === 0) {
                r = x.index - y.index;
            }
            return parsed.dir === 'desc' ? -r : r;
        });
        var out = [];
        for (i = 0; i < items.length; i++) {
            out.push(items[i].book);
        }
        return out;
    }

    // Sorted books of the active shelf
    function getSortedBooks() {
        return sortBooks(getBooks(), getSortKey());
    }

    // ---------------------------------------------------------------------------
    // Pagination (client-side, page size from c.config.max_item_per_page)
    // ---------------------------------------------------------------------------

    // Page size from currentData.c.config.max_item_per_page (available from the
    // complete=1 initial load and kept across AJAX navigations).
    // Returns 0 when missing, invalid or <= 0 (same as "no pagination" server-side).
    function getPageSize() {
        try {
            if (typeof currentData !== 'undefined' && currentData.c &&
                currentData.c.config && currentData.c.config.max_item_per_page !== undefined) {
                var size = parseInt(currentData.c.config.max_item_per_page, 10);
                if (!isNaN(size) && size > 0) {
                    return size;
                }
            }
        } catch (e) {
            // fall through to no pagination
        }
        return 0;
    }

    // Number of pages for a total of "total" books (always >= 1)
    function getPageCount(total, pageSize) {
        if (pageSize <= 0) {
            return 1;
        }
        return Math.max(1, Math.ceil(total / pageSize));
    }

    // Sorted + paginated view of the active shelf.
    // Returns { books, page, maxPage, pageSize, total } - the books array only
    // holds the current page when a page size is configured.
    function getPagedBooks() {
        var all = getSortedBooks();
        var total = all.length;
        var pageSize = getPageSize();
        var maxPage = getPageCount(total, pageSize);
        if (shelfPage > maxPage) {
            shelfPage = maxPage;
        }
        if (shelfPage < 1) {
            shelfPage = 1;
        }
        var books = all;
        if (pageSize > 0) {
            var start = (shelfPage - 1) * pageSize;
            books = all.slice(start, start + pageSize);
        }
        return {
            books: books,
            page: shelfPage,
            maxPage: maxPage,
            pageSize: pageSize,
            total: total
        };
    }

    // Set the current page (clamped on the next getPagedBooks() call)
    function setPage(page) {
        shelfPage = page;
    }

    // Export only the active shelf as a JSON string
    function exportData() {
        var data = load();
        var activeName = data.activeShelf;
        var exportData = {
            activeShelf: activeName,
            shelves: {}
        };
        exportData.shelves[activeName] = data.shelves[activeName] || [];
        return JSON.stringify(exportData);
    }

    // Import shelf data from a JSON string
    // mode: 'replace' replaces current active shelf, 'new' creates a new "Imported" shelf
    function importData(jsonString, mode) {
        try {
            var parsed = JSON.parse(jsonString);
            if (!parsed || typeof parsed !== 'object') {
                return false;
            }
            if (!parsed.shelves || typeof parsed.shelves !== 'object') {
                return false;
            }

            // Get the books from the exported shelf
            var shelfName = parsed.activeShelf || Object.keys(parsed.shelves)[0];
            var books = parsed.shelves[shelfName] || [];

            var data = load();

            if (mode === 'new') {
                // Create a new "Imported" shelf (with numeric suffix if needed)
                var newName = 'Imported';
                var counter = 1;
                while (data.shelves[newName]) {
                    newName = 'Imported ' + counter;
                    counter++;
                }
                data.shelves[newName] = books;
            } else if (mode === 'merge') {
                // Merge imported books into current active shelf (no duplicates)
                var currentActive = data.activeShelf;
                if (!data.shelves[currentActive]) {
                    data.shelves[currentActive] = [];
                }
                var currentBooks = data.shelves[currentActive];
                for (var k = 0; k < books.length; k++) {
                    var bookKey = getBookKey(books[k]);
                    var exists = false;
                    for (var l = 0; l < currentBooks.length; l++) {
                        if (getBookKey(currentBooks[l]) === bookKey) {
                            exists = true;
                            break;
                        }
                    }
                    if (!exists) {
                        currentBooks.push(books[k]);
                    }
                }
            } else {
                // Default: replace current active shelf
                var currentActive = data.activeShelf;
                if (!data.shelves[currentActive]) {
                    data.shelves[currentActive] = [];
                }
                data.shelves[currentActive] = books;
            }

            shelfPage = 1;
            save(data);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Download shelf data as a .json file
    function downloadExport() {
        var data = exportData();
        var blob;
        try {
            blob = new Blob([data], { type: 'application/json' });
        } catch (e) {
            var blobBuilder = window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder;
            if (blobBuilder) {
                var bb = new blobBuilder();
                bb.append(data);
                blob = bb.getBlob('application/json');
            } else {
                showStatusMessage(getI18n('shelfExportError', 'Export not supported in this browser.'), 'error');
                return;
            }
        }
        var url = (window.URL || window.webkitURL).createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'cops_shelves_export.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        (window.URL || window.webkitURL).revokeObjectURL(url);
    }

    // Build a zip download URL for all books in the active shelf.
    // Groups books by normalized database id and uses nested idlist params
    // so the ZipperHandler can serve multi-database shelves.
    // Returns null when the shelf is empty.
    function getShelfZipUrl(format) {
        var books = getBooks();
        if (!books || books.length < 1) {
            return null;
        }
        // group book ids by normalized database id
        var groups = {};
        for (var i = 0; i < books.length; i++) {
            var db = normalizeDb(books[i].db);
            if (!groups[db]) {
                groups[db] = [];
            }
            groups[db].push(books[i].id);
        }
        // build query string: idlist[0]=17,19&idlist[1]=1 ...
        var queryParts = [];
        var dbKeys = Object.keys(groups);
        for (var k = 0; k < dbKeys.length; k++) {
            var dbKey = dbKeys[k];
            var index = dbKey === '' ? 0 : parseInt(dbKey, 10);
            if (isNaN(index)) {
                index = k;
            }
            queryParts.push('idlist[' + index + ']=' + encodeURIComponent(groups[dbKey].join(',')));
        }
        var shelfName = getActiveShelfName();
        queryParts.push('source=' + encodeURIComponent(shelfName.replace(/[^\w -]/g, '')));
        var baseUrl = typeof currentData !== 'undefined' && currentData.baseurl
            ? currentData.baseurl
            : '.';
        return baseUrl + '/zipper/books/' + encodeURIComponent(String(format)) + '.zip?' + queryParts.join('&');
    }

    // Build a share URL for the active shelf that a target device can scan.
    // Mirrors getShelfZipUrl() but points at the shelves page (idlist + name)
    // so the target can fetch /restapi/shelf and import the books.
    // Returns null when the shelf is empty.
    function getShelfShareUrl() {
        var books = getBooks();
        if (!books || books.length < 1) {
            return null;
        }
        var groups = {};
        for (var i = 0; i < books.length; i++) {
            var db = normalizeDb(books[i].db);
            if (!groups[db]) {
                groups[db] = [];
            }
            groups[db].push(books[i].id);
        }
        var queryParts = [];
        var dbKeys = Object.keys(groups);
        for (var k = 0; k < dbKeys.length; k++) {
            var dbKey = dbKeys[k];
            var index = dbKey === '' ? 0 : parseInt(dbKey, 10);
            if (isNaN(index)) {
                index = k;
            }
            queryParts.push('idlist[' + index + ']=' + encodeURIComponent(groups[dbKey].join(',')));
        }
        var shelfName = getActiveShelfName();
        var safeName = String(shelfName).replace(/[^\w -]/g, '');
        queryParts.push('name=' + encodeURIComponent(safeName));
        queryParts.push('share=1');
        var baseUrl = (typeof currentData !== 'undefined' && currentData.shelvesurl)
            ? currentData.shelvesurl
            : ((typeof currentData !== 'undefined' && currentData.baseurl) ? currentData.baseurl : '.');
        var url = baseUrl + '?' + queryParts.join('&');

        // Resolve to an absolute URL when running in a browser so the QR code
        // can be scanned by another device (showQRCode only renders absolute URLs).
        try {
            if (typeof window !== 'undefined' && window.location && typeof window.location.href === 'string' &&
                window.location.href.indexOf('://') !== -1) {
                url = new URL(url, window.location.href).href;
            }
        } catch (e) { }
        return url;
    }


    // Download all books in the active shelf as a .zip file in the given format.
    // Uses existing ZipperHandler / ZipStream infrastructure.
    function downloadShelf(format) {
        if (typeof currentData === 'undefined' || !currentData.download) {
            showStatusMessage(getI18n('shelfDownloadDisabled', 'Downloads are disabled.'), 'error');
            return;
        }
        var url = getShelfZipUrl(format);
        if (!url) {
            showStatusMessage(getI18n('shelfDownloadEmpty', 'No books in shelf to download.'), 'error');
            return;
        }
        window.location.href = url;
        showStatusMessage(getI18n('shelfDownloadStarted', 'Download started.'), 'success');
    }

    // Show a status message on the page (replaces alert())
    function showStatusMessage(message, type) {
        var statusDiv = document.getElementById('shelf-status-message');
        if (!statusDiv) {
            statusDiv = document.createElement('div');
            statusDiv.id = 'shelf-status-message';
            statusDiv.style.padding = '10px';
            statusDiv.style.marginBottom = '10px';
            statusDiv.style.borderRadius = '4px';
            statusDiv.style.fontSize = '13px';
            // Insert OUTSIDE #shelf-page-content so refreshShelfPage()
            // re-renders (innerHTML replacement) do not wipe the message
            var pageContent = document.getElementById('shelf-page-content');
            if (pageContent && pageContent.parentNode) {
                pageContent.parentNode.insertBefore(statusDiv, pageContent);
            } else if (document.body) {
                document.body.insertBefore(statusDiv, document.body.firstChild);
            }
        }
        statusDiv.textContent = message;
        if (type === 'error') {
            statusDiv.style.backgroundColor = '#f2dede';
            statusDiv.style.color = '#a94442';
            statusDiv.style.border = '1px solid #ebccd1';
        } else {
            statusDiv.style.backgroundColor = '#dff0d8';
            statusDiv.style.color = '#3c763d';
            statusDiv.style.border = '1px solid #d6e9c6';
        }
        statusDiv.style.display = 'block';
        if (statusDiv.hideTimer) {
            clearTimeout(statusDiv.hideTimer);
        }
        statusDiv.hideTimer = setTimeout(function() {
            statusDiv.style.display = 'none';
            statusDiv.hideTimer = null;
        }, 5000);
    }

    // Handle the result of an import: parse mode, call importData, refresh UI
    function handleImportResult(jsonString, mode, fileInput) {
        var result = importData(jsonString, mode);
        if (result) {
            refreshShelfPage();
            updateUI();
            showStatusMessage(getI18n('shelfImportSuccess', 'Shelves imported successfully.'), 'success');
        } else {
            showStatusMessage(getI18n('shelfImportError', 'Invalid shelf data file.'), 'error');
        }
        if (fileInput) {
            fileInput.value = '';
        }
    }

    // Default import mode dialog (uses prompt). Templates can override with a modal.
    function showImportDialog(jsonString, callback) {
        var choice = prompt(getI18n('shelfImportChoicePrompt',
            'Import shelf data:\n\nR = Replace current active shelf\nN = Create new shelf "Imported"\nM = Merge into current active shelf'),
            'R');
        var mode = 'replace';
        if (choice && choice.length > 0) {
            var first = choice.charAt(0).toUpperCase();
            if (first === 'N') {
                mode = 'new';
            } else if (first === 'M') {
                mode = 'merge';
            }
        }
        callback(mode);
    }

    // Escape HTML entities
    function escapeHtml(text) {
        if (text === undefined || text === null || text === '') return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Read a string attribute, treating missing values and the literal
    // strings "null"/"undefined" (as emitted by some template engines for
    // null values) as empty
    function getStringAttribute(btn, name) {
        var value = btn.getAttribute(name);
        if (!value || value === 'null' || value === 'undefined') {
            return '';
        }
        return value;
    }

    // Handle toggle button clicks (shared across all templates)
    function handleToggle(btn) {
        var bookData = {
            db: getStringAttribute(btn, 'data-book-db'),
            id: btn.getAttribute('data-book-id'),
            title: getStringAttribute(btn, 'data-book-title'),
            author: getStringAttribute(btn, 'data-book-author'),
            thumbnailurl: getStringAttribute(btn, 'data-book-thumbnail'),
            detailurl: getStringAttribute(btn, 'data-book-detailurl')
        };
        
        toggleBook(bookData);
        updateUI();
        refreshShelfPage();
    }
    
    // Render the dedicated shelf page into #shelf-page-content
    function renderShelfPage() {
        var pageContent = document.getElementById('shelf-page-content');
        if (pageContent) {
            pageContent.innerHTML = ShelfManager.renderShelfContent();
            ShelfManager.bindPageEvents();
        }
    }
    
    // Refresh shelf page content
    function refreshShelfPage() {
        renderShelfPage();
    }
    
    // Bind events for shelf page interactions (shared across all templates,
    // as all templates use the same element ids and data attributes)
    function bindPageEvents() {
        var switcherLinks = document.querySelectorAll('#shelf-switcher a');
        for (var i = 0; i < switcherLinks.length; i++) {
            (function(shelfName) {
                switcherLinks[i].addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    setActiveShelf(shelfName);
                    refreshShelfPage();
                    updateUI();
                });
            })(switcherLinks[i].getAttribute('data-shelf'));
        }

        var createBtn = document.getElementById('shelf-create-btn');
        if (createBtn) {
            createBtn.addEventListener('click', function() {
                var name = prompt(getI18n('shelfCreatePrompt', 'Enter shelf name:'));
                if (name && name.trim()) {
                    if (createShelf(name.trim())) {
                        setActiveShelf(name.trim());
                        refreshShelfPage();
                        updateUI();
                    } else {
                        alert(getI18n('shelfExistsError', 'Shelf already exists or could not be created.'));
                    }
                }
            });
        }

        var deleteBtn = document.getElementById('shelf-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                var activeName = getActiveShelfName();
                if (confirm(str_format(getI18n('shelfDeleteConfirm', 'Delete shelf "{0}"?'), activeName))) {
                    if (deleteShelf(activeName)) {
                        refreshShelfPage();
                        updateUI();
                    } else {
                        alert(getI18n('shelfDeleteError', 'Cannot delete the last shelf.'));
                    }
                }
            });
        }

        var renameBtn = document.getElementById('shelf-rename-btn');
        if (renameBtn) {
            renameBtn.addEventListener('click', function() {
                var oldName = getActiveShelfName();
                var newName = prompt(getI18n('shelfRenamePrompt', 'Enter new shelf name'), oldName);
                if (newName && newName.trim() && newName.trim() !== oldName) {
                    if (renameShelf(oldName, newName.trim())) {
                        refreshShelfPage();
                        updateUI();
                    } else {
                        alert(getI18n('shelfRenameError', 'Shelf name already exists or could not be renamed.'));
                    }
                }
            });
        }

        var exportBtn = document.getElementById('shelf-export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                downloadExport();
            });
        }
        var shareBtn = document.getElementById('shelf-share-btn');
        if (shareBtn) {
            shareBtn.addEventListener('click', function() {
                var url = getShelfShareUrl();
                if (!url) {
                    showStatusMessage(getI18n('shelfDownloadEmpty', 'No books in shelf to download.'), 'error');
                    return;
                }
                if (url.length > 2000) {
                    showStatusMessage(getI18n('shelfShareTooLarge', 'Shelf too large for QR code sharing (URL exceeds 2000 characters).'), 'error');
                    return;
                }
                var qrContainer = document.getElementById('shelf-share-qr');
                if (qrContainer && typeof showQRCode === 'function') {
                    showQRCode('shelf-share-qr', url);
                }
            });
        }



        var importBtn = document.getElementById('shelf-import-btn');
        if (importBtn) {
            importBtn.addEventListener('click', function() {
                var fileInput = document.getElementById('shelf-import-file');
                if (fileInput) {
                    fileInput.click();
                }
            });
        }

        var importFile = document.getElementById('shelf-import-file');
        if (importFile) {
            importFile.addEventListener('change', function() {
                var file = importFile.files[0];
                if (!file) {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    var parsed;
                    try {
                        parsed = JSON.parse(e.target.result);
                    } catch (err) {
                        showStatusMessage(getI18n('shelfImportError', 'Invalid shelf data file.'), 'error');
                        importFile.value = '';
                        return;
                    }
                    if (!parsed || typeof parsed !== 'object' ||
                        !parsed.shelves || typeof parsed.shelves !== 'object') {
                        showStatusMessage(getI18n('shelfImportError', 'Invalid shelf data file.'), 'error');
                        importFile.value = '';
                        return;
                    }

                    // Delegate import mode choice to template-specific dialog
                    if (typeof ShelfManager.showImportDialog === 'function') {
                        ShelfManager.showImportDialog(e.target.result, function(mode) {
                            handleImportResult(e.target.result, mode, importFile);
                        });
                    } else {
                        handleImportResult(e.target.result, 'replace', importFile);
                    }

                };
                reader.onerror = function() {
                    showStatusMessage(getI18n('shelfImportError', 'Could not read shelf data file.'), 'error');
                    importFile.value = '';
                };
                reader.readAsText(file);
            });
        }


        var downloadButtons = document.querySelectorAll('.shelf-download-zip-btn');
        for (var k = 0; k < downloadButtons.length; k++) {
            (function(fmt) {
                downloadButtons[k].addEventListener('click', function() {
                    downloadShelf(fmt);
                });
            })(downloadButtons[k].getAttribute('data-format'));
        }

        var removeButtons = document.querySelectorAll('.shelf-remove-btn');
        for (var j = 0; j < removeButtons.length; j++) {
            (function(book) {
                removeButtons[j].addEventListener('click', function() {
                    var shelfName = getActiveShelfName();
                    removeBook(shelfName, book);
                    refreshShelfPage();
                    updateUI();
                });
            })({
                db: removeButtons[j].getAttribute('data-book-db') || '',
                id: removeButtons[j].getAttribute('data-book-id')
            });
        }

        // Sort links (shared: #shelf-sortlinks a[data-sort] with the key name)
        var sortLinks = document.querySelectorAll('#shelf-sortlinks a[data-sort]');
        for (var s = 0; s < sortLinks.length; s++) {
            (function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var key = link.getAttribute('data-sort');
                    var current = parseSortKey(getSortKey());
                    var dir = 'asc';
                    if (current.name === key) {
                        dir = current.dir === 'asc' ? 'desc' : 'asc';
                    }
                    setSortKey(key + '+' + dir);
                    refreshShelfPage();
                });
            })(sortLinks[s]);
        }

        // Pager links (shared: #shelf-pager a[data-page] = first|prev|next|last)
        var pageLinks = document.querySelectorAll('#shelf-pager a[data-page]');
        for (var p = 0; p < pageLinks.length; p++) {
            (function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var li = link.parentNode;
                    if (li && (' ' + li.className + ' ').indexOf(' disabled ') > -1) {
                        return;
                    }
                    var info = getPagedBooks();
                    var target = info.page;
                    var action = link.getAttribute('data-page');
                    if (action === 'first') {
                        target = 1;
                    } else if (action === 'prev') {
                        target = info.page - 1;
                    } else if (action === 'next') {
                        target = info.page + 1;
                    } else if (action === 'last') {
                        target = info.maxPage;
                    }
                    if (target < 1) {
                        target = 1;
                    }
                    if (target > info.maxPage) {
                        target = info.maxPage;
                    }
                    if (target === info.page) {
                        return;
                    }
                    setPage(target);
                    refreshShelfPage();
                    var content = document.getElementById('shelf-page-content');
                    if (content && typeof content.scrollIntoView === 'function') {
                        content.scrollIntoView();
                    }
                });
            })(pageLinks[p]);
        }
    }
    
    // Detect whether the current page URL carries a shared shelf (idlist + share=1).
    function isSharedShelfUrl() {
        if (typeof window === 'undefined' || !window.location || !window.location.search) return false;
        var params = parseQueryString(window.location.search);
        return !!(params && params.idlist && (params.share === '1' || params.share === 1));
    }

    function parseQueryString(qs) {
        var out = {};
        if (!qs) return out;
        var s = qs.charAt(0) === '?' ? qs.substring(1) : qs;
        if (!s) return out;
        var parts = s.split('&');
        for (var i = 0; i < parts.length; i++) {
            var kv = parts[i].split('=');
            var key = decodeURIComponent(kv[0] || '');
            var val = kv.length > 1 ? decodeURIComponent(kv.slice(1).join('=')) : '';
            if (!key) continue;
            if (key.indexOf('idlist') === 0) {
                out.idlist = out.idlist || {};
                var m = key.match(/^idlist\[(.*)\]$/);
                if (m) {
                    out.idlist[m[1]] = val;
                } else {
                    out.idlist[''] = val;
                }
            } else {
                out[key] = val;
            }
        }
        return out;
    }

    var shareHandled = false;
    function handleSharedShelf() {
        if (shareHandled) return;
        if (typeof currentData === 'undefined' || !currentData.baseurl) return;
        if (!isSharedShelfUrl()) return;
        shareHandled = true;

        var fetchUrl = currentData.baseurl + '/restapi/shelf' + window.location.search;

        var onSuccess = function (resp) {
            processSharedShelfResponse(resp);
        };
        var onError = function () {
            showStatusMessage(getI18n('shelfImportError', 'Invalid shelf data file.'), 'error');
            shareHandled = false;
        };

        if (typeof $ !== 'undefined' && typeof $.getJSON === 'function') {
            $.getJSON(fetchUrl, onSuccess).fail(onError);
        } else if (typeof fetch === 'function') {
            fetch(fetchUrl).then(function (r) { return r.json(); }).then(onSuccess).catch(onError);
        } else {
            shareHandled = false;
        }
    }

    function processSharedShelfResponse(resp) {
        if (!resp || !resp.entries || !Array.isArray(resp.entries)) {
            showStatusMessage(getI18n('shelfImportError', 'Invalid shelf data file.'), 'error');
            shareHandled = false;
            return;
        }
        var books = [];
        for (var i = 0; i < resp.entries.length; i++) {
            var e = resp.entries[i];
            if (!e || e.id === undefined || e.id === null) continue;
            books.push({
                id: String(e.id),
                db: (e.db === undefined || e.db === null) ? '' : e.db,
                title: e.title || '',
                author: e.author || '',
                thumbnailurl: e.thumbnail || '',
                detailurl: e.detailurl || ''
            });
        }
        if (books.length < 1) {
            showStatusMessage(getI18n('shelfDownloadEmpty', 'No books in shelf to download.'), 'error');
            cleanShareUrl();
            return;
        }
        var requestedName = (resp.shelf && String(resp.shelf).trim()) ? String(resp.shelf).trim() : 'Shared';
        var data = load();
        if (!data.shelves) data.shelves = {};
        var name = requestedName;
        var counter = 2;
        while (data.shelves[name]) {
            name = requestedName + ' ' + counter;
            counter++;
        }
        data.shelves[name] = books;
        data.activeShelf = name;
        shelfPage = 1;
        save(data);
        if (typeof refreshShelfPage === 'function') refreshShelfPage();
        if (typeof updateUI === 'function') updateUI();
        showStatusMessage(getI18n('shelfImportSuccess', 'Shelf imported successfully.'), 'success');
        cleanShareUrl();
    }

    function cleanShareUrl() {
        try {
            if (typeof window !== 'undefined' && window.history && typeof window.history.replaceState === 'function' && typeof currentData !== 'undefined' && currentData.shelvesurl) {
                window.history.replaceState({}, document.title, currentData.shelvesurl);
            }
        } catch (e) { }
    }

    // Navigate to the dedicated shelves page (shared across all templates)
    function openShelvesPage() {
        if (typeof navigateTo === 'function' && typeof currentData !== 'undefined' && currentData.shelvesurl) {
            navigateTo(currentData.shelvesurl);
        }
    }
    
    // Update UI elements (badge count only - templates handle button states)
    function updateUI() {
        var count = getBooks().length;
        var badge = document.getElementById('shelf-count');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Delegate to template-specific updateBookButtons()
        if (typeof ShelfManager.updateBookButtons === 'function') {
            ShelfManager.updateBookButtons();
        }
    }
    
    // Initialize event handlers with template delegation
    function init() {
        $(document).off('click', '.shelf-toggle-btn').on('click', '.shelf-toggle-btn', function(e) {
            e.preventDefault();
            handleToggle(this);
        });
        
        // Call template-specific initialization if available
        if (typeof ShelfManager.initTemplate === 'function') {
            ShelfManager.initTemplate();
        }
    }
    
    // Public API
    return {
        load: load,
        save: save,
        getAll: getAll,
        getActiveShelfName: getActiveShelfName,
        setActiveShelf: setActiveShelf,
        getBooks: getBooks,
        getBooksInShelf: getBooksInShelf,
        getShelfNames: getShelfNames,
        getBookKey: getBookKey,
        isInShelf: isInShelf,
        toggleBook: toggleBook,
        addBook: addBook,
        removeBook: removeBook,
        createShelf: createShelf,
        deleteShelf: deleteShelf,
        renameShelf: renameShelf,
        getSortKey: getSortKey,
        setSortKey: setSortKey,
        parseSortKey: parseSortKey,
        getUrlSortKey: getUrlSortKey,
        sortBooks: sortBooks,
        getSortedBooks: getSortedBooks,
        getPageSize: getPageSize,
        getPageCount: getPageCount,
        getPagedBooks: getPagedBooks,
        setPage: setPage,
        exportData: exportData,
        importData: importData,
        downloadExport: downloadExport,
        downloadShelf: downloadShelf,
        getShelfZipUrl: getShelfZipUrl,
        getShelfShareUrl: getShelfShareUrl,
        handleSharedShelf: handleSharedShelf,
        showImportDialog: showImportDialog,
        showStatusMessage: showStatusMessage,
        escapeHtml: escapeHtml,
        handleToggle: handleToggle,
        renderShelfPage: renderShelfPage,
        refreshShelfPage: refreshShelfPage,
        bindPageEvents: bindPageEvents,
        openShelvesPage: openShelvesPage,
        updateUI: updateUI,
        init: init
    };
})();
