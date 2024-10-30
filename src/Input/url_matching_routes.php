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
        '/feed' => [[['_route' => 'feed', '_handler' => 'feed'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/custom' => [[['_route' => 'restapi-CustomColumnType', '_resource' => 'CustomColumnType', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/databases' => [[['_route' => 'restapi-Database', '_resource' => 'Database', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/openapi' => [[['_route' => 'restapi-openapi', '_resource' => 'openapi', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/routes' => [[['_route' => 'restapi-route', '_resource' => 'route', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/handlers' => [[['_route' => 'restapi-handler', '_resource' => 'handler', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/notes' => [[['_route' => 'restapi-Note', '_resource' => 'Note', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/preferences' => [[['_route' => 'restapi-Preference', '_resource' => 'Preference', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/annotations' => [[['_route' => 'restapi-Annotation', '_resource' => 'Annotation', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user/details' => [[['_route' => 'restapi-User-details', '_resource' => 'User', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/restapi/user' => [[['_route' => 'restapi-User', '_resource' => 'User', '_handler' => 'restapi'], null, ['GET' => 0], null, false, false, null]],
        '/opds' => [[['_route' => 'opds', '_handler' => 'opds'], null, ['GET' => 0], null, false, false, null]],
        '/loader' => [[['_route' => 'loader', '_handler' => 'loader'], null, ['GET' => 0], null, false, false, null]],
        '/mail' => [[['_route' => 'mail', '_handler' => 'mail'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/graphql' => [[['_route' => 'graphql', '_handler' => 'graphql'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/tables' => [[['_route' => 'tables', '_handler' => 'tables'], null, ['GET' => 0], null, false, false, null]],
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
                    .'|humbs/([^/]++)/(\\d+)/(\\d+)\\.jpg(*:382)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:419)'
                        .'|(\\d+)(*:432)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:463)'
                    .'|heck(?'
                        .'|/(.*)(*:483)'
                        .'|(*:491)'
                    .')'
                    .'|alres/(\\d+)/([^/]++)/([^/]++)(*:529)'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:569)'
                        .'|(\\d+)(*:582)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:628)'
                            .'|(\\d+)/(\\w*)(*:647)'
                            .'|(\\d+)(*:660)'
                        .')'
                        .'|(*:669)'
                        .'|(*:677)'
                    .')'
                .')'
                .'|/publishers/(?'
                    .'|(\\d+)/([^/]++)(*:716)'
                    .'|(\\d+)(*:729)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:767)'
                        .'|(\\d+)(*:780)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:819)'
                            .'|(\\d+)/(\\d+)(*:838)'
                        .')'
                        .'|stapi/(?'
                            .'|databases/([^/]++)(?'
                                .'|/([^/]++)(*:886)'
                                .'|(*:894)'
                            .')'
                            .'|notes/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:941)'
                                    .'|(*:949)'
                                .')'
                                .'|(*:958)'
                            .')'
                            .'|preferences/([^/]++)(*:987)'
                            .'|annotations/([^/]++)(?'
                                .'|/([^/]++)(*:1027)'
                                .'|(*:1036)'
                            .')'
                            .'|metadata/([^/]++)(?'
                                .'|/([^/]++)(?'
                                    .'|/([^/]++)(*:1087)'
                                    .'|(*:1096)'
                                .')'
                                .'|(*:1106)'
                            .')'
                            .'|(.*)(*:1120)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:1165)'
                        .'|(\\w+)(*:1179)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1227)'
                .')'
                .'|/f(?'
                    .'|e(?'
                        .'|ed/([^/]++)(?'
                            .'|/([^/]++)(*:1269)'
                            .'|(*:1278)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1324)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:1355)'
                .')'
                .'|/view/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1409)'
                .'|/download/([^/]++)/([^/]++)/([^/\\.]++)\\.([^/]++)(*:1466)'
                .'|/epubfs/(\\d+)/(\\d+)/(.+)(*:1499)'
                .'|/opds/([^/]++)(?'
                    .'|/([^/]++)(*:1534)'
                    .'|(*:1543)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)(?'
                        .'|/([^/]++)(?'
                            .'|/([^/]++)(*:1596)'
                            .'|(*:1605)'
                        .')'
                        .'|(*:1615)'
                    .')'
                    .'|fs/(\\d+)/(\\d+)/(.+)(*:1644)'
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
        382 => [[['_route' => 'fetch-thumb', '_handler' => 'fetch'], ['thumb', 'db', 'id'], ['GET' => 0], null, false, false, null]],
        419 => [[['_route' => 'page-15-custom-id', 'page' => '15'], ['custom', 'id'], ['GET' => 0], null, false, true, null]],
        432 => [[['_route' => 'page-14-custom', 'page' => '14'], ['custom'], ['GET' => 0], null, false, true, null]],
        463 => [[['_route' => 'fetch-cover', '_handler' => 'fetch'], ['db', 'id'], ['GET' => 0], null, false, false, null]],
        483 => [[['_route' => 'check-more', '_handler' => 'check'], ['more'], ['GET' => 0], null, false, true, null]],
        491 => [[['_route' => 'check', '_handler' => 'check'], [], ['GET' => 0], null, false, false, null]],
        529 => [[['_route' => 'calres', '_handler' => 'calres'], ['db', 'alg', 'digest'], ['GET' => 0], null, false, true, null]],
        569 => [[['_route' => 'page-18-id-title', 'page' => '18'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        582 => [[['_route' => 'page-18-id', 'page' => '18'], ['id'], ['GET' => 0], null, false, true, null]],
        628 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'loader'], ['action', 'dbNum', 'authorId', 'urlPath'], ['GET' => 0], null, false, true, null]],
        647 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'loader'], ['action', 'dbNum', 'authorId'], ['GET' => 0], null, false, true, null]],
        660 => [[['_route' => 'loader-action-dbNum', '_handler' => 'loader'], ['action', 'dbNum'], ['GET' => 0], null, false, true, null]],
        669 => [[['_route' => 'loader-action-', '_handler' => 'loader'], ['action'], ['GET' => 0], null, true, true, null]],
        677 => [[['_route' => 'loader-action', '_handler' => 'loader'], ['action'], ['GET' => 0], null, false, true, null]],
        716 => [[['_route' => 'page-21-id-title', 'page' => '21'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        729 => [[['_route' => 'page-21-id', 'page' => '21'], ['id'], ['GET' => 0], null, false, true, null]],
        767 => [[['_route' => 'page-23-id-title', 'page' => '23'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        780 => [[['_route' => 'page-23-id', 'page' => '23'], ['id'], ['GET' => 0], null, false, true, null]],
        819 => [[['_route' => 'read-title', '_handler' => 'read'], ['db', 'data', 'title'], ['GET' => 0], null, false, true, null]],
        838 => [[['_route' => 'read', '_handler' => 'read'], ['db', 'data'], ['GET' => 0], null, false, true, null]],
        886 => [[['_route' => 'restapi-Database-db-name', '_resource' => 'Database', '_handler' => 'restapi'], ['db', 'name'], ['GET' => 0], null, false, true, null]],
        894 => [[['_route' => 'restapi-Database-db', '_resource' => 'Database', '_handler' => 'restapi'], ['db'], ['GET' => 0], null, false, true, null]],
        941 => [[['_route' => 'restapi-Note-type-id-title', '_resource' => 'Note', '_handler' => 'restapi'], ['type', 'id', 'title'], ['GET' => 0], null, false, true, null]],
        949 => [[['_route' => 'restapi-Note-type-id', '_resource' => 'Note', '_handler' => 'restapi'], ['type', 'id'], ['GET' => 0], null, false, true, null]],
        958 => [[['_route' => 'restapi-Note-type', '_resource' => 'Note', '_handler' => 'restapi'], ['type'], ['GET' => 0], null, false, true, null]],
        987 => [[['_route' => 'restapi-Preference-key', '_resource' => 'Preference', '_handler' => 'restapi'], ['key'], ['GET' => 0], null, false, true, null]],
        1027 => [[['_route' => 'restapi-Annotation-bookId-id', '_resource' => 'Annotation', '_handler' => 'restapi'], ['bookId', 'id'], ['GET' => 0], null, false, true, null]],
        1036 => [[['_route' => 'restapi-Annotation-bookId', '_resource' => 'Annotation', '_handler' => 'restapi'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1087 => [[['_route' => 'restapi-Metadata-bookId-element-name', '_resource' => 'Metadata', '_handler' => 'restapi'], ['bookId', 'element', 'name'], ['GET' => 0], null, false, true, null]],
        1096 => [[['_route' => 'restapi-Metadata-bookId-element', '_resource' => 'Metadata', '_handler' => 'restapi'], ['bookId', 'element'], ['GET' => 0], null, false, true, null]],
        1106 => [[['_route' => 'restapi-Metadata-bookId', '_resource' => 'Metadata', '_handler' => 'restapi'], ['bookId'], ['GET' => 0], null, false, true, null]],
        1120 => [[['_route' => 'restapi-other', '_handler' => 'restapi'], ['route'], ['GET' => 0], null, false, true, null]],
        1165 => [[['_route' => 'page-42-id-title', 'page' => '42'], ['id', 'title'], ['GET' => 0], null, false, true, null]],
        1179 => [[['_route' => 'page-42-id', 'page' => '42'], ['id'], ['GET' => 0], null, false, true, null]],
        1227 => [[['_route' => 'fetch-inline', 'view' => 1, '_handler' => 'fetch'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1269 => [[['_route' => 'feed-page-id', '_handler' => 'feed'], ['page', 'id'], ['GET' => 0], null, false, true, null]],
        1278 => [[['_route' => 'feed-page', '_handler' => 'feed'], ['page'], ['GET' => 0], null, false, true, null]],
        1324 => [[['_route' => 'fetch-data', '_handler' => 'fetch'], ['db', 'data', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1355 => [[['_route' => 'fetch-file', '_handler' => 'fetch'], ['db', 'id', 'file'], ['GET' => 0], null, false, true, null]],
        1409 => [[['_route' => 'fetch-view', 'view' => 1, '_handler' => 'fetch'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1466 => [[['_route' => 'fetch-download', '_handler' => 'fetch'], ['data', 'db', 'ignore', 'type'], ['GET' => 0], null, false, true, null]],
        1499 => [[['_route' => 'epubfs', '_handler' => 'epubfs'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null]],
        1534 => [[['_route' => 'opds-page-id', '_handler' => 'opds'], ['page', 'id'], ['GET' => 0], null, false, true, null]],
        1543 => [[['_route' => 'opds-page', '_handler' => 'opds'], ['page'], ['GET' => 0], null, false, true, null]],
        1596 => [[['_route' => 'zipper-page-type-id', '_handler' => 'zipper'], ['page', 'type', 'id'], ['GET' => 0], null, false, true, null]],
        1605 => [[['_route' => 'zipper-page-type', '_handler' => 'zipper'], ['page', 'type'], ['GET' => 0], null, false, true, null]],
        1615 => [[['_route' => 'zipper-page', '_handler' => 'zipper'], ['page'], ['GET' => 0], null, false, true, null]],
        1644 => [
            [['_route' => 'zipfs', '_handler' => 'zipfs'], ['db', 'data', 'comp'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
