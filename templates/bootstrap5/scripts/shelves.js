/**
 * Bootstrap5 template - extends shared ShelfManager with template-specific UI
 * Uses shared ShelfManager from resources/shelves/shelves.js
 *
 * Shared functions (handleToggle, renderShelfPage, refreshShelfPage,
 * bindPageEvents, openShelvesPage) live in the core library.
 * This file only defines template-specific styling:
 * - updateBookButtons() - active-state CSS classes
 * - renderShelfContent() - Bootstrap 5 markup with bootstrap-icons
 * - ensureImportModal() - creates the import modal on demand (after AJAX navigation)
 * - showImportDialog() - import modal override (replace / new / merge choice)
 */
$(document).ready(function() {
    if (typeof ShelfManager !== 'undefined') {
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
                        btn.classList.remove('btn-outline-secondary');
                        btn.setAttribute('title', getI18n('shelfRemoveTitle', 'Remove from Shelf'));
                    } else {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-secondary');
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

            var html = '<div class="card">' +
                       '<div class="card-body">' +
                       '<div class="btn-group">' +
                       '<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">' +
                       ShelfManager.escapeHtml(activeShelf) + ' <span class="badge text-bg-secondary ms-1">' + books.length + '</span></button>' +
                       '<ul class="dropdown-menu" id="shelf-switcher">';

            for (var i = 0; i < shelfNames.length; i++) {
                var name = shelfNames[i];
                var activeClass = (name === activeShelf) ? ' active' : '';
                html += '<li><a class="dropdown-item' + activeClass + '" href="javascript:void(0);" data-shelf="' + ShelfManager.escapeHtml(name) + '">' + ShelfManager.escapeHtml(name) + '</a></li>';
            }

            html += '</ul>' +
                    '</div>' +
                    ' <button type="button" class="btn btn-success btn-sm" id="shelf-create-btn" title="' + getI18n('shelfCreatePrompt', 'Enter shelf name:') + '">' +
                    '<i class="bi bi-plus-lg pe-1"></i>' + getI18n('shelfNewTitle', 'New') + '</button> ';

            if (shelfNames.length > 1) {
                html += '<button type="button" class="btn btn-danger btn-sm" id="shelf-delete-btn" title="' + getI18n('shelfDeleteTitle', 'Delete') + '">' +
                        '<i class="bi bi-trash3 pe-1"></i>' + getI18n('shelfDeleteTitle', 'Delete') + '</button> ';
            }

            html += '<button type="button" class="btn btn-info btn-sm" id="shelf-rename-btn" title="' + getI18n('shelfRenameTitle', 'Rename') + '">' +
                    '<i class="bi bi-pencil pe-1"></i>' + getI18n('shelfRenameTitle', 'Rename') + '</button> ' +
                    '<button type="button" class="btn btn-success btn-sm" id="shelf-export-btn" title="' + getI18n('shelfExportTitle', 'Export') + '">' +
                    '<i class="bi bi-download pe-1"></i>' + getI18n('shelfExportTitle', 'Export') + '</button> ' +
                    '<button type="button" class="btn btn-info btn-sm" id="shelf-import-btn" title="' + getI18n('shelfImportTitle', 'Import') + '">' +
                    '<i class="bi bi-upload pe-1"></i>' + getI18n('shelfImportTitle', 'Import') + '</button> ' +
                    '<input type="file" id="shelf-import-file" style="display:none;" accept=".json">' +
                    '</div>' +
                    '</div>';

            // Book list
            html += '<h5 class="mt-2">' + getI18n('shelfBooksInTitle', 'Books in') + ' "' + ShelfManager.escapeHtml(activeShelf) + '"' +
                    ' <span class="badge text-bg-secondary">' + books.length + '</span></h5>';

            if (books.length === 0) {
                html += '<p class="text-muted">' + getI18n('shelfEmptyTitle', 'This shelf is empty. Browse books and add them using the bookmark button.') + '</p>';
            } else {
                html += '<div class="row row-cols-2 row-cols-md-5 g-0" id="shelf-book-list">';
                for (var j = 0; j < books.length; j++) {
                    var book = books[j];
                    html += '<div class="card" data-book-id="' + ShelfManager.escapeHtml(book.id) + '">';
                    if (book.thumbnailurl) {
                        html += '<div class="cover-image">' +
                                '<a href="' + ShelfManager.escapeHtml(book.detailurl || '#') + '">' +
                                '<img class="card-image-top" src="' + ShelfManager.escapeHtml(book.thumbnailurl) + '" alt="' + ShelfManager.escapeHtml(book.title) + '">' +
                                '</a>' +
                                '</div>';
                    }
                    html += '<div class="card-body meta">' +
                            '<h5 class="card-title title text-truncate"><a href="' + ShelfManager.escapeHtml(book.detailurl || '#') + '">' + ShelfManager.escapeHtml(book.title) + '</a></h5>' +
                            '<div class="card-subtitle author text-truncate">' + ShelfManager.escapeHtml(book.author || getI18n('shelfUnknownAuthor', 'Unknown')) + '</div>';

                    if (book.seriesName) {
                        html += '<div class="series text-truncate"><em>' + ShelfManager.escapeHtml(book.seriesName) + ' #' + ShelfManager.escapeHtml(book.seriesIndex || '') + '</em></div>';
                    }

                    html += '</div>' +
                            '<div class="card-footer text-center">' +
                            '<button type="button" class="btn btn-danger btn-sm shelf-remove-btn" data-book-db="' + ShelfManager.escapeHtml(book.db) + '" data-book-id="' + ShelfManager.escapeHtml(book.id) + '" title="' + getI18n('shelfRemoveTitle', 'Remove from Shelf') + '">' +
                            '<i class="bi bi-x-lg pe-1"></i>' + getI18n('shelfRemoveAlt', 'Remove') + '</button>' +
                            '</div>' +
                            '</div>';
                }
                html += '</div>';
            }

            return html;
        };

        // Override showImportDialog with modal version
        function ensureImportModal() {
            if ($('#shelf-import-modal').length > 0) {
                return;
            }
            var modalHtml = '<div class="modal fade" id="shelf-import-modal" tabindex="-1" aria-hidden="true">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h5 class="modal-title">' + getI18n('shelfImportTitle', 'Import') + '</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p>' + getI18n('shelfImportChoose', 'Choose import option:') + '</p>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="shelf-import-mode" value="replace" checked>' +
                '<label class="form-check-label">' + getI18n('shelfImportReplace', 'Replace current active shelf') + '</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="shelf-import-mode" value="new">' +
                '<label class="form-check-label">' + getI18n('shelfImportNew', 'Create new shelf "Imported"') + '</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="shelf-import-mode" value="merge">' +
                '<label class="form-check-label">' + getI18n('shelfImportMerge', 'Merge into current active shelf') + '</label>' +
                '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + getI18n('shelfImportCancel', 'Cancel') + '</button>' +
                '<button type="button" class="btn btn-primary" id="shelf-import-confirm">' + getI18n('shelfImportConfirm', 'Import') + '</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            if (document.body) {
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = modalHtml;
                document.body.appendChild(tempDiv.firstChild);
            }
        }
        ShelfManager.showImportDialog = function(jsonString, callback) {
            ensureImportModal();
            var modalEl = document.getElementById('shelf-import-modal');
            if (!modalEl) {
                callback('replace');
                return;
            }
            var modal = $(modalEl);
            var confirmBtn = $('#shelf-import-confirm');
            var radios = modal.find('input[name="shelf-import-mode"]');
            radios.prop('checked', false);
            radios.filter('[value="replace"]').prop('checked', true);
            confirmBtn.off('click').on('click', function() {
                var mode = radios.filter(':checked').val() || 'replace';
                // Bootstrap 5: vanilla API, no jQuery .modal()
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                callback(mode);
            });
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        };
    }
});
