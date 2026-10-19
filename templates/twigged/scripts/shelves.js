/**
 * Twigged template - extends shared ShelfManager with template-specific UI
 * Uses shared ShelfManager from resources/shelves/shelves.js
 *
 * Shared functions (handleToggle, renderShelfPage, refreshShelfPage,
 * bindPageEvents, openShelvesPage) live in the core library.
 * This file only defines template-specific styling:
 * - updateBookButtons() - active-state CSS classes
 * - renderShelfContent() - Bootstrap 3 markup with glyphicons
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
                    '<span class="glyphicon glyphicon-pencil"></span> ' + getI18n('shelfRenameTitle', 'Rename') + '</button> ' +
                    '<button type="button" class="btn btn-success btn-sm" id="shelf-export-btn" title="' + getI18n('shelfExportTitle', 'Export') + '">' +
                    '<span class="glyphicon glyphicon-download"></span> ' + getI18n('shelfExportTitle', 'Export') + '</button> ' +
                    '<button type="button" class="btn btn-info btn-sm" id="shelf-import-btn" title="' + getI18n('shelfImportTitle', 'Import') + '">' +
                    '<span class="glyphicon glyphicon-upload"></span> ' + getI18n('shelfImportTitle', 'Import') + '</button> ' +
                    '<input type="file" id="shelf-import-file" style="display:none;" accept=".json">' +
                    '</div>' +
                    '</div>';

            // Book list
            html += '<h4>' + str_format(getI18n('shelfBooksInTitle', 'Books in "{0}"'), ShelfManager.escapeHtml(activeShelf)) +
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

        // Override showImportDialog with modal version
        function ensureImportModal() {
            if ($('#shelf-import-modal').length > 0) {
                return;
            }
            var modalHtml = '<div class="modal fade" id="shelf-import-modal" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                '<h4 class="modal-title">' + getI18n('shelfImportTitle', 'Import') + '</h4>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p>' + getI18n('shelfImportChoose', 'Choose import option:') + '</p>' +
                '<div class="radio">' +
                '<label><input type="radio" name="shelf-import-mode" value="replace" checked> ' + getI18n('shelfImportReplace', 'Replace current active shelf') + '</label>' +
                '</div>' +
                '<div class="radio">' +
                '<label><input type="radio" name="shelf-import-mode" value="new"> ' + getI18n('shelfImportNew', 'Create new shelf "Imported"') + '</label>' +
                '</div>' +
                '<div class="radio">' +
                '<label><input type="radio" name="shelf-import-mode" value="merge"> ' + getI18n('shelfImportMerge', 'Merge into current active shelf') + '</label>' +
                '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal">' + getI18n('shelfImportCancel', 'Cancel') + '</button>' +
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
            var modal = $('#shelf-import-modal');
            var confirmBtn = $('#shelf-import-confirm');
            var radios = modal.find('input[name="shelf-import-mode"]');
            radios.prop('checked', false);
            radios.filter('[value="replace"]').prop('checked', true);
            confirmBtn.off('click').on('click', function() {
                var mode = radios.filter(':checked').val() || 'replace';
                modal.modal('hide');
                callback(mode);
            });
            modal.modal('show');
        };
    }
});
