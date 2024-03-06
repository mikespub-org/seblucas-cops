function postRefresh()
{
    $('[data-toggle="tooltip"]').tooltip();
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();
}

function initiateTwig(url, theme, templates = 'templates') {
    Twig.extendFunction("str_format", str_format);
    Twig.extendFunction("asset", asset);

    let template = Twig.twig({
        id: 'page',
        href: templates + '/' + theme + '/page.html',
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
        document.write (error.responseText);
        console.log('getJSON failed: ' + JSON.stringify(error, null, 4));
    });
}
