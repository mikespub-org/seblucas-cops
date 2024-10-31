<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/index' => [[['_route' => 'page-index', 'page' => 'index'], null, ['GET' => 0], null, false, false, null]],
        '/authors/letter' => [[['_route' => 'page-1-letter', 'page' => '1', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/authors' => [[['_route' => 'page-1', 'page' => '1'], null, ['GET' => 0], null, false, false, null]],
        '/books/letter' => [[['_route' => 'page-4-letter', 'page' => '4', 'letter' => 1], null, ['GET' => 0], null, false, false, null]],
        '/books/year' => [[['_route' => 'page-4-year', 'page' => '4', 'year' => 1], null, ['GET' => 0], null, false, false, null]],
        '/books' => [[['_route' => 'page-4', 'page' => '4'], null, ['GET' => 0], null, false, false, null]],
        '/series' => [[['_route' => 'page-6', 'page' => '6'], null, ['GET' => 0], null, false, false, null]],
        '/search' => [[['_route' => 'page-8', 'page' => '8'], null, ['GET' => 0], null, false, false, null]],
        '/recent' => [[['_route' => 'page-10', 'page' => '10'], null, ['GET' => 0], null, false, false, null]],
        '/tags' => [[['_route' => 'page-11', 'page' => '11'], null, ['GET' => 0], null, false, false, null]],
        '/about' => [[['_route' => 'page-16', 'page' => '16'], null, ['GET' => 0], null, false, false, null]],
        '/languages' => [[['_route' => 'page-17', 'page' => '17'], null, ['GET' => 0], null, false, false, null]],
        '/customize' => [[['_route' => 'page-19', 'page' => '19'], null, ['GET' => 0], null, false, false, null]],
        '/publishers' => [[['_route' => 'page-20', 'page' => '20'], null, ['GET' => 0], null, false, false, null]],
        '/ratings' => [[['_route' => 'page-22', 'page' => '22'], null, ['GET' => 0], null, false, false, null]],
        '/identifiers' => [[['_route' => 'page-41', 'page' => '41'], null, ['GET' => 0], null, false, false, null]],
        '/libraries' => [[['_route' => 'page-43', 'page' => '43'], null, ['GET' => 0], null, false, false, null]],
        '/feed/search' => [[['_route' => 'feed-search', 'page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], null, ['GET' => 0], null, false, false, null]],
        '/feed' => [[['_route' => 'feed', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/custom' => [[['_route' => 'restapi-CustomColumnType', '_resource' => 'CustomColumnType', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/databases' => [[['_route' => 'restapi-Database', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/openapi' => [[['_route' => 'restapi-openapi', '_resource' => 'openapi', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/routes' => [[['_route' => 'restapi-route', '_resource' => 'route', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/handlers' => [[['_route' => 'restapi-handler', '_resource' => 'handler', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/notes' => [[['_route' => 'restapi-Note', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/preferences' => [[['_route' => 'restapi-Preference', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/annotations' => [[['_route' => 'restapi-Annotation', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user/details' => [[['_route' => 'restapi-User-details', '_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user' => [[['_route' => 'restapi-User', '_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], null, ['GET' => 0], null, false, false, null]],
        '/opds/search' => [[['_route' => 'opds-search', 'page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], null, ['GET' => 0], null, false, false, null]],
        '/opds' => [[['_route' => 'opds', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], null, ['GET' => 0], null, false, false, null]],
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
                .'|/query/([^/]++)(?'
                    .'|/([^/]++)(*:286)'
                    .'|(*:294)'
                .')'
                .'|/t(?'
                    .'|ags/(?'
                        .'|(\\d+)/([^/]++)(*:329)'
                        .'|(\\d+)(*:342)'
                    .')'
                    .'|humbs/(\\d+)/(\\d+)/([^/\\.]++)\\.jpg(*:384)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:421)'
                        .'|(\\d+)(*:434)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:465)'
                    .'|heck(?'
                        .'|/(.*)(*:485)'
                        .'|(*:493)'
                    .')'
                    .'|alres/(\\d+)/([^/]++)/([^/]++)(*:531)'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:571)'
                        .'|(\\d+)(*:584)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:630)'
                            .'|(\\d+)/(\\w*)(*:649)'
                            .'|(\\d+)(*:662)'
                        .')'
                        .'|(*:671)'
                        .'|(*:679)'
                    .')'
                .')'
                .'|/publishers/(?'
                    .'|(\\d+)/([^/]++)(*:718)'
                    .'|(\\d+)(*:731)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:769)'
                        .'|(\\d+)(*:782)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:821)'
                            .'|(\\d+)/(\\d+)(*:840)'
                        .')'
                        .'|stapi/(?'
                            .'|databases/([^/]++)(?'
                                .'|/([^/]++)(*:888)'
                                .'|(*:896)'
                            .')'
                            .'|notes/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:943)'
                                    .'|(*:951)'
                                .')'
                                .'|(*:960)'
                            .')'
                            .'|preferences/([^/]++)(*:989)'
                            .'|annotations/([^/]++)(?'
                                .'|/([^/]++)(*:1029)'
                                .'|(*:1038)'
                            .')'
                            .'|metadata/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1089)'
                                    .'|(*:1098)'
                                .')'
                                .'|(*:1108)'
                            .')'
                            .'|(.*)(*:1122)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:1167)'
                        .'|(\\w+)(*:1181)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1229)'
                .')'
                .'|/f(?'
                    .'|e(?'
                        .'|ed/([^/]++)(?'
                            .'|/([^/]++)(*:1271)'
                            .'|(*:1280)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1326)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:1357)'
                .')'
                .'|/view/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1411)'
                .'|/download/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1468)'
                .'|/epubfs/(\\d+)/(\\d+)/(.+)(*:1501)'
                .'|/opds/([^/]++)(?'
                    .'|/([^/]++)(*:1536)'
                    .'|(*:1545)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)/(?'
                        .'|([^/]++)/([^/\\.]++)\\.zip(*:1602)'
                        .'|([^/\\.]++)\\.zip(*:1626)'
                    .')'
                    .'|fs/(\\d+)/(\\d+)/(.+)(*:1655)'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        34 => [[['_route' => 'page-2-id', 'page' => '2'], ['id'], ['GET' => 0], null, false, true, null]],
        55 => [[['_route' => 'page-3-id-title', 'page' => '3'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        67 => [[['_route' => 'page-3-id', 'page' => '3'], ['id'], ['GET' => 0], null, false, true, null]],
        96 => [[['_route' => 'page-5-id', 'page' => '5'], ['id'], ['GET' => 0], null, false, true, null]],
        113 => [[['_route' => 'page-50-id', 'page' => '50'], ['id'], ['GET' => 0], null, false, true, null]],
        144 => [[['_route' => 'page-13-id-author-title', 'page' => '13'], ['id', 'author', 'title'], ['GET' => 0], null, false, true, null]],
        157 => [[['_route' => 'page-13-id', 'page' => '13'], ['id'], ['GET' => 0], null, false, true, null]],
        194 => [[['_route' => 'page-7-id-title', 'page' => '7'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        207 => [[['_route' => 'page-7-id', 'page' => '7'], ['id'], ['GET' => 0], null, false, true, null]],
        241 => [[['_route' => 'page-9-search-scope', 'page' => '9'], ['query', 'scope'], ['GET' => 0], null, false, true, null]],
        249 => [[['_route' => 'page-9-search', 'page' => '9'], ['query'], ['GET' => 0], null, false, true, null]],
        286 => [[['_route' => 'page-9-query-scope', 'page' => '9', 'search' => 1], ['query', 'scope'], ['GET' => 0], null, false, true, null]],
        294 => [[['_route' => 'page-9-query', 'page' => '9', 'search' => 1], ['query'], ['GET' => 0], null, false, true, null]],
        329 => [[['_route' => 'page-12-id-title', 'page' => '12'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        342 => [[['_route' => 'page-12-id', 'page' => '12'], ['id'], ['GET' => 0], null, false, true, null]],
        384 => [[['_route' => 'fetch-thumb', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'thumb'], ['GET' => 0], null, false, false, null]],
        421 => [[['_route' => 'page-15-custom-id', 'page' => '15'], ['custom', 'id'], ['GET' => 0], null, false, true, null]],
        434 => [[['_route' => 'page-14-custom', 'page' => '14'], ['custom'], ['GET' => 0], null, false, true, null]],
        465 => [[['_route' => 'fetch-cover', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id'], ['GET' => 0], null, false, false, null]],
        485 => [[['_route' => 'check-more', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], ['more'], ['GET' => 0], null, false, true, null]],
        493 => [[['_route' => 'check', '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler'], [], ['GET' => 0], null, false, false, null]],
        531 => [[['_route' => 'calres', '_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler'], ['db', 'alg', 'digest'], ['GET' => 0], null, false, true, null]],
        571 => [[['_route' => 'page-18-id-title', 'page' => '18'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        584 => [[['_route' => 'page-18-id', 'page' => '18'], ['id'], ['GET' => 0], null, false, true, null]],
        630 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId', 'urlPath'], ['GET' => 0], null, false, true, null]],
        649 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum', 'authorId'], ['GET' => 0], null, false, true, null]],
        662 => [[['_route' => 'loader-action-dbNum', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action', 'dbNum'], ['GET' => 0], null, false, true, null]],
        671 => [[['_route' => 'loader-action-', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, true, true, null]],
        679 => [[['_route' => 'loader-action', '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler'], ['action'], ['GET' => 0], null, false, true, null]],
        718 => [[['_route' => 'page-21-id-title', 'page' => '21'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        731 => [[['_route' => 'page-21-id', 'page' => '21'], ['id'], ['GET' => 0], null, false, true, null]],
        769 => [[['_route' => 'page-23-id-title', 'page' => '23'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        782 => [[['_route' => 'page-23-id', 'page' => '23'], ['id'], ['GET' => 0], null, false, true, null]],
        821 => [[['_route' => 'read-title', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data', 'title'], ['GET' => 0], null, false, true, null]],
        840 => [[['_route' => 'read', '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler'], ['db', 'data'], ['GET' => 0], null, false, true, null]],
        888 => [[['_route' => 'restapi-Database-db-name', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db', 'name'], ['GET' => 0], null, false, true, null]],
        896 => [[['_route' => 'restapi-Database-db', '_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['db'], ['GET' => 0], null, false, true, null]],
        943 => [[['_route' => 'restapi-Note-type-id-title', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'id', 'title'], ['GET' => 0], null, false, true, null]],
        951 => [[['_route' => 'restapi-Note-type-id', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type', 'id'], ['GET' => 0], null, false, true, null]],
        960 => [[['_route' => 'restapi-Note-type', '_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['type'], ['GET' => 0], null, false, true, null]],
        989 => [[['_route' => 'restapi-Preference-key', '_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['key'], ['GET' => 0], null, false, true, null]],
        1029 => [[['_route' => 'restapi-Annotation-bookId-id', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'id'], ['GET' => 0], null, false, true, null]],
        1038 => [[['_route' => 'restapi-Annotation-bookId', '_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1089 => [[['_route' => 'restapi-Metadata-bookId-element-name', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element', 'name'], ['GET' => 0], null, false, true, null]],
        1098 => [[['_route' => 'restapi-Metadata-bookId-element', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId', 'element'], ['GET' => 0], null, false, true, null]],
        1108 => [[['_route' => 'restapi-Metadata-bookId', '_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1122 => [[['_route' => 'restapi-other', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler'], ['route'], ['GET' => 0], null, false, true, null]],
        1167 => [[['_route' => 'page-42-id-title', 'page' => '42'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1181 => [[['_route' => 'page-42-id', 'page' => '42'], ['id'], ['GET' => 0], null, false, true, null]],
        1229 => [[['_route' => 'fetch-inline', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1271 => [[['_route' => 'feed-page-id', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['page', 'id'], ['GET' => 0], null, false, true, null]],
        1280 => [[['_route' => 'feed-page', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1326 => [[['_route' => 'fetch-data', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1357 => [[['_route' => 'fetch-file', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['db', 'id', 'file'], ['GET' => 0], null, false, true, null]],
        1411 => [[['_route' => 'fetch-view', 'view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1468 => [[['_route' => 'fetch-download', '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1501 => [[['_route' => 'epubfs', '_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        1536 => [[['_route' => 'opds-page-id', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['page', 'id'], ['GET' => 0], null, false, true, null]],
        1545 => [[['_route' => 'opds-page', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler'], ['page'], ['GET' => 0], null, false, true, null]],
        1602 => [[['_route' => 'zipper-page-id-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'id', 'type'], ['GET' => 0], null, false, false, null]],
        1626 => [[['_route' => 'zipper-page-type', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler'], ['page', 'type'], ['GET' => 0], null, false, false, null]],
        1655 => [
            [['_route' => 'zipfs', '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
