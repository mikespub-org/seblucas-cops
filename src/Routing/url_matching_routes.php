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
        '/series' => [[['_route' => 'page-series', 'page' => 'series'], null, ['GET' => 0], null, false, false, null]],
        '/typeahead' => [[['_route' => 'page-typeahead', 'page' => 'query', 'search' => 1], null, ['GET' => 0], null, false, false, null]],
        '/search' => [[['_route' => 'page-search', 'page' => 'opensearch'], null, ['GET' => 0], null, false, false, null]],
        '/recent' => [[['_route' => 'page-recent', 'page' => 'recent'], null, ['GET' => 0], null, false, false, null]],
        '/tags' => [[['_route' => 'page-tags', 'page' => 'tags'], null, ['GET' => 0], null, false, false, null]],
        '/about' => [[['_route' => 'page-about', 'page' => 'about'], null, ['GET' => 0], null, false, false, null]],
        '/languages' => [[['_route' => 'page-languages', 'page' => 'languages'], null, ['GET' => 0], null, false, false, null]],
        '/customize' => [[['_route' => 'page-customize', 'page' => 'customize'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
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
        '/restapi/user/details' => [[['_route' => 'restapi-user-details', '_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user' => [[['_route' => 'restapi-user', '_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/opds/search' => [[['_route' => 'opds-search', 'page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], null, ['GET' => 0], null, false, false, null]],
        '/loader' => [[['_route' => 'loader', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], null, ['GET' => 0], null, false, false, null]],
        '/mail' => [[['_route' => 'mail', '_handler' => 'SebLucas\\Cops\\Handlers\\MailHandler'], null, ['POST' => 0], null, false, false, null]],
        '/graphql' => [[['_route' => 'graphql', '_handler' => 'SebLucas\\Cops\\Handlers\\GraphQLHandler'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/tables' => [[['_route' => 'tables', '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler'], null, ['GET' => 0], null, false, false, null]],
        '/test' => [[['_route' => 'test', '_handler' => 'SebLucas\\Cops\\Handlers\\TestHandler'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/authors/(?'
                    .'|letter/([^/]++)(*:34)'
                    .'|(\\d+)/([^/]++)(*:55)'
                    .'|(\\d+)(*:67)'
                .')'
                .'|/books/(?'
                    .'|letter/(\\w)(*:96)'
                    .'|year/(\\d+)(*:113)'
                    .'|(\\d+)/([^/]++)/([^/]++)(*:144)'
                    .'|(\\d+)(*:157)'
                .')'
                .'|/se(?'
                    .'|ries/(?'
                        .'|(\\d+)/([^/]++)(*:194)'
                        .'|(\\d+)(*:207)'
                    .')'
                    .'|arch/([^/]++)(?'
                        .'|/([^/]++)(*:241)'
                        .'|(*:249)'
                    .')'
                .')'
                .'|/t(?'
                    .'|ags/(?'
                        .'|(\\d+)/([^/]++)(*:285)'
                        .'|(\\d+)(*:298)'
                    .')'
                    .'|humbs/(\\d+)/(\\d+)/([^/\\.]++)\\.jpg(*:340)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:377)'
                        .'|(\\d+)(*:390)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:421)'
                    .'|heck(?'
                        .'|/(.*)(*:441)'
                        .'|(*:449)'
                    .')'
                    .'|alres/(\\d+)/([^/]++)/([^/]++)(*:487)'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:527)'
                        .'|(\\d+)(*:540)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:586)'
                            .'|(\\d+)/(\\w*)(*:605)'
                            .'|(\\d+)(*:618)'
                        .')'
                        .'|(*:627)'
                        .'|(*:635)'
                    .')'
                .')'
                .'|/publishers/(?'
                    .'|(\\d+)/([^/]++)(*:674)'
                    .'|(\\d+)(*:687)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:725)'
                        .'|(\\d+)(*:738)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:777)'
                            .'|(\\d+)/(\\d+)(*:796)'
                        .')'
                        .'|stapi/(?'
                            .'|databases/([^/]++)(?'
                                .'|/([^/]++)(*:844)'
                                .'|(*:852)'
                            .')'
                            .'|notes/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:899)'
                                    .'|(*:907)'
                                .')'
                                .'|(*:916)'
                            .')'
                            .'|preferences/([^/]++)(*:945)'
                            .'|annotations/([^/]++)(?'
                                .'|/([^/]++)(*:985)'
                                .'|(*:993)'
                            .')'
                            .'|metadata/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1043)'
                                    .'|(*:1052)'
                                .')'
                                .'|(*:1062)'
                            .')'
                            .'|(.*)(*:1076)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:1121)'
                        .'|(\\w+)(*:1135)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1183)'
                .')'
                .'|/f(?'
                    .'|ormats/(\\w+)(*:1210)'
                    .'|e(?'
                        .'|ed/(?'
                            .'|(\\w+)(*:1234)'
                            .'|(.+)(*:1247)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1293)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:1324)'
                .')'
                .'|/view/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1378)'
                .'|/download/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1435)'
                .'|/epubfs/(\\d+)/(\\d+)/(.+)(*:1468)'
                .'|/opds(?'
                    .'|/(?'
                        .'|(\\w+)(*:1494)'
                        .'|(.*)(*:1507)'
                    .')'
                    .'|(*:1517)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)/(?'
                        .'|([^/]++)/([^/\\.]++)\\.zip(*:1574)'
                        .'|([^/\\.]++)\\.zip(*:1598)'
                    .')'
                    .'|fs/(\\d+)/(\\d+)/(.+)(*:1627)'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        34 => [[['_route' => 'page-authors-letter', 'page' => 'authors_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        55 => [[['_route' => 'page-author', 'page' => 'author'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        67 => [[['_route' => 'page-author-id', 'page' => 'author'], ['id'], ['GET' => 0], null, false, true, null]],
        96 => [[['_route' => 'page-books-letter', 'page' => 'books_letter'], ['letter'], ['GET' => 0], null, false, true, null]],
        113 => [[['_route' => 'page-books-year', 'page' => 'books_year'], ['year'], ['GET' => 0], null, false, true, null]],
        144 => [[['_route' => 'page-book', 'page' => 'book'], ['id', 'author', 'title'], ['GET' => 0], null, false, true, null]],
        157 => [[['_route' => 'page-book-id', 'page' => 'book'], ['id'], ['GET' => 0], null, false, true, null]],
        194 => [[['_route' => 'page-serie', 'page' => 'serie'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        207 => [[['_route' => 'page-serie-id', 'page' => 'serie'], ['id'], ['GET' => 0], null, false, true, null]],
        241 => [[['_route' => 'page-query-scope', 'page' => 'query'], ['query', 'scope'], ['GET' => 0], null, false, true, null]],
        249 => [[['_route' => 'page-query', 'page' => 'query'], ['query'], ['GET' => 0], null, false, true, null]],
        285 => [[['_route' => 'page-tag', 'page' => 'tag'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        298 => [[['_route' => 'page-tag-id', 'page' => 'tag'], ['id'], ['GET' => 0], null, false, true, null]],
        340 => [[['_route' => 'fetch-thumb', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'thumb'], ['GET' => 0], null, false, false, null]],
        377 => [[['_route' => 'page-custom', 'page' => 'custom'], ['custom', 'id'], ['GET' => 0], null, false, true, null]],
        390 => [[['_route' => 'page-customtype', 'page' => 'customtype'], ['custom'], ['GET' => 0], null, false, true, null]],
        421 => [[['_route' => 'fetch-cover', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id'], ['GET' => 0], null, false, false, null]],
        441 => [[['_route' => 'check-more', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], ['more'], ['GET' => 0], null, false, true, null]],
        449 => [[['_route' => 'check', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], [], ['GET' => 0], null, false, false, null]],
        487 => [[['_route' => 'calres', '_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler'], ['db', 'alg', 'digest'], ['GET' => 0], null, false, true, null]],
        527 => [[['_route' => 'page-language', 'page' => 'language'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        540 => [[['_route' => 'page-language-id', 'page' => 'language'], ['id'], ['GET' => 0], null, false, true, null]],
        586 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId', 'urlPath'], ['GET' => 0], null, false, true, null]],
        605 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId'], ['GET' => 0], null, false, true, null]],
        618 => [[['_route' => 'loader-action-dbNum', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum'], ['GET' => 0], null, false, true, null]],
        627 => [[['_route' => 'loader-action-', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, true, true, null]],
        635 => [[['_route' => 'loader-action', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, false, true, null]],
        674 => [[['_route' => 'page-publisher', 'page' => 'publisher'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        687 => [[['_route' => 'page-publisher-id', 'page' => 'publisher'], ['id'], ['GET' => 0], null, false, true, null]],
        725 => [[['_route' => 'page-rating', 'page' => 'rating'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        738 => [[['_route' => 'page-rating-id', 'page' => 'rating'], ['id'], ['GET' => 0], null, false, true, null]],
        777 => [[['_route' => 'read-title', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data', 'title'], ['GET' => 0], null, false, true, null]],
        796 => [[['_route' => 'read', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data'], ['GET' => 0], null, false, true, null]],
        844 => [[['_route' => 'restapi-database-table', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db', 'name'], ['GET' => 0], null, false, true, null]],
        852 => [[['_route' => 'restapi-database', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db'], ['GET' => 0], null, false, true, null]],
        899 => [[['_route' => 'restapi-note', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item', 'title'], ['GET' => 0], null, false, true, null]],
        907 => [[['_route' => 'restapi-notes-type-id', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'item'], ['GET' => 0], null, false, true, null]],
        916 => [[['_route' => 'restapi-notes-type', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type'], ['GET' => 0], null, false, true, null]],
        945 => [[['_route' => 'restapi-preference', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['key'], ['GET' => 0], null, false, true, null]],
        985 => [[['_route' => 'restapi-annotation', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'id'], ['GET' => 0], null, false, true, null]],
        993 => [[['_route' => 'restapi-annotations-book', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1043 => [[['_route' => 'restapi-metadata-element-name', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element', 'name'], ['GET' => 0], null, false, true, null]],
        1052 => [[['_route' => 'restapi-metadata-element', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element'], ['GET' => 0], null, false, true, null]],
        1062 => [[['_route' => 'restapi-metadata', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1076 => [[['_route' => 'restapi-path', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1121 => [[['_route' => 'page-identifier', 'page' => 'identifier'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1135 => [[['_route' => 'page-identifier-id', 'page' => 'identifier'], ['id'], ['GET' => 0], null, false, true, null]],
        1183 => [[['_route' => 'fetch-inline', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1210 => [[['_route' => 'page-format', 'page' => 'format'], ['id'], ['GET' => 0], null, false, true, null]],
        1234 => [[['_route' => 'feed-page', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1247 => [[['_route' => 'feed-path', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1293 => [[['_route' => 'fetch-data', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1324 => [[['_route' => 'fetch-file', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'file'], ['GET' => 0], null, false, true, null]],
        1378 => [[['_route' => 'fetch-view', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1435 => [[['_route' => 'fetch-download', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1468 => [[['_route' => 'epubfs', '_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        1494 => [[['_route' => 'opds-page', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1507 => [[['_route' => 'opds-path', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['path'], ['GET' => 0], null, false, true, null]],
        1517 => [[['_route' => 'opds', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], [], ['GET' => 0], null, false, false, null]],
        1574 => [[['_route' => 'zipper-page-id-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'id', 'type'], ['GET' => 0], null, false, false, null]],
        1598 => [[['_route' => 'zipper-page-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'type'], ['GET' => 0], null, false, false, null]],
        1627 => [
            [['_route' => 'zipfs', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
