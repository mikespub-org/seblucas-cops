/**
 * ShelfManager - Shared client-side virtual shelves using localStorage
 * ES5-compatible core library - templates extend this
 */
var ShelfManager = (function() {
    var STORAGE_KEY = 'cops_shelves';
    
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
                var newName = prompt(getI18n('shelfRenamePrompt', 'Enter new shelf name:'), oldName);
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
        exportData: exportData,
        importData: importData,
        downloadExport: downloadExport,
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
