function postRefresh()
{
    $('[data-toggle="tooltip"]').tooltip();
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();

    var gallery = document.getElementById('image-gallery');
    if (gallery) {
        $("#image-gallery").magnificPopup({
            delegate: '.image-item', // child items selector
            type: 'image',
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
    }
}

function getComicImages(url) {
    $.getJSON(url, function(data) {
        var gallery = document.getElementById('image-gallery');
        if (gallery && data) {
            for (var i in data) {
                var item = data[i];
                console.log('item: ' + JSON.stringify(item, null, 4));
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
    }).fail(function (error) {
        if (error.responseText) {
            document.write (error.responseText);
        } else {
            document.write ('Error loading templates from directory "' + encodeURI(templates + '/' + theme) + '/" for url: ' + encodeURI(url));
        }
        console.log('getJSON failed: ' + JSON.stringify(error, null, 4));
    });
}
