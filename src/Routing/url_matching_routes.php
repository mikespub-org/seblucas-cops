<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/index' => [[['_route' => 'page-index', 'page' => 'index'], null, ['GET' => 0], null, false, false, null]],
        '/authors/letter' => [[['_route' => 'page-authors-letters', 'page' => 'authors', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/authors' => [[['_route' => 'page-authors', 'page' => 'authors'], null, ['GET' => 0], null, false, false, null]],
        '/books/letter' => [[['_route' => 'page-books-letters', 'page' => 'books', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/books/year' => [[['_route' => 'page-books-years', 'page' => 'books', 'year' => 1], null, ['GET' => 0], null, false, false, null]],
        '/books' => [[['_route' => 'page-books', 'page' => 'books'], null, ['GET' => 0], null, false, false, null]],
        '/series/letter' => [[['_route' => 'page-series-letters', 'page' => 'series', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/series' => [[['_route' => 'page-series', 'page' => 'series'], null, ['GET' => 0], null, false, false, null]],
        '/typeahead' => [[['_route' => 'page-typeahead', 'page' => 'query', 'search' => 1], null, ['GET' => 0], null, false, false, null]],
        '/search' => [[['_route' => 'page-search', 'page' => 'opensearch'], null, ['GET' => 0], null, false, false, null]],
        '/recent' => [[['_route' => 'page-recent', 'page' => 'recent'], null, ['GET' => 0], null, false, false, null]],
        '/tags/letter' => [[['_route' => 'page-tags-letters', 'page' => 'tags', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/tags' => [[['_route' => 'page-tags', 'page' => 'tags'], null, ['GET' => 0], null, false, false, null]],
        '/about' => [[['_route' => 'page-about', 'page' => 'about'], null, ['GET' => 0], null, false, false, null]],
        '/languages' => [[['_route' => 'page-languages', 'page' => 'languages'], null, ['GET' => 0], null, false, false, null]],
        '/customize' => [[['_route' => 'page-customize', 'page' => 'customize'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/publishers/letter' => [[['_route' => 'page-publishers-letters', 'page' => 'publishers', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/publishers' => [[['_route' => 'page-publishers', 'page' => 'publishers'], null, ['GET' => 0], null, false, false, null]],
        '/ratings' => [[['_route' => 'page-ratings', 'page' => 'ratings'], null, ['GET' => 0], null, false, false, null]],
        '/identifiers' => [[['_route' => 'page-identifiers', 'page' => 'identifiers'], null, ['GET' => 0], null, false, false, null]],
        '/formats' => [[['_route' => 'page-formats', 'page' => 'formats'], null, ['GET' => 0], null, false, false, null]],
        '/libraries' => [[['_route' => 'page-libraries', 'page' => 'libraries'], null, ['GET' => 0], null, false, false, null]],
        '/filter' => [[['_route' => 'page-filter', 'page' => 'filter'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/feed/search' => [[['_route' => 'feed-search', 'page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], null, ['GET' => 0], null, false, false, null]],
        '/feed' => [[['_route' => 'feed', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/custom' => [[['_route' => 'restapi-customtypes', '_resource' => 'CustomColumnType', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/databases' => [[['_route' => 'restapi-databases', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/openapi' => [[['_route' => 'restapi-openapi', '_resource' => 'openapi', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/routes' => [[['_route' => 'restapi-route', '_resource' => 'route', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/handlers' => [[['_route' => 'restapi-handler', '_resource' => 'handler', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/notes' => [[['_route' => 'restapi-notes', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/preferences' => [[['_route' => 'restapi-preferences', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/annotations' => [[['_route' => 'restapi-annotations', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user/details' => [[['_route' => 'restapi-user-details', '_resource' => 'User', 'details' => true, '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user' => [[['_route' => 'restapi-user', '_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/opds/search' => [[['_route' => 'opds-search', 'page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], null, ['GET' => 0], null, false, false, null]],
        '/loader' => [[['_route' => 'loader', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], null, ['GET' => 0], null, false, false, null]],
        '/mail' => [[['_route' => 'mail', '_handler' => 'SebLucas\\Cops\\Handlers\\MailHandler'], null, ['POST' => 0], null, false, false, null]],
        '/graphql' => [[['_route' => 'graphql', '_handler' => 'SebLucas\\Cops\\Handlers\\GraphQLHandler'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/tables' => [[['_route' => 'tables', '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], null, ['GET' => 0], null, false, false, null]],
        '/test' => [[['_route' => 'test', '_handler' => 'SebLucas\\Cops\\Handlers\\TestHandler'], null, ['GET' => 0], null, false, false, null]],
        '/admin/clearcache' => [[['_route' => 'admin-clearcache', 'action' => 'clearcache', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], null, ['GET' => 0], null, false, false, null]],
        '/admin/config' => [[['_route' => 'admin-config', 'action' => 'config', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/admin/checkbooks' => [[['_route' => 'admin-checkbooks', 'action' => 'checkbooks', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/a(?'
                    .'|uthors/(?'
                        .'|letter/([^/]++)(*:37)'
                        .'|(\\d+)/([^/]++)(*:58)'
                        .'|(\\d+)(*:70)'
                    .')'
                    .'|dmin(?'
                        .'|/(.*)(*:90)'
                        .'|(*:97)'
                    .')'
                .')'
                .'|/books/(?'
                    .'|letter/([^/]++)(*:131)'
                    .'|year/(\\d+)(*:149)'
                    .'|(\\d+)/([^/]++)/([^/]++)(*:180)'
                    .'|(\\d+)(*:193)'
                .')'
                .'|/se(?'
                    .'|ries/(?'
                        .'|letter/([^/]++)(*:231)'
                        .'|(\\d+)/([^/]++)(*:253)'
                        .'|(\\d+)(*:266)'
                    .')'
                    .'|arch/([^/]++)(?'
                        .'|/([^/]++)(*:300)'
                        .'|(*:308)'
                    .')'
                .')'
                .'|/f(?'
                    .'|o(?'
                        .'|lder/(.*)(*:336)'
                        .'|rmat(?'
                            .'|s/(\\w+)(*:358)'
                            .'|/(.+)(*:371)'
                        .')'
                    .')'
                    .'|e(?'
                        .'|ed/(?'
                            .'|(\\w+)(*:396)'
                            .'|(.+)(*:408)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:453)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:483)'
                .')'
                .'|/e(?'
                    .'|book/(.+)(*:506)'
                    .'|pubfs/(\\d+)/(\\d+)/(.+)(*:536)'
                .')'
                .'|/t(?'
                    .'|ags/(?'
                        .'|letter/([^/]++)(*:572)'
                        .'|(\\d+)/([^/]++)(*:594)'
                        .'|(\\d+)(*:607)'
                    .')'
                    .'|humbs/(\\d+)/(\\d+)/([^/\\.]++)\\.jpg(*:649)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:686)'
                        .'|(\\d+)(*:699)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:730)'
                    .'|heck(?'
                        .'|/(.*)(*:750)'
                        .'|(*:758)'
                    .')'
                    .'|al(?'
                        .'|ibre/([^/]++)/([^/]++)(?'
                            .'|/(.*)(*:802)'
                            .'|(*:810)'
                        .')'
                        .'|res/(\\d+)/([^/]++)/([^/]++)(*:846)'
                    .')'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:887)'
                        .'|(\\d+)(*:900)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:946)'
                            .'|(\\d+)/(\\w*)(*:965)'
                            .'|(\\d+)(*:978)'
                        .')'
                        .'|(*:987)'
                        .'|(*:995)'
                    .')'
                .')'
                .'|/publishers/(?'
                    .'|letter/([^/]++)(*:1035)'
                    .'|(\\d+)/([^/]++)(*:1058)'
                    .'|(\\d+)(*:1072)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:1111)'
                        .'|(\\d+)(*:1125)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:1165)'
                            .'|(\\d+)/(\\d+)(*:1185)'
                            .'|(.+)(*:1198)'
                        .')'
                        .'|stapi/(?'
                            .'|databases/([^/]++)(?'
                                .'|/([^/]++)(*:1247)'
                                .'|(*:1256)'
                            .')'
                            .'|notes/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1304)'
                                    .'|(*:1313)'
                                .')'
                                .'|(*:1323)'
                            .')'
                            .'|preferences/([^/]++)(*:1353)'
                            .'|annotations/([^/]++)(?'
                                .'|/([^/]++)(*:1394)'
                                .'|(*:1403)'
                            .')'
                            .'|metadata/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1454)'
                                    .'|(*:1463)'
                                .')'
                                .'|(*:1473)'
                            .')'
                            .'|folders/(.*)(*:1495)'
                            .'|(.*)(*:1508)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:1553)'
                        .'|(\\w+)(*:1567)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1615)'
                    .'|mages/([^/]++)/(.+)(*:1643)'
                .')'
                .'|/view/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1697)'
                .'|/download/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1754)'
                .'|/opds(?'
                    .'|/(?'
                        .'|(\\w+)(*:1780)'
                        .'|(.*)(*:1793)'
                    .')'
                    .'|(*:1803)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)/(?'
                        .'|([^/]++)/([^/\\.]++)\\.zip(*:1860)'
                        .'|([^/\\.]++)\\.zip(*:1884)'
                    .')'
                    .'|fs/(?'
                        .'|(\\d+)/(\\d+)/(.+)(*:1916)'
                        .'|(.+)(*:1929)'
                    .')'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        37 => [[['_route' => 'page-authors-letter', 'page' => 'authors_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        58 => [[['_route' => 'page-author', 'page' => 'author'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        70 => [[['_route' => 'page-author-id', 'page' => 'author'], ['id'], ['GET' => 0], null, false, true, null]],
        90 => [[['_route' => 'admin-action', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], ['action'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        97 => [[['_route' => 'admin', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], [], ['GET' => 0], null, false, false, null]],
        131 => [[['_route' => 'page-books-letter', 'page' => 'books_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        149 => [[['_route' => 'page-books-year', 'page' => 'books_year'], ['year'], ['GET' => 0], null, false, true, null]],
        180 => [[['_route' => 'page-book', 'page' => 'book'], ['id', 'author', 'title'], ['GET' => 0], null, false, true, null]],
        193 => [[['_route' => 'page-book-id', 'page' => 'book'], ['id'], ['GET' => 0], null, false, true, null]],
        231 => [[['_route' => 'page-series-letter', 'page' => 'series_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        253 => [[['_route' => 'page-serie', 'page' => 'serie'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        266 => [[['_route' => 'page-serie-id', 'page' => 'serie'], ['id'], ['GET' => 0], null, false, true, null]],
        300 => [[['_route' => 'page-query-scope', 'page' => 'query'], ['query', 'scope'], ['GET' => 0], null, false, true, null]],
        308 => [[['_route' => 'page-query', 'page' => 'query'], ['query'], ['GET' => 0], null, false, true, null]],
        336 => [[['_route' => 'page-folder', 'page' => 'folder'], ['path'], ['GET' => 0], null, false, true, null]],
        358 => [[['_route' => 'page-format', 'page' => 'format'], ['id'], ['GET' => 0], null, false, true, null]],
        371 => [[['_route' => 'fetch-format', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        396 => [[['_route' => 'feed-page', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        408 => [[['_route' => 'feed-path', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        453 => [[['_route' => 'fetch-data', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        483 => [[['_route' => 'fetch-file', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'file'], ['GET' => 0], null, false, true, null]],
        506 => [[['_route' => 'page-ebook', 'page' => 'folder', 'ebook' => 1], ['path'], ['GET' => 0], null, false, true, null]],
        536 => [[['_route' => 'epubfs', '_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        572 => [[['_route' => 'page-tags-letter', 'page' => 'tags_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        594 => [[['_route' => 'page-tag', 'page' => 'tag'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        607 => [[['_route' => 'page-tag-id', 'page' => 'tag'], ['id'], ['GET' => 0], null, false, true, null]],
        649 => [[['_route' => 'fetch-thumb', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'thumb'], ['GET' => 0], null, false, false, null]],
        686 => [[['_route' => 'page-custom', 'page' => 'custom'], ['custom', 'id'], ['GET' => 0], null, false, true, null]],
        699 => [[['_route' => 'page-customtype', 'page' => 'customtype'], ['custom'], ['GET' => 0], null, false, true, null]],
        730 => [[['_route' => 'fetch-cover', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id'], ['GET' => 0], null, false, false, null]],
        750 => [[['_route' => 'check-more', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], ['more'], ['GET' => 0], null, false, true, null]],
        758 => [[['_route' => 'check', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], [], ['GET' => 0], null, false, false, null]],
        802 => [[['_route' => 'calibre-details', '_handler' => 'SebLucas\\Cops\\Handlers\\CalibreHandler'], ['action', 'library', 'details'], ['GET' => 0], null, false, true, null]],
        810 => [[['_route' => 'calibre-library', '_handler' => 'SebLucas\\Cops\\Handlers\\CalibreHandler'], ['action', 'library'], ['GET' => 0], null, false, true, null]],
        846 => [[['_route' => 'calres', '_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler'], ['db', 'alg', 'digest'], ['GET' => 0], null, false, true, null]],
        887 => [[['_route' => 'page-language', 'page' => 'language'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        900 => [[['_route' => 'page-language-id', 'page' => 'language'], ['id'], ['GET' => 0], null, false, true, null]],
        946 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId', 'urlPath'], ['GET' => 0], null, false, true, null]],
        965 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId'], ['GET' => 0], null, false, true, null]],
        978 => [[['_route' => 'loader-action-dbNum', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum'], ['GET' => 0], null, false, true, null]],
        987 => [[['_route' => 'loader-action-', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, true, true, null]],
        995 => [[['_route' => 'loader-action', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, false, true, null]],
        1035 => [[['_route' => 'page-publishers-letter', 'page' => 'publishers_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        1058 => [[['_route' => 'page-publisher', 'page' => 'publisher'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1072 => [[['_route' => 'page-publisher-id', 'page' => 'publisher'], ['id'], ['GET' => 0], null, false, true, null]],
        1111 => [[['_route' => 'page-rating', 'page' => 'rating'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1125 => [[['_route' => 'page-rating-id', 'page' => 'rating'], ['id'], ['GET' => 0], null, false, true, null]],
        1165 => [[['_route' => 'read-title', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data', 'title'], ['GET' => 0], null, false, true, null]],
        1185 => [[['_route' => 'read', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data'], ['GET' => 0], null, false, true, null]],
        1198 => [[['_route' => 'read-format', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1247 => [[['_route' => 'restapi-database-table', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db', 'name'], ['GET' => 0], null, false, true, null]],
        1256 => [[['_route' => 'restapi-database', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db'], ['GET' => 0], null, false, true, null]],
        1304 => [[['_route' => 'restapi-note', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item', 'title'], ['GET' => 0], null, false, true, null]],
        1313 => [[['_route' => 'restapi-notes-type-id', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item'], ['GET' => 0], null, false, true, null]],
        1323 => [[['_route' => 'restapi-notes-type', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type'], ['GET' => 0], null, false, true, null]],
        1353 => [[['_route' => 'restapi-preference', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['key'], ['GET' => 0], null, false, true, null]],
        1394 => [[['_route' => 'restapi-annotation', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'id'], ['GET' => 0], null, false, true, null]],
        1403 => [[['_route' => 'restapi-annotations-book', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1454 => [[['_route' => 'restapi-metadata-element-name', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element', 'name'], ['GET' => 0], null, false, true, null]],
        1463 => [[['_route' => 'restapi-metadata-element', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element'], ['GET' => 0], null, false, true, null]],
        1473 => [[['_route' => 'restapi-metadata', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1495 => [[['_route' => 'restapi-folders', '_resource' => 'Folder', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1508 => [[['_route' => 'restapi-path', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1553 => [[['_route' => 'page-identifier', 'page' => 'identifier'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1567 => [[['_route' => 'page-identifier-id', 'page' => 'identifier'], ['id'], ['GET' => 0], null, false, true, null]],
        1615 => [[['_route' => 'fetch-inline', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1643 => [[['_route' => 'fetch-image', 'image' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['size', 'path'], ['GET' => 0], null, false, true, null]],
        1697 => [[['_route' => 'fetch-view', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1754 => [[['_route' => 'fetch-download', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1780 => [[['_route' => 'opds-page', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1793 => [[['_route' => 'opds-path', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1803 => [[['_route' => 'opds', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], [], ['GET' => 0], null, false, false, null]],
        1860 => [[['_route' => 'zipper-page-id-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'id', 'type'], ['GET' => 0], null, false, false, null]],
        1884 => [[['_route' => 'zipper-page-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'type'], ['GET' => 0], null, false, false, null]],
        1916 => [[['_route' => 'zipfs', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        1929 => [
            [['_route' => 'zipfs-format', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['path'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
