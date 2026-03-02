// util.js
// copyright Sébastien Lucas
// https://github.com/seblucas/cops

/*jshint curly: true, latedef: true, trailing: true, noarg: true, undef: true, browser: true, jquery: true, unused: true, devel: true, loopfunc: true */
/*global LRUCache, doT, Bloodhound, postRefresh */

var templatePage, templateBookDetail, templateMain, templateSuggestion, currentData, before, filterList;

var CLEAR_FILTER_ID = "_CLEAR_";

var COOKIE_LIFETIME = 400; // Set the maximum time cookies should be stored in days - most browsers now limit this to 400 days

if (typeof LRUCache === 'undefined') {
    console.log('ERROR: LRUCache module not loaded!');
}
var cache = new LRUCache(30);

$.ajaxSetup({
    cache: false
});

if (typeof Bloodhound === 'undefined') {
    console.log('INFO: Bloodhound module not loaded!');
} else {
    var copsTypeahead = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit: 30,
        remote: {
                    //url: 'getJSON.php?page=query&search=1&db=%DB&vl=%VL&query=%QUERY',
                    url: 'index.php?page=query&search=1&db=%DB&vl=%VL&query=%QUERY',
                    replace: function (url, query) {
                        //url = url.replace('getJSON.php', currentData.baseurl.replace('index.php', 'getJSON.php'));
                        url = url.replace('index.php', currentData.baseurl);
                        if (currentData.libraryId) {
                            url = url.replace('%VL', encodeURIComponent(currentData.libraryId));
                        } else {
                            url = url.replace('&vl=%VL', "");
                        }
                        if (currentData.multipleDatabase === 1 && currentData.databaseId === "") {
                            return url.replace('%QUERY', query).replace('&db=%DB', "");
                        }
                        return url.replace('%QUERY', query).replace('%DB', currentData.databaseId);
                    }
                }
    });

    copsTypeahead.initialize();
}

var DEBUG = false;
var isPushStateEnabled = window.history && window.history.pushState && window.history.replaceState &&
  // pushState isn't reliable on iOS until 5.
  !window.navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);

function debug_log(text) {
    if ( DEBUG ) {
        console.log(text);
    }
}

/*exported updateCookie */
function updateCookie (id) {
    if ($(id).prop('pattern') && !$(id).val().match(new RegExp ($(id).prop('pattern')))) {
        return;
    }
    var name = $(id).attr('id');
    var value = $(id).val ();
    Cookies.set(name, value, { expires: COOKIE_LIFETIME });
}

/*exported updateCookieFromCheckbox */
function updateCookieFromCheckbox (id) {
    var name = $(id).attr('id');
    if (name.indexOf('-') !== -1) { // Replaced includes with indexOf
        var nameArray = name.split('-');
        name = nameArray[0];
    }
    if ($(id).is(":checked"))
    {
        if ($(id).is(':radio')) {
            Cookies.set(name, $(id).val (), { expires: COOKIE_LIFETIME });
        } else {
            Cookies.set(name, '1', { expires: COOKIE_LIFETIME });
        }
    }
    else
    {
        Cookies.set(name, '0', { expires: COOKIE_LIFETIME });
    }
}

/*exported updateCookieFromCheckboxGroup */
function updateCookieFromCheckboxGroup (id) {
    var name = $(id).attr('name');
    var idBase = name.replace (/\[\]/, "");
    var group = [];
    $(':checkbox[name="' + name + '"]:checked').each (function () {
        var id = $(this).attr("id");
        group.push (id.replace (idBase + "_", ""));
    });
    Cookies.set(idBase, group.join (), { expires: COOKIE_LIFETIME });
}


function elapsed () {
    var elapsedTime = new Date () - before;
    return "Elapsed : " + elapsedTime;
}

function retourMail(data) {
    $("#mailButton :first-child").removeClass ("fas fa-spinner fa-pulse").addClass ("fas fa-envelope");
    if (typeof data === 'string') {
        alert ("Result: " + data);
    } else {
        alert ("Result: " + JSON.stringify(data, null, 4));
    }
}

function errorMail(data) {
    $("#mailButton :first-child").removeClass ("fas fa-spinner fa-pulse").addClass ("fas fa-envelope");
    if (typeof data === 'string') {
        alert ("Error: " + data);
    } else {
        alert ("Error: " + JSON.stringify(data, null, 4));
    }
}

