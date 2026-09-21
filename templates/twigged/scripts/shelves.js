/**
 * ShelfManager - Client-side virtual shelves using localStorage
 * ES5-compatible for twigged template
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
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Render HTML for a shelf toggle button
    function renderShelfButton(bookId, bookData) {
        // @todo check databaseId
        var inShelf = isInShelf(bookId);
        var btnClass = inShelf ? 'btn-warning' : 'btn-default';
        var title = inShelf ? 'Remove from Shelf' : 'Add to Shelf';
        
        return '<button type="button" class="btn btn-sm ' + btnClass + ' shelf-toggle-btn" ' +
               'data-book-db="' + escapeHtml(bookData.db || '') + '" ' +
               'data-book-id="' + bookId + '" ' +
               'data-book-title="' + escapeHtml(bookData.title || '') + '" ' +
               'data-book-author="' + escapeHtml(bookData.author || '') + '" ' +
               'data-book-thumbnail="' + escapeHtml(bookData.thumbnailurl || '') + '" ' +
               'data-book-detailurl="' + escapeHtml(bookData.detailurl || '') + '" ' +
               'title="' + escapeHtml(title) + '">' +
               '<span class="glyphicon glyphicon-bookmark"></span>' +
               '</button>';
    }
    
    // Render the shelf management modal content
    function renderShelfModal() {
        var data = load();
        var activeShelf = data.activeShelf;
        var books = data.shelves[activeShelf] || [];
        var shelfNames = Object.keys(data.shelves);
        
        var html = '<div class="modal-header">' +
                   '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                   '<span aria-hidden="true">&times;</span></button>' +
                   '<h4 class="modal-title">My Shelves</h4>' +
                   '</div>' +
                   '<div class="modal-body">' +
                   '<div class="form-group">' +
                   '<label>Current Shelf:</label> ' +
                   '<div class="btn-group">' +
                   '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">' +
                   escapeHtml(activeShelf) + ' <span class="caret"></span></button>' +
                   '<ul class="dropdown-menu" id="shelf-switcher">';
        
        for (var i = 0; i < shelfNames.length; i++) {
            var name = shelfNames[i];
            var activeClass = (name === activeShelf) ? ' class="active"' : '';
            html += '<li' + activeClass + '><a href="javascript:void(0);" data-shelf="' + escapeHtml(name) + '">' + escapeHtml(name) + '</a></li>';
        }
        
        html += '</ul>' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-success" id="shelf-create-btn">' +
                '<span class="glyphicon glyphicon-plus"></span> New</button> ';
        
        if (shelfNames.length > 1) {
            html += '<button type="button" class="btn btn-sm btn-danger" id="shelf-delete-btn">' +
                    '<span class="glyphicon glyphicon-trash"></span> Delete</button> ';
        }
        
        html += '<button type="button" class="btn btn-sm btn-info" id="shelf-rename-btn">' +
                '<span class="glyphicon glyphicon-pencil"></span> Rename</button>' +
                '</div>';
        
        // Book list
        html += '<hr><h5>Books in "' + escapeHtml(activeShelf) + '"</h5>';
        
        if (books.length === 0) {
            html += '<p class="text-muted">This shelf is empty. Browse books and add them using the bookmark button.</p>';
        } else {
            html += '<div class="list-group" id="shelf-book-list">';
            for (var j = 0; j < books.length; j++) {
                var book = books[j];
                html += '<div class="list-group-item" data-book-id="' + escapeHtml(book.id) + '">' +
                        '<div class="row">' +
                        '<div class="col-sm-2">' +
                        '<img src="' + escapeHtml(book.thumbnailurl || '') + '" class="img-thumbnail" style="max-height: 80px;">' +
                        '</div>' +
                        '<div class="col-sm-8">' +
                        '<strong><a href="' + escapeHtml(book.detailurl || '#') + '">' + escapeHtml(book.title) + '</a></strong><br>' +
                        '<small>' + escapeHtml(book.author || 'Unknown') + '</small>';
                
                if (book.seriesName) {
                    html += '<br><small><em>' + escapeHtml(book.seriesName) + ' #' + escapeHtml(book.seriesIndex || '') + '</em></small>';
                }
                
                html += '</div>' +
                        '<div class="col-sm-2">' +
                        '<button type="button" class="btn btn-sm btn-danger shelf-remove-btn" data-book-db="' + escapeHtml(book.db) + '" data-book-id="' + escapeHtml(book.id) + '">' +
                        '<span class="glyphicon glyphicon-remove"></span> Remove</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
            }
            html += '</div>';
        }
        
        html += '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
                '</div>';
        
        return html;
    }
    
    // Refresh the shelf modal content
    function refreshShelfModal() {
        var content = document.getElementById('shelf-modal-content');
        if (content) {
            content.innerHTML = renderShelfModal();
            bindModalEvents();
        }
    }
    
    // Update UI elements (badge count, button states)
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
        
        var buttons = document.querySelectorAll('.shelf-toggle-btn');
        for (var i = 0; i < buttons.length; i++) {
            var btn = buttons[i];
            var bookId = btn.getAttribute('data-book-id');
            if (bookId) {
                var bookData = {
                    db: btn.getAttribute('data-book-db') || '',
                    title: btn.getAttribute('data-book-title') || '',
                    author: btn.getAttribute('data-book-author') || '',
                    thumbnailurl: btn.getAttribute('data-book-thumbnail') || '',
                    detailurl: btn.getAttribute('data-book-detailurl') || ''
                };
                var newHtml = renderShelfButton(bookId, bookData);
                btn.outerHTML = newHtml;
            }
        }
    }
    
    // Bind events for the shelf modal
    function bindModalEvents() {
        var switcherLinks = document.querySelectorAll('#shelf-switcher a');
        for (var i = 0; i < switcherLinks.length; i++) {
            (function(shelfName) {
                switcherLinks[i].addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    setActiveShelf(shelfName);
                    refreshShelfModal();
                    updateUI();
                });
            })(switcherLinks[i].getAttribute('data-shelf'));
        }
        
        var createBtn = document.getElementById('shelf-create-btn');
        if (createBtn) {
            createBtn.addEventListener('click', function() {
                var name = prompt('Enter shelf name:');
                if (name && name.trim()) {
                    if (createShelf(name.trim())) {
                        setActiveShelf(name.trim());
                        refreshShelfModal();
                        updateUI();
                    } else {
                        alert('Shelf already exists or could not be created.');
                    }
                }
            });
        }
        
        var deleteBtn = document.getElementById('shelf-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                var activeName = getActiveShelfName();
                if (confirm('Delete shelf "' + activeName + '"?')) {
                    if (deleteShelf(activeName)) {
                        refreshShelfModal();
                        updateUI();
                    } else {
                        alert('Cannot delete the last shelf.');
                    }
                }
            });
        }
        
        var renameBtn = document.getElementById('shelf-rename-btn');
        if (renameBtn) {
            renameBtn.addEventListener('click', function() {
                var oldName = getActiveShelfName();
                var newName = prompt('Enter new shelf name:', oldName);
                if (newName && newName.trim() && newName.trim() !== oldName) {
                    if (renameShelf(oldName, newName.trim())) {
                        refreshShelfModal();
                        updateUI();
                    } else {
                        alert('Shelf name already exists or could not be renamed.');
                    }
                }
            });
        }
        
        // @todo check databaseId
        var removeButtons = document.querySelectorAll('.shelf-remove-btn');
        for (var j = 0; j < removeButtons.length; j++) {
            (function(bookId) {
                removeButtons[j].addEventListener('click', function() {
                    var shelfName = getActiveShelfName();
                    removeBook(shelfName, bookId);
                    refreshShelfModal();
                    updateUI();
                });
            })(removeButtons[j].getAttribute('data-book-id'));
        }
    }
    
    // Open the shelf modal
    function openModal() {
        var modal = document.getElementById('shelf-modal');
        if (modal) {
            refreshShelfModal();
            $(modal).modal('show');
        }
    }
    
    // Initialize event handlers
    function init() {
        // Use event delegation for shelf toggle to survive AJAX navigation
        $(document).on('click', '#shelf-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openModal();
        });
        
        $(document).on('click', '.shelf-toggle-btn', function(e) {
            e.preventDefault();
            var btn = this;
            var bookId = btn.getAttribute('data-book-id');
            var bookData = {
                db: btn.getAttribute('data-book-db') || '',
                title: btn.getAttribute('data-book-title') || '',
                author: btn.getAttribute('data-book-author') || '',
                thumbnailurl: btn.getAttribute('data-book-thumbnail') || '',
                detailurl: btn.getAttribute('data-book-detailurl') || ''
            };
            
            toggleBook(bookId, bookData);
            var newHtml = renderShelfButton(bookId, bookData);
            btn.outerHTML = newHtml;
            updateUI();
        });
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
        renderShelfButton: renderShelfButton,
        renderShelfModal: renderShelfModal,
        updateUI: updateUI,
        init: init
    };
})();
