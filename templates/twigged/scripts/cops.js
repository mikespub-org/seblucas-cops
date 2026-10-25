function postRefresh()
{
    $('[data-toggle="tooltip"]').tooltip();
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();
    
    // Update shelf UI
    if (typeof ShelfManager !== 'undefined') {
        if (typeof ShelfManager.updateUI === 'function') {
            ShelfManager.updateUI();
        }
        if (typeof ShelfManager.renderShelfPage === 'function') {
            ShelfManager.renderShelfPage();
        }
        if (typeof ShelfManager.handleSharedShelf === 'function') {
            ShelfManager.handleSharedShelf();
        }
    }

    var gallery = document.getElementById('image-gallery');
    if (gallery) {
        $("#image-gallery").magnificPopup({
            delegate: '.image-item', // child items selector
            type: 'image',
            mainClass: 'cops-full-height',
            gallery: {
                enabled: true,
                preload: [0,2]
            },
            showCloseBtn: false
        });
        $("body").on ("click", "#open-gallery", function(event) {
            event.preventDefault();
            $("#image-gallery").magnificPopup('open');
        });
    } else {
        $("body").off ("click", "#open-gallery");
    }
}

// use namespaced event here
$(document).off ("submit.serverShelves", ".server-shelf-toggle-form").on ("submit.serverShelves", ".server-shelf-toggle-form", function(e) {
    e.preventDefault();
    var form = this;
    $.ajax ({
        url      : form.action,
        type     : 'post',
        data     : $(form).serialize(),
        headers  : {
            'Accept'               : 'application/json',
            'X-Requested-With'     : 'XMLHttpRequest'
        },
        success  : function (result) {
            if (!result || !result.success) {
                return;
            }

            var inShelf = !!result.inShelf;
            var button = $(form).find('button[type="submit"]');
            button.attr('aria-pressed', inShelf ? 'true' : 'false');
            button.removeClass('btn-danger btn-default').addClass(inShelf ? 'btn-danger' : 'btn-default');
            button.attr('title', getI18n(inShelf ? 'shelfRemoveTitle' : 'shelfAddTitle', inShelf ? 'Remove from Shelf' : 'Add to Shelf'));
            button.find('.sr-only').text(getI18n(inShelf ? 'shelfRemoveTitle' : 'shelfAddTitle', inShelf ? 'Remove from Shelf' : 'Add to Shelf'));

            if (!inShelf && $(form).attr('data-remove-on-toggle') === 'true') {
                var item = $(form).closest('.server-shelf-item');
                item.remove();
                var count = parseInt($('#server-shelf-total').text(), 10) || 0;
                $('#server-shelf-total').text(Math.max(0, count - 1));
            }
        },
        error    : function () {
            // Silent failure: if the toggle request fails, the button state is left unchanged.
        }
    });
});

// Enhance the server shelf selector: navigate via AJAX when the selected shelf changes
$(document).off("change.serverShelves", "#server-shelf-select").on("change.serverShelves", "#server-shelf-select", function () {
    if (typeof currentData === 'undefined' || !currentData.shelvesurl || typeof navigateTo !== 'function') {
        return;
    }
    navigateTo(currentData.shelvesurl + '?shelf=' + encodeURIComponent($(this).val()));
});

// Copy shared shelf-name input into the hidden field of create/rename forms before submit
function copyShelfName(targetId, form) {
    var source = document.getElementById('shelf-name-input');
    var target = document.getElementById(targetId);
    if (source && target) {
        target.value = source.value;
        if (!target.value) {
            return false;
        }
    }
    return true;
}

function getComicImages(url) {
    $.getJSON(url, function(data) {
        var gallery = document.getElementById('image-gallery');
        if (gallery && data) {
            for (var i in data) {
                var item = data[i];
                var link = document.createElement('a');
                link.href = item.href;
                // link.title = item.name;
                link.className = 'image-item';
                gallery.appendChild(link);
            }
        }
    }).fail(function (error) {
        console.log('getJSON failed: ' + JSON.stringify(error, null, 4));
    });
}

// Refactored to replace ES6+ features with ES5-compatible syntax
function initiateTwig(url, theme, templates, version) {
    templates = typeof templates !== 'undefined' ? templates : 'templates';
    Twig.extendFunction("str_format", str_format);
    Twig.extendFunction("asset", asset);

    var template = Twig.twig({
        id: 'page',
        href: templates + '/' + theme + '/page.html?v=' + version,
        async: false
     });

     templatePage = function (data) {
        return Twig.twig({ref: 'page'}).render({it: data});
     };

     $.when($.getJSON(url)).done(function(data){
        currentData = data;

        updatePage (currentData);
        cache.put (url, currentData);
        if (isPushStateEnabled) {
            window.history.replaceState(url, "", window.location);
        }
        handleLinks ();
        
        // Initialize shelves
        if (typeof ShelfManager !== 'undefined' && typeof ShelfManager.init === 'function') {
            ShelfManager.init();
            ShelfManager.updateUI();
            if (typeof ShelfManager.renderShelfPage === 'function') {
                ShelfManager.renderShelfPage();
            }
            if (typeof ShelfManager.handleSharedShelf === 'function') {
                ShelfManager.handleSharedShelf();
            }
        }
    }).fail(function (error) {
        if (error.responseText) {
            document.write (error.responseText);
        } else {
            document.write ('Error loading templates from directory "' + encodeURI(templates + '/' + theme) + '/" for url: ' + encodeURI(url));
        }
        console.log('getJSON failed: ' + JSON.stringify(error, null, 4));
    });
}