/*exported sendToMailAddress */
function sendToMailAddress (component, dataid) {
    var email = Cookies.get('email');
    if (!Cookies.get('email')) {
        email = window.prompt (currentData.c.i18n.customizeEmail, "");
        if (email === null)
        {
            return;
        }
        Cookies.set('email', email, { expires: COOKIE_LIFETIME });
    }
    // fix https://github.com/dunxd/HomeAssistantAddons/issues/90
    var url = currentData.baseurl;
    if (url.indexOf('index.php') !== -1) {
        url = url + '/mail';
    } else {
        url = url + 'index.php/mail';
    }
    if (currentData.databaseId) {
        url = url + '?db=' + currentData.databaseId;
    }
    $("#mailButton :first-child").removeClass ("fas fa-envelope").addClass ("fas fa-spinner fa-pulse");
    $.ajax ({'url': url, 'type': 'post', 'data': { 'data':  dataid, 'email': email }, 'success': retourMail, 'error': errorMail});
}

/*exported showQRCode */
function showQRCode (id, link) {
    var qr = document.getElementById(id);
    if (qr === null) {
        return;
    }
    // clear qr code if already set
    if (qr.innerHTML !== "") {
        qr.innerHTML = "";
        return;
    }
    // use dynamic script loading here
    if (link.indexOf('://') !== -1) { // Replaced includes with indexOf
        var el = document.createElement('script');
        el.type = 'text/javascript';
        el.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        el.onload = function() {
            new QRCode(qr, {
                text: link,
                correctLevel: QRCode.CorrectLevel.M
            });
        }
        qr.appendChild(el);
    }
}

/*exported asset */
function asset (file) {
    var url = currentData.assets + '/' + file;
    if (currentData && currentData.version) {
        url = url + '?v=' + currentData.version;
    }
    return url;
}

function str_format () {
    var s = arguments[0];
    if (typeof s === 'undefined') {
        return '';
    }
    for (var i = 0; i < arguments.length - 1; i++) {
        var reg = new RegExp("\\{" + i + "\\}", "gm");
        s = s.replace(reg, arguments[i + 1]);
    }
    return s;
}

function isDefined(x) {
    return (typeof x !== 'undefined');
}

function getCurrentOption (option) {
    if (!Cookies.get(option)) {
        if (currentData && currentData.c && currentData.c.config && currentData.c.config [option]) {
            return currentData.c.config [option];
        }
    }
    return Cookies.get(option);
}

/*exported htmlspecialchars */
function htmlspecialchars(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}

/************************************************
 * All functions needed to filter the book list by tags
 ************************************************
 */

function getTagList () {
    var tagList = {};
    $(".se").each (function(){
        if ($(this).parents (".filtered").length > 0) { return; }
        var tagtext = $(this).text();

        var tagarray = tagtext.split (",");
        for (var i in tagarray) {
            if (!tagarray.hasOwnProperty(i)) {
                continue;
            }
            var tag = tagarray [i].replace(/^\s+/g,'').replace(/\s+$/g,'');
            tagList [tag] = 1;
        }
    });
    return tagList;
}

function updateFilters () {
    var tagList = getTagList ();

    // If there is already some filters then let's prepare to update the list
    $("#filter ul li").each (function () {
        var text = $(this).text ();
        if (isDefined (tagList [text]) || $(this).attr ('class')) {
            tagList [text] = 0;
        } else {
            tagList [text] = -1;
        }
    });

    // Update the filter -1 to remove, 1 to add, 0 already there
    for (var tag in tagList) {
        if (!tagList.hasOwnProperty(tag)) {
            continue;
        }
        var tagValue = tagList [tag];
        if (tagValue === -1) {
            $("#filter ul li").filter (function () { return $.text([this]) === tag; }).remove();
        }
        if (tagValue === 1) {
            $("#filter ul").append ("<li>" + tag + "</li>");
        }
    }

    $("#filter ul").append ("<li id='" + CLEAR_FILTER_ID + "'>" + currentData.c.i18n.filterClearAll + "</li>");

    // Sort the list alphabetically
    $('#filter ul li').sortElements(function(a, b){
        if (a.id === CLEAR_FILTER_ID) {
            return 1;
        }
        if (b.id === CLEAR_FILTER_ID) {
            return -1;
        }
        return $(a).text() > $(b).text() ? 1 : -1;
    });
}

function doFilter () {
    $(".books").removeClass("filtered");
    if (jQuery.isEmptyObject(filterList)) {
        updateFilters ();
        return;
    }

    $(".se").each (function(){
        var tagtext = ", " + $(this).text() + ", ";
        var toBeFiltered = false;
        for (var filter in filterList) {
            if (!filterList.hasOwnProperty(filter)) {
                continue;
            }
            var onlyThisTag = filterList [filter];
            filter = ', ' + filter + ', ';
            var myreg = new RegExp (filter);
            if (myreg.test (tagtext)) {
                if (onlyThisTag === false) {
                    toBeFiltered = true;
                }
            } else {
                if (onlyThisTag === true) {
                    toBeFiltered = true;
                }
            }
        }
        if (toBeFiltered) { $(this).parents (".books").addClass ("filtered"); }
    });

    // Handle the books with no tags
    var atLeastOneTagSelected = false;
    for (var filter in filterList) {
        if (!filterList.hasOwnProperty(filter)) {
            continue;
        }
        if (filterList[filter] === true) {
            atLeastOneTagSelected = true;
        }
    }
    if (atLeastOneTagSelected) {
        $(".books").not (":has(span.se)").addClass ("filtered");
    }

    updateFilters ();
}

