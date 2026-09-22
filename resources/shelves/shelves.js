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
            console.log('Unable to save shelf data: ' + e.message);
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
    
    // Check if a book is in the active shelf
    function isInShelf(bookId) {
        // @todo check databaseId
        var books = getBooks();
        for (var i = 0; i < books.length; i++) {
            if (books[i].id === bookId) {
                return true;
            }
        }
        return false;
    }
    
    // Toggle a book in the active shelf (add if not present, remove if present)
    function toggleBook(bookId, bookData) {
        var data = load();
        var shelfName = data.activeShelf;
        var books = data.shelves[shelfName];
        var index = -1;
        
        // @todo check databaseId
        for (var i = 0; i < books.length; i++) {
            if (books[i].id === bookId) {
                index = i;
                break;
            }
        }
        
        if (index > -1) {
            books.splice(index, 1);
            save(data);
            return false;
        } else {
            bookData.id = bookId;
            books.push(bookData);
            save(data);
            return true;
        }
    }
    
    // Add a book to a specific shelf
    function addBook(shelfName, bookData) {
        var data = load();
        if (!data.shelves[shelfName]) {
            data.shelves[shelfName] = [];
        }
        var books = data.shelves[shelfName];
        
        for (var i = 0; i < books.length; i++) {
            if (books[i].id === bookData.id) {
                return false;
            }
        }
        
        books.push(bookData);
        save(data);
        return true;
    }
    
    // Remove a book from a specific shelf
    function removeBook(shelfName, bookId) {
        var data = load();
        if (!data.shelves[shelfName]) {
            return false;
        }
        var books = data.shelves[shelfName];
        var index = -1;
        
        // @todo check databaseId
        for (var i = 0; i < books.length; i++) {
            if (books[i].id === bookId) {
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
    
    // Escape HTML entities
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
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
        // Use event delegation for shelf toggle to survive AJAX navigation
        $(document).off('click', '#shelf-toggle').on('click', '#shelf-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Delegate to template implementation
            if (typeof ShelfManager.openModal === 'function') {
                ShelfManager.openModal();
            }
        });
        
        $(document).off('click', '.shelf-toggle-btn').on('click', '.shelf-toggle-btn', function(e) {
            e.preventDefault();
            // Delegate to template implementation
            if (typeof ShelfManager.handleToggle === 'function') {
                ShelfManager.handleToggle(this);
            } else {
                // Fallback: just toggle book and update UI
                var bookId = this.getAttribute('data-book-id');
                var bookData = {
                    db: this.getAttribute('data-book-db') || '',
                    title: this.getAttribute('data-book-title') || '',
                    author: this.getAttribute('data-book-author') || '',
                    thumbnailurl: this.getAttribute('data-book-thumbnail') || '',
                    detailurl: this.getAttribute('data-book-detailurl') || ''
                };
                toggleBook(bookId, bookData);
                updateUI();
            }
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
        isInShelf: isInShelf,
        toggleBook: toggleBook,
        addBook: addBook,
        removeBook: removeBook,
        createShelf: createShelf,
        deleteShelf: deleteShelf,
        renameShelf: renameShelf,
        escapeHtml: escapeHtml,
        updateUI: updateUI,
        init: init
    };
})();
