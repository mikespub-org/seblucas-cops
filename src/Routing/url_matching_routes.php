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
        '/editor' => [[['_route' => 'editor', 'editor' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], null, ['GET' => 0, 'POST' => 1], null, true, false, null]],
        '/adminer' => [[['_route' => 'adminer', 'adminer' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], null, ['GET' => 0, 'POST' => 1], null, true, false, null]],
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
                        .'|er/static/(.+)(*:99)'
                        .'|/(.*)(*:111)'
                        .'|(*:119)'
                    .')'
                .')'
                .'|/books/(?'
                    .'|letter/([^/]++)(*:154)'
                    .'|year/(\\d+)(*:172)'
                    .'|(\\d+)/([^/]++)/([^/]++)(*:203)'
                    .'|(\\d+)(*:216)'
                .')'
                .'|/se(?'
                    .'|ries/(?'
                        .'|letter/([^/]++)(*:254)'
                        .'|(\\d+)/([^/]++)(*:276)'
                        .'|(\\d+)(*:289)'
                    .')'
                    .'|arch/([^/]++)(?'
                        .'|/([^/]++)(*:323)'
                        .'|(*:331)'
                    .')'
                .')'
                .'|/f(?'
                    .'|o(?'
                        .'|lder/(.*)(*:359)'
                        .'|rmat(?'
                            .'|s/(\\w+)(*:381)'
                            .'|/(.+)(*:394)'
                        .')'
                    .')'
                    .'|e(?'
                        .'|ed/(?'
                            .'|(\\w+)(*:419)'
                            .'|(.+)(*:431)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:476)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:506)'
                .')'
                .'|/e(?'
                    .'|book/(.+)(*:529)'
                    .'|pubfs/(\\d+)/(\\d+)/(.+)(*:559)'
                    .'|ditor/static/(.+)(*:584)'
                .')'
                .'|/t(?'
                    .'|a(?'
                        .'|gs/(?'
                            .'|letter/([^/]++)(*:623)'
                            .'|(\\d+)/([^/]++)(*:645)'
                            .'|(\\d+)(*:658)'
                        .')'
                        .'|bles/(?'
                            .'|(\\d+)/(\\w+)(*:686)'
                            .'|(\\d+)(*:699)'
                        .')'
                    .')'
                    .'|humbs/(\\d+)/(\\d+)/([^/\\.]++)\\.jpg(*:742)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:779)'
                        .'|(\\d+)(*:792)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:823)'
                    .'|heck(?'
                        .'|/(.*)(*:843)'
                        .'|(*:851)'
                    .')'
                    .'|al(?'
                        .'|ibre/([^/]++)/([^/]++)(?'
                            .'|/(.*)(*:895)'
                            .'|(*:903)'
                        .')'
                        .'|res/(\\d+)/([^/]++)/([^/]++)(*:939)'
                    .')'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:980)'
                        .'|(\\d+)(*:993)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:1039)'
                            .'|(\\d+)/(\\w*)(*:1059)'
                            .'|(\\d+)(*:1073)'
                        .')'
                        .'|(*:1083)'
                        .'|(*:1092)'
                    .')'
                .')'
                .'|/publishers/(?'
                    .'|letter/([^/]++)(*:1133)'
                    .'|(\\d+)/([^/]++)(*:1156)'
                    .'|(\\d+)(*:1170)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:1209)'
                        .'|(\\d+)(*:1223)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:1263)'
                            .'|(\\d+)/(\\d+)(*:1283)'
                            .'|(.+)(*:1296)'
                        .')'
                        .'|stapi/(?'
                            .'|databases/(?'
                                .'|(\\d+)/(\\w+)(*:1339)'
                                .'|(\\d+)(*:1353)'
                            .')'
                            .'|notes/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1401)'
                                    .'|(*:1410)'
                                .')'
                                .'|(*:1420)'
                            .')'
                            .'|preferences/([^/]++)(*:1450)'
                            .'|annotations/([^/]++)(?'
                                .'|/([^/]++)(*:1491)'
                                .'|(*:1500)'
                            .')'
                            .'|metadata/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1551)'
                                    .'|(*:1560)'
                                .')'
                                .'|(*:1570)'
                            .')'
                            .'|folders/(.*)(*:1592)'
                            .'|(.*)(*:1605)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:1650)'
                        .'|(\\w+)(*:1664)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1712)'
                    .'|mages/([^/]++)/(.+)(*:1740)'
                .')'
                .'|/view/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1794)'
                .'|/download/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1851)'
                .'|/opds(?'
                    .'|/(?'
                        .'|(\\w+)(*:1877)'
                        .'|(.*)(*:1890)'
                    .')'
                    .'|(*:1900)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)/(?'
                        .'|([^/]++)/([^/\\.]++)\\.zip(*:1957)'
                        .'|([^/\\.]++)\\.zip(*:1981)'
                    .')'
                    .'|fs/(?'
                        .'|(\\d+)/(\\d+)/(.+)(*:2013)'
                        .'|(.+)(*:2026)'
                    .')'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        37 => [[['_route' => 'page-authors-letter', 'page' => 'authors_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        58 => [[['_route' => 'page-author', 'page' => 'author'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        70 => [[['_route' => 'page-author-id', 'page' => 'author'], ['id'], ['GET' => 0], null, false, true, null]],
        99 => [[['_route' => 'adminer-static', 'adminer' => 1, 'static' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        111 => [[['_route' => 'admin-action', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], ['action'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        119 => [[['_route' => 'admin', '_handler' => 'SebLucas\\Cops\\Handlers\\AdminHandler'], [], ['GET' => 0], null, false, false, null]],
        154 => [[['_route' => 'page-books-letter', 'page' => 'books_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        172 => [[['_route' => 'page-books-year', 'page' => 'books_year'], ['year'], ['GET' => 0], null, false, true, null]],
        203 => [[['_route' => 'page-book', 'page' => 'book'], ['id', 'author', 'title'], ['GET' => 0], null, false, true, null]],
        216 => [[['_route' => 'page-book-id', 'page' => 'book'], ['id'], ['GET' => 0], null, false, true, null]],
        254 => [[['_route' => 'page-series-letter', 'page' => 'series_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        276 => [[['_route' => 'page-serie', 'page' => 'serie'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        289 => [[['_route' => 'page-serie-id', 'page' => 'serie'], ['id'], ['GET' => 0], null, false, true, null]],
        323 => [[['_route' => 'page-query-scope', 'page' => 'query'], ['query', 'scope'], ['GET' => 0], null, false, true, null]],
        331 => [[['_route' => 'page-query', 'page' => 'query'], ['query'], ['GET' => 0], null, false, true, null]],
        359 => [[['_route' => 'page-folder', 'page' => 'folder'], ['path'], ['GET' => 0], null, false, true, null]],
        381 => [[['_route' => 'page-format', 'page' => 'format'], ['id'], ['GET' => 0], null, false, true, null]],
        394 => [[['_route' => 'fetch-format', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        419 => [[['_route' => 'feed-page', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        431 => [[['_route' => 'feed-path', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        476 => [[['_route' => 'fetch-data', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        506 => [[['_route' => 'fetch-file', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'file'], ['GET' => 0], null, false, true, null]],
        529 => [[['_route' => 'page-ebook', 'page' => 'folder', 'ebook' => 1], ['path'], ['GET' => 0], null, false, true, null]],
        559 => [[['_route' => 'epubfs', '_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        584 => [[['_route' => 'editor-static', 'editor' => 1, 'static' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        623 => [[['_route' => 'page-tags-letter', 'page' => 'tags_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        645 => [[['_route' => 'page-tag', 'page' => 'tag'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        658 => [[['_route' => 'page-tag-id', 'page' => 'tag'], ['id'], ['GET' => 0], null, false, true, null]],
        686 => [[['_route' => 'tables-db-name', '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], ['db', 'name'], ['GET' => 0], null, false, true, null]],
        699 => [[['_route' => 'tables-db', '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], ['db'], ['GET' => 0], null, false, true, null]],
        742 => [[['_route' => 'fetch-thumb', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'thumb'], ['GET' => 0], null, false, false, null]],
        779 => [[['_route' => 'page-custom', 'page' => 'custom'], ['custom', 'id'], ['GET' => 0], null, false, true, null]],
        792 => [[['_route' => 'page-customtype', 'page' => 'customtype'], ['custom'], ['GET' => 0], null, false, true, null]],
        823 => [[['_route' => 'fetch-cover', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id'], ['GET' => 0], null, false, false, null]],
        843 => [[['_route' => 'check-more', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], ['more'], ['GET' => 0], null, false, true, null]],
        851 => [[['_route' => 'check', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], [], ['GET' => 0], null, false, false, null]],
        895 => [[['_route' => 'calibre-details', '_handler' => 'SebLucas\\Cops\\Handlers\\CalibreHandler'], ['action', 'library', 'details'], ['GET' => 0], null, false, true, null]],
        903 => [[['_route' => 'calibre-library', '_handler' => 'SebLucas\\Cops\\Handlers\\CalibreHandler'], ['action', 'library'], ['GET' => 0], null, false, true, null]],
        939 => [[['_route' => 'calres', '_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler'], ['db', 'alg', 'digest'], ['GET' => 0], null, false, true, null]],
        980 => [[['_route' => 'page-language', 'page' => 'language'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        993 => [[['_route' => 'page-language-id', 'page' => 'language'], ['id'], ['GET' => 0], null, false, true, null]],
        1039 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId', 'urlPath'], ['GET' => 0], null, false, true, null]],
        1059 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId'], ['GET' => 0], null, false, true, null]],
        1073 => [[['_route' => 'loader-action-dbNum', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum'], ['GET' => 0], null, false, true, null]],
        1083 => [[['_route' => 'loader-action-', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, true, true, null]],
        1092 => [[['_route' => 'loader-action', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, false, true, null]],
        1133 => [[['_route' => 'page-publishers-letter', 'page' => 'publishers_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        1156 => [[['_route' => 'page-publisher', 'page' => 'publisher'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1170 => [[['_route' => 'page-publisher-id', 'page' => 'publisher'], ['id'], ['GET' => 0], null, false, true, null]],
        1209 => [[['_route' => 'page-rating', 'page' => 'rating'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1223 => [[['_route' => 'page-rating-id', 'page' => 'rating'], ['id'], ['GET' => 0], null, false, true, null]],
        1263 => [[['_route' => 'read-title', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data', 'title'], ['GET' => 0], null, false, true, null]],
        1283 => [[['_route' => 'read', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data'], ['GET' => 0], null, false, true, null]],
        1296 => [[['_route' => 'read-format', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1339 => [[['_route' => 'restapi-database-table', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db', 'name'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        1353 => [[['_route' => 'restapi-database', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db'], ['GET' => 0], null, false, true, null]],
        1401 => [[['_route' => 'restapi-note', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item', 'title'], ['GET' => 0], null, false, true, null]],
        1410 => [[['_route' => 'restapi-notes-type-id', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item'], ['GET' => 0], null, false, true, null]],
        1420 => [[['_route' => 'restapi-notes-type', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type'], ['GET' => 0], null, false, true, null]],
        1450 => [[['_route' => 'restapi-preference', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['key'], ['GET' => 0], null, false, true, null]],
        1491 => [[['_route' => 'restapi-annotation', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'id'], ['GET' => 0], null, false, true, null]],
        1500 => [[['_route' => 'restapi-annotations-book', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1551 => [[['_route' => 'restapi-metadata-element-name', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element', 'name'], ['GET' => 0], null, false, true, null]],
        1560 => [[['_route' => 'restapi-metadata-element', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element'], ['GET' => 0], null, false, true, null]],
        1570 => [[['_route' => 'restapi-metadata', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1592 => [[['_route' => 'restapi-folders', '_resource' => 'Folder', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1605 => [[['_route' => 'restapi-path', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1650 => [[['_route' => 'page-identifier', 'page' => 'identifier'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1664 => [[['_route' => 'page-identifier-id', 'page' => 'identifier'], ['id'], ['GET' => 0], null, false, true, null]],
        1712 => [[['_route' => 'fetch-inline', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1740 => [[['_route' => 'fetch-image', 'image' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['size', 'path'], ['GET' => 0], null, false, true, null]],
        1794 => [[['_route' => 'fetch-view', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1851 => [[['_route' => 'fetch-download', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1877 => [[['_route' => 'opds-page', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1890 => [[['_route' => 'opds-path', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1900 => [[['_route' => 'opds', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], [], ['GET' => 0], null, false, false, null]],
        1957 => [[['_route' => 'zipper-page-id-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'id', 'type'], ['GET' => 0], null, false, false, null]],
        1981 => [[['_route' => 'zipper-page-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'type'], ['GET' => 0], null, false, false, null]],
        2013 => [[['_route' => 'zipfs', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        2026 => [
            [['_route' => 'zipfs-format', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['path'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