function handleFilterEvents () {
    $("#filter ul").on ("click", "li", function(){
        var filter = $(this).text ();
        var filterId = this.id;
        console.log(filter, filterId);
        if (filterId === CLEAR_FILTER_ID) {
            filterList = {};
            $("#filter ul li").removeClass ("filter-exclude");
            $("#filter ul li").removeClass ("filter-include");
            doFilter ();
            return;
        }
        switch ($(this).attr("class")) {
            case "filter-include" :
                $(this).attr("class", "filter-exclude");
                filterList [filter] = false;
                break;
            case "filter-exclude" :
                $(this).removeClass ("filter-exclude");
                delete filterList [filter];
                break;
            default :
                $(this).attr("class", "filter-include");
                filterList [filter] = true;
                break;
        }
        doFilter ();
    });
}

/************************************************
 * Functions to handle Ajax navigation
 ************************************************
 */

var updatePage, navigateTo;

updatePage = function (data) {
    var result;
    filterList = {};
    data.c = currentData.c;
    if (false && $("section").length && currentData.isPaginated === 0 &&  data.isPaginated === 0) {
        // Partial update (for now disabled)
        debug_log ("Partial update");
        result = templateMain (data);
        $("h1").html (data.title);
        $("section").html (result);
    } else {
        // Full update
        result = templatePage (data);
        $("body").html (result);
    }
    if (data.title != data.libraryName) {
        if (data.libraryId) {
            document.title = data.libraryName + ' ' + data.libraryId + ' - ' + data.title;
        } else {
            document.title = data.libraryName + ' - ' + data.title;
        }
    } else if (data.libraryId) {
        document.title = data.libraryId + ' - ' + data.title;
    } else {
        document.title = data.title;
    }
    currentData = data;

    debug_log (elapsed ());

    if (Cookies.get('toolbar') === '1') { $("#tool").show (); }
    if (currentData.containsBook === 1) {
        $("#sortForm").show ();
        // disable html tag filter when dealing with hierarchical tags or custom columns
        if (getCurrentOption ("html_tag_filter") === "1" && !currentData.hierarchy) {
            $("#filter ul").empty ();
            updateFilters ();
            handleFilterEvents ();
        }
    } else {
        $("#sortForm").hide ();
    }

    $('input[name=query]').typeahead(
    {
        hint: true,
        minLength : 3
    },
    {
        name: 'search',
        displayKey: 'title',
        templates: {
            suggestion: templateSuggestion
        },
        source: copsTypeahead.ttAdapter()
    });

    $('input[name=query]').on('typeahead:selected', function(obj, datum) {
        if (isPushStateEnabled) {
            navigateTo (datum.navlink);
        } else {
            window.location = datum.navlink;
        }
    });

    if(typeof postRefresh == 'function')
    { postRefresh(); }
};

navigateTo = function (url) {
    $("h1").append (" <i class='fas fa-spinner fa-pulse'></i>");
    before = new Date ();
    //var jsonurl = url.replace ("index.php", "getJSON.php");
    var jsonurl = url;
    var cachedData = cache.get (jsonurl);
    if (cachedData) {
        window.history.pushState(jsonurl, "", url);
        updatePage (cachedData);
    } else {
        $.getJSON(jsonurl, function(data, status, xhr) {
            // handle redirected json requests - see CalibreHandler
            if (typeof xhr !== undefined) {
                var redirected = xhr.getResponseHeader('X-Response-Url');
                if (redirected) {
                    url = redirected;
                }
            }
            window.history.pushState(jsonurl, "", url);
            cache.put (jsonurl, data);
            updatePage (data);
        }).fail(function (error) {
            window.history.pushState(jsonurl, "", url);
            if (error.responseText) {
                $("#error").html (error.responseText);
            } else {
                $("#error").html ('Error loading url: ' + encodeURI(jsonurl));
            }
            console.log('getJSON failed: ' + JSON.stringify(error, null, 4));
        });
    }
};

