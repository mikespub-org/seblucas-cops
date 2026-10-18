/**
 * Bootstrap2 template - extends shared ShelfManager with template-specific UI
 * Uses shared ShelfManager from resources/shelves/shelves.js
 */
$(document).ready(function() {
    if (typeof ShelfManager !== 'undefined') {
        // Handle toggle button clicks for bootstrap2
        ShelfManager.handleToggle = function(btn) {
            var bookData = {
                db: btn.getAttribute('data-book-db') || '',
                id: btn.getAttribute('data-book-id'),
                title: btn.getAttribute('data-book-title') || '',
                author: btn.getAttribute('data-book-author') || '',
                thumbnailurl: btn.getAttribute('data-book-thumbnail') || '',
                detailurl: btn.getAttribute('data-book-detailurl') || ''
            };

            ShelfManager.toggleBook(bookData);
            ShelfManager.updateUI();
            ShelfManager.refreshShelfPage();
        };

        // Update book button states in booklist views
        ShelfManager.updateBookButtons = function() {
            var buttons = document.querySelectorAll('.shelf-toggle-btn');
            for (var i = 0; i < buttons.length; i++) {
                var btn = buttons[i];
                var bookId = btn.getAttribute('data-book-id');
                if (bookId) {
                    var book = {
                        db: btn.getAttribute('data-book-db') || '',
                        id: bookId
                    };
                    var inShelf = ShelfManager.isInShelf(book);

                    // Update button class
                    if (inShelf) {
                        btn.classList.add('btn-danger');
                        btn.classList.remove('btn-default');
                        btn.setAttribute('title', getI18n('shelfRemoveTitle', 'Remove from Shelf'));
                    } else {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-default');
                        btn.setAttribute('title', getI18n('shelfAddTitle', 'Add to Shelf'));
                    }
                }
            }
        };

        // Render shelf toolbar and book list content
        ShelfManager.renderShelfContent = function() {
            var data = ShelfManager.load();
            var activeShelf = data.activeShelf;
            var books = data.shelves[activeShelf] || [];
            var shelfNames = Object.keys(data.shelves);

            var html = '<div class="panel panel-default">' +
                       '<div class="panel-body">' +
                       '<div class="btn-group">' +
                       '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">' +
                       ShelfManager.escapeHtml(activeShelf) + ' <span class="badge pull-right">' + books.length + '</span> <span class="caret"></span></button>' +
                       '<ul class="dropdown-menu" id="shelf-switcher">';

            for (var i = 0; i < shelfNames.length; i++) {
                var name = shelfNames[i];
                var activeClass = (name === activeShelf) ? ' class="active"' : '';
                html += '<li' + activeClass + '><a href="javascript:void(0);" data-shelf="' + ShelfManager.escapeHtml(name) + '">' + ShelfManager.escapeHtml(name) + '</a></li>';
            }

            html += '</ul>' +
                    '</div>' +
                    ' <button type="button" class="btn btn-success" id="shelf-create-btn" title="' + getI18n('shelfCreatePrompt', 'Enter shelf name:') + '">' +
                    '<span class="glyphicon glyphicon-plus"></span> ' + getI18n('shelfNewTitle', 'New') + '</button> ';

            if (shelfNames.length > 1) {
                html += '<button type="button" class="btn btn-danger" id="shelf-delete-btn" title="' + getI18n('shelfDeleteTitle', 'Delete') + '">' +
                        '<span class="glyphicon glyphicon-trash"></span> ' + getI18n('shelfDeleteTitle', 'Delete') + '</button> ';
            }

            html += '<button type="button" class="btn btn-info" id="shelf-rename-btn" title="' + getI18n('shelfRenameTitle', 'Rename') + '">' +
                    '<span class="glyphicon glyphicon-pencil"></span> ' + getI18n('shelfRenameTitle', 'Rename') + '</button>' +
                    '</div>' +
                    '</div>';

            // Book list
            html += '<h4>' + getI18n('shelfBooksInTitle', 'Books in') + ' "' + ShelfManager.escapeHtml(activeShelf) + '"' +
                    ' <span class="badge pull-right">' + books.length + '</span></h4>';

            if (books.length === 0) {
                html += '<p class="text-muted">' + getI18n('shelfEmptyTitle', 'This shelf is empty. Browse books and add them using the bookmark button.') + '</p>';
            } else {
                html += '<div class="row d-flex" id="shelf-book-list">';
                for (var j = 0; j < books.length; j++) {
                    var book = books[j];
                    html += '<div class="col-lg-2 col-sm-3 col-xs-6 books" data-book-id="' + ShelfManager.escapeHtml(book.id) + '">';
                    if (book.thumbnailurl) {
                        html += '<div class="cover-image">' +
                                '<a href="' + ShelfManager.escapeHtml(book.detailurl || '#') + '">' +
                                '<img class="img-responsive" src="' + ShelfManager.escapeHtml(book.thumbnailurl) + '" alt="' + ShelfManager.escapeHtml(book.title) + '">' +
                                '</a>' +
                                '</div>';
                    }
                    html += '<div class="meta">' +
                            '<p class="title ellipsis"><a href="' + ShelfManager.escapeHtml(book.detailurl || '#') + '">' + ShelfManager.escapeHtml(book.title) + '</a></p>' +
                            '<div class="author ellipsis">' + ShelfManager.escapeHtml(book.author || getI18n('shelfUnknownAuthor', 'Unknown')) + '</div>';

                    if (book.seriesName) {
                        html += '<div class="series ellipsis"><em>' + ShelfManager.escapeHtml(book.seriesName) + ' #' + ShelfManager.escapeHtml(book.seriesIndex || '') + '</em></div>';
                    }

                    html += '</div>' +
                            '<div class="text-center" style="height: 40px;">' +
                            '<button type="button" class="btn btn-sm btn-danger shelf-remove-btn" data-book-db="' + ShelfManager.escapeHtml(book.db) + '" data-book-id="' + ShelfManager.escapeHtml(book.id) + '" title="' + getI18n('shelfRemoveTitle', 'Remove from Shelf') + '">' +
                            '<span class="glyphicon glyphicon-remove"></span> ' + getI18n('shelfRemoveAlt', 'Remove') + '</button>' +
                            '</div>' +
                            '</div>';
                }
                html += '</div>';
            }

            return html;
        };

        // Render dedicated shelf page content
        ShelfManager.renderShelfPage = function() {
            var pageContent = document.getElementById('shelf-page-content');
            if (pageContent) {
                pageContent.innerHTML = ShelfManager.renderShelfContent();
                ShelfManager.bindPageEvents();
            }
        };

        // Refresh shelf page content
        ShelfManager.refreshShelfPage = function() {
            ShelfManager.renderShelfPage();
        };

        // Bind events for shelf page interactions
        ShelfManager.bindPageEvents = function() {
            var switcherLinks = document.querySelectorAll('#shelf-switcher a');
            for (var i = 0; i < switcherLinks.length; i++) {
                (function(shelfName) {
                    switcherLinks[i].addEventListener('click', function(e) {
                        e.preventDefault();
                        ShelfManager.setActiveShelf(shelfName);
                        ShelfManager.refreshShelfPage();
                        ShelfManager.updateUI();
                    });
                })(switcherLinks[i].getAttribute('data-shelf'));
            }

            var createBtn = document.getElementById('shelf-create-btn');
            if (createBtn) {
                createBtn.addEventListener('click', function() {
                    var name = prompt(getI18n('shelfCreatePrompt', 'Enter shelf name:'));
                    if (name && name.trim()) {
                        if (ShelfManager.createShelf(name.trim())) {
                            ShelfManager.setActiveShelf(name.trim());
                            ShelfManager.refreshShelfPage();
                            ShelfManager.updateUI();
                        } else {
                            alert(getI18n('shelfExistsError', 'Shelf already exists or could not be created.'));
                        }
                    }
                });
            }

            var deleteBtn = document.getElementById('shelf-delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    var activeName = ShelfManager.getActiveShelfName();
                    if (confirm(getI18n('shelfDeleteConfirm', 'Delete shelf "') + activeName + '"?')) {
                        if (ShelfManager.deleteShelf(activeName)) {
                            ShelfManager.refreshShelfPage();
                            ShelfManager.updateUI();
                        } else {
                            alert(getI18n('shelfDeleteError', 'Cannot delete the last shelf.'));
                        }
                    }
                });
            }

            var renameBtn = document.getElementById('shelf-rename-btn');
            if (renameBtn) {
                renameBtn.addEventListener('click', function() {
                    var oldName = ShelfManager.getActiveShelfName();
                    var newName = prompt(getI18n('shelfRenamePrompt', 'Enter new shelf name:'), oldName);
                    if (newName && newName.trim() && newName.trim() !== oldName) {
                        if (ShelfManager.renameShelf(oldName, newName.trim())) {
                            ShelfManager.refreshShelfPage();
                            ShelfManager.updateUI();
                        } else {
                            alert(getI18n('shelfRenameError', 'Shelf name already exists or could not be renamed.'));
                        }
                    }
                });
            }

            var removeButtons = document.querySelectorAll('.shelf-remove-btn');
            for (var j = 0; j < removeButtons.length; j++) {
                (function(book) {
                    removeButtons[j].addEventListener('click', function() {
                        var shelfName = ShelfManager.getActiveShelfName();
                        ShelfManager.removeBook(shelfName, book);
                        ShelfManager.refreshShelfPage();
                        ShelfManager.updateUI();
                    });
                })({
                    db: removeButtons[j].getAttribute('data-book-db') || '',
                    id: removeButtons[j].getAttribute('data-book-id')
                });
            }
        };

        // Navigate to the dedicated shelves page
        ShelfManager.openShelvesPage = function() {
            if (typeof navigateTo === 'function' && typeof currentData !== 'undefined' && currentData.shelvesurl) {
                navigateTo(currentData.shelvesurl);
            }
        };
    }
});