function link_Clicked (event) {
    var currentLink = $(this);
    if (!isPushStateEnabled ||
        currentData.page === "customize") {
        return;
    }
    // let read, fetch, zippper etc. do their thing
    var url = currentLink.attr('href');
    if (url.indexOf('/read/') !== -1 || // Replaced includes with indexOf
        url.indexOf('/fetch/') !== -1 ||
        url.indexOf('/zipper/') !== -1 ||
        url.indexOf('/covers/') !== -1 ||
        url.indexOf('/files/') !== -1) {
        return;
    }
    event.preventDefault();

    if ($(".mfp-ready").length)
    {
        $.magnificPopup.close();
    }

    // The bookdetail / about should be displayed in a lightbox
    if (getCurrentOption ("use_fancyapps") === "1" &&
        (currentLink.hasClass ("fancydetail") || currentLink.hasClass ("fancyabout"))) {
        before = new Date ();
        var jsonurl = url;
        $.getJSON(jsonurl, function(data) {
            data.c = currentData.c;
            var detail = "";
            if (data.page === "about") {
                detail = data.fullhtml;
            } else {
                detail = templateBookDetail (data);
            }
            $.magnificPopup.open({
              items: {
                src: detail,
                type: 'inline'
              }
            });
            debug_log (elapsed ());
        });
        return;
    }
    navigateTo (url);
}

function search_Submitted (event) {
    if (!isPushStateEnabled ||
        currentData.page === "customize") {
        return;
    }
    event.preventDefault();
    var url = str_format (currentData.baseurl + "?page=query&current={0}&query={1}&db={2}", currentData.page, encodeURIComponent ($("input[name=query]").val ()), currentData.databaseId);
    if (currentData.libraryId) {
        url = url + '&vl=' + encodeURIComponent(currentData.libraryId);
    }
    navigateTo (url);
}

/*exported handleLinks */
function handleLinks () {
    if (currentData && currentData.baseurl) {
        $("body").on ("click", "a[href^='" + currentData.baseurl + "']", link_Clicked);
    } else {
        $("body").on ("click", "a[href^='index']", link_Clicked);
    }
    $("body").on ("submit", "#searchForm", search_Submitted);
    $("body").on ("click", "#sort", function(){
        $('.books').sortElements(function(a, b){
            var test = 1;
            if ($("#sortorder").val() === "desc")
            {
                test = -1;
            }
            return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
        });
    });

    $("body").on ("click", ".headright", function(){
        if ($("#tool").is(":hidden")) {
            $("#tool").slideDown("slow");
            Cookies.set('toolbar', '1', { expires: COOKIE_LIFETIME });
        } else {
            $("#tool").slideUp();
            Cookies.remove('toolbar');
        }
    });
    $("body").magnificPopup({
        delegate: '.fancycover', // child items selector, by clicking on it popup will open
        type: 'image',
        gallery:{enabled:true, preload: [0,2]},
        disableOn: function() {
          if( getCurrentOption ("use_fancyapps") === "1" ) {
            return true;
          }
          return false;
        }
    });
}

window.onpopstate = function(event) {
    if (!isDefined (currentData)) {
        return;
    }

    before = new Date ();
    var data = cache.get (event.state);
    updatePage (data);
};

$(document).on("keydown", function(e){
    if (e.which === 37 && $("#prevLink").length > 0) {
        navigateTo ($("#prevLink").attr('href'));
    }
    if (e.which === 39  && $("#nextLink").length > 0) {
        navigateTo ($("#nextLink").attr('href'));
    }
});

/*exported initiateAjax */
function initiateAjax (url, theme, templates, version) {
    templates = typeof templates !== 'undefined' ? templates : 'templates';
    version = typeof version !== 'undefined' ? version : '4.1.0';
    // allow caching to get() template files here, but not for getJSON()
    $.when($.get({url: templates + '/' + theme + '/header.html?v=' + version, cache: true}),
           $.get({url: templates + '/' + theme + '/footer.html?v=' + version, cache: true}),
           $.get({url: templates + '/' + theme + '/bookdetail.html?v=' + version, cache: true}),
           $.get({url: templates + '/' + theme + '/main.html?v=' + version, cache: true}),
           $.get({url: templates + '/' + theme + '/page.html?v=' + version, cache: true}),
           $.get({url: templates + '/' + theme + '/suggestion.html?v=' + version, cache: true}),
           $.getJSON(url)).done(function(header, footer, bookdetail, main, page, suggestion, data){
        templateBookDetail = doT.template (bookdetail [0]);

        var defMain = {
            bookdetail: bookdetail [0]
        };

        templateMain = doT.template (main [0], undefined, defMain);

        var defPage = {
            header: header [0],
            footer: footer [0],
            main  : main [0],
            bookdetail: bookdetail [0]
        };

        templatePage = doT.template (page [0], undefined, defPage);

        templateSuggestion = doT.template (suggestion [0]);

        currentData = data [0];

        updatePage (data [0]);
        cache.put (url, data [0]);
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

/** Moved initiateTwig from util.js to twigged cops.js due to unknown issue with Kindle - see #36 */
