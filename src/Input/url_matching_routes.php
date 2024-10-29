<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/index' => [[['_route' => 'index', 'page' => 'index'], null, null, null, false, false, null]],
        '/authors/letter' => [[['_route' => 'authors-letter', 'page' => '1', 'letter' => 1], null, null, null, false, false, null]],
        '/authors' => [[['_route' => 'authors', 'page' => '1'], null, null, null, false, false, null]],
        '/books/letter' => [[['_route' => 'books-letter', 'page' => '4', 'letter' => 1], null, null, null, false, false, null]],
        '/books/year' => [[['_route' => 'books-year', 'page' => '4', 'year' => 1], null, null, null, false, false, null]],
        '/books' => [[['_route' => 'books', 'page' => '4'], null, null, null, false, false, null]],
        '/series' => [[['_route' => 'series', 'page' => '6'], null, null, null, false, false, null]],
        '/search' => [[['_route' => 'search', 'page' => '8'], null, null, null, false, false, null]],
        '/recent' => [[['_route' => 'recent', 'page' => '10'], null, null, null, false, false, null]],
        '/tags' => [[['_route' => 'tags', 'page' => '11'], null, null, null, false, false, null]],
        '/about' => [[['_route' => 'about', 'page' => '16'], null, null, null, false, false, null]],
        '/languages' => [[['_route' => 'languages', 'page' => '17'], null, null, null, false, false, null]],
        '/customize' => [[['_route' => 'customize', 'page' => '19'], null, null, null, false, false, null]],
        '/publishers' => [[['_route' => 'publishers', 'page' => '20'], null, null, null, false, false, null]],
        '/ratings' => [[['_route' => 'ratings', 'page' => '22'], null, null, null, false, false, null]],
        '/identifiers' => [[['_route' => 'identifiers', 'page' => '41'], null, null, null, false, false, null]],
        '/libraries' => [[['_route' => 'libraries', 'page' => '43'], null, null, null, false, false, null]],
        '/feed' => [[['_route' => 'feed', '_handler' => 'feed'], null, null, null, false, false, null]],
        '/restapi/custom' => [[['_route' => 'restapi-custom', '_handler' => 'restapi', '_resource' => 'CustomColumnType'], null, null, null, false, false, null]],
        '/restapi/databases' => [[['_route' => 'restapi-databases', '_handler' => 'restapi', '_resource' => 'Database'], null, null, null, false, false, null]],
        '/restapi/openapi' => [[['_route' => 'restapi-openapi', '_handler' => 'restapi', '_resource' => 'openapi'], null, null, null, false, false, null]],
        '/restapi/routes' => [[['_route' => 'restapi-routes', '_handler' => 'restapi', '_resource' => 'route'], null, null, null, false, false, null]],
        '/restapi/pages' => [[['_route' => 'restapi-pages', '_handler' => 'restapi', '_resource' => 'page'], null, null, null, false, false, null]],
        '/restapi/notes' => [[['_route' => 'restapi-notes', '_handler' => 'restapi', '_resource' => 'Note'], null, null, null, false, false, null]],
        '/restapi/preferences' => [[['_route' => 'restapi-preferences', '_handler' => 'restapi', '_resource' => 'Preference'], null, null, null, false, false, null]],
        '/restapi/annotations' => [[['_route' => 'restapi-annotations', '_handler' => 'restapi', '_resource' => 'Annotation'], null, null, null, false, false, null]],
        '/restapi/user/details' => [[['_route' => 'restapi-user-details', '_handler' => 'restapi', '_resource' => 'User'], null, null, null, false, false, null]],
        '/restapi/user' => [[['_route' => 'restapi-user', '_handler' => 'restapi', '_resource' => 'User'], null, null, null, false, false, null]],
        '/opds' => [[['_route' => 'opds', '_handler' => 'opds'], null, null, null, false, false, null]],
        '/loader' => [[['_route' => 'loader', '_handler' => 'loader'], null, null, null, false, false, null]],
        '/mail' => [[['_route' => 'mail', '_handler' => 'mail'], null, null, null, false, false, null]],
        '/graphql' => [[['_route' => 'graphql', '_handler' => 'graphql'], null, null, null, false, false, null]],
        '/tables' => [[['_route' => 'tables', '_handler' => 'tables'], null, null, null, false, false, null]],
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
                .'|/view/([^/]++)/(?'
                    .'|([^/]++)/([^/\\.]++)\\.([^/]++)(*:1412)'
                    .'|([^/\\.]++)\\.([^/]++)(*:1441)'
                .')'
                .'|/download/([^/]++)/(?'
                    .'|([^/]++)/([^/\\.]++)\\.([^/]++)(*:1502)'
                    .'|([^/\\.]++)\\.([^/]++)(*:1531)'
                .')'
                .'|/epubfs/(\\d+)/(\\d+)/(.+)(*:1565)'
                .'|/opds/([^/]++)(?'
                    .'|/([^/]++)(*:1600)'
                    .'|(*:1609)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)(?'
                        .'|/([^/]++)(?'
                            .'|/([^/]++)(*:1662)'
                            .'|(*:1671)'
                        .')'
                        .'|(*:1681)'
                    .')'
                    .'|fs/(\\d+)/(\\d+)/(.+)(*:1710)'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        34 => [[['_route' => 'authors-letter-id', 'page' => '2'], ['id'], null, null, false, true, null]],
        55 => [[['_route' => 'authors-id-title', 'page' => '3'], ['id', 'title'], null, null, false, true, null]],
        67 => [[['_route' => 'authors-id', 'page' => '3'], ['id'], null, null, false, true, null]],
        96 => [[['_route' => 'books-letter-id', 'page' => '5'], ['id'], null, null, false, true, null]],
        113 => [[['_route' => 'books-year-id', 'page' => '50'], ['id'], null, null, false, true, null]],
        144 => [[['_route' => 'books-id-author-title', 'page' => '13'], ['id', 'author', 'title'], null, null, false, true, null]],
        157 => [[['_route' => 'books-id', 'page' => '13'], ['id'], null, null, false, true, null]],
        194 => [[['_route' => 'series-id-title', 'page' => '7'], ['id', 'title'], null, null, false, true, null]],
        207 => [[['_route' => 'series-id', 'page' => '7'], ['id'], null, null, false, true, null]],
        241 => [[['_route' => 'search-query-scope', 'page' => '9'], ['query', 'scope'], null, null, false, true, null]],
        249 => [[['_route' => 'search-query', 'page' => '9'], ['query'], null, null, false, true, null]],
        286 => [[['_route' => 'query-query-scope', 'page' => '9', 'search' => 1], ['query', 'scope'], null, null, false, true, null]],
        294 => [[['_route' => 'query-query', 'page' => '9', 'search' => 1], ['query'], null, null, false, true, null]],
        329 => [[['_route' => 'tags-id-title', 'page' => '12'], ['id', 'title'], null, null, false, true, null]],
        342 => [[['_route' => 'tags-id', 'page' => '12'], ['id'], null, null, false, true, null]],
        382 => [[['_route' => 'thumbs-thumb-db-id', '_handler' => 'fetch'], ['thumb', 'db', 'id'], null, null, false, false, null]],
        419 => [[['_route' => 'custom-custom-id', 'page' => '15'], ['custom', 'id'], null, null, false, true, null]],
        432 => [[['_route' => 'custom-custom', 'page' => '14'], ['custom'], null, null, false, true, null]],
        463 => [[['_route' => 'covers-db-id', '_handler' => 'fetch'], ['db', 'id'], null, null, false, false, null]],
        483 => [[['_route' => 'check-more', '_handler' => 'check'], ['more'], null, null, false, true, null]],
        491 => [[['_route' => 'check', '_handler' => 'check'], [], null, null, false, false, null]],
        529 => [[['_route' => 'calres-db-alg-digest', '_handler' => 'calres'], ['db', 'alg', 'digest'], null, null, false, true, null]],
        569 => [[['_route' => 'languages-id-title', 'page' => '18'], ['id', 'title'], null, null, false, true, null]],
        582 => [[['_route' => 'languages-id', 'page' => '18'], ['id'], null, null, false, true, null]],
        628 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'loader'], ['action', 'dbNum', 'authorId', 'urlPath'], null, null, false, true, null]],
        647 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'loader'], ['action', 'dbNum', 'authorId'], null, null, false, true, null]],
        660 => [[['_route' => 'loader-action-dbNum', '_handler' => 'loader'], ['action', 'dbNum'], null, null, false, true, null]],
        669 => [[['_route' => 'loader-action-', '_handler' => 'loader'], ['action'], null, null, true, true, null]],
        677 => [[['_route' => 'loader-action', '_handler' => 'loader'], ['action'], null, null, false, true, null]],
        716 => [[['_route' => 'publishers-id-title', 'page' => '21'], ['id', 'title'], null, null, false, true, null]],
        729 => [[['_route' => 'publishers-id', 'page' => '21'], ['id'], null, null, false, true, null]],
        767 => [[['_route' => 'ratings-id-title', 'page' => '23'], ['id', 'title'], null, null, false, true, null]],
        780 => [[['_route' => 'ratings-id', 'page' => '23'], ['id'], null, null, false, true, null]],
        819 => [[['_route' => 'read-db-data-title', '_handler' => 'read'], ['db', 'data', 'title'], null, null, false, true, null]],
        838 => [[['_route' => 'read-db-data', '_handler' => 'read'], ['db', 'data'], null, null, false, true, null]],
        886 => [[['_route' => 'restapi-databases-db-name', '_handler' => 'restapi', '_resource' => 'Database'], ['db', 'name'], null, null, false, true, null]],
        894 => [[['_route' => 'restapi-databases-db', '_handler' => 'restapi', '_resource' => 'Database'], ['db'], null, null, false, true, null]],
        941 => [[['_route' => 'restapi-notes-type-id-title', '_handler' => 'restapi', '_resource' => 'Note'], ['type', 'id', 'title'], null, null, false, true, null]],
        949 => [[['_route' => 'restapi-notes-type-id', '_handler' => 'restapi', '_resource' => 'Note'], ['type', 'id'], null, null, false, true, null]],
        958 => [[['_route' => 'restapi-notes-type', '_handler' => 'restapi', '_resource' => 'Note'], ['type'], null, null, false, true, null]],
        987 => [[['_route' => 'restapi-preferences-key', '_handler' => 'restapi', '_resource' => 'Preference'], ['key'], null, null, false, true, null]],
        1027 => [[['_route' => 'restapi-annotations-bookId-id', '_handler' => 'restapi', '_resource' => 'Annotation'], ['bookId', 'id'], null, null, false, true, null]],
        1036 => [[['_route' => 'restapi-annotations-bookId', '_handler' => 'restapi', '_resource' => 'Annotation'], ['bookId'], null, null, false, true, null]],
        1087 => [[['_route' => 'restapi-metadata-bookId-element-name', '_handler' => 'restapi', '_resource' => 'Metadata'], ['bookId', 'element', 'name'], null, null, false, true, null]],
        1096 => [[['_route' => 'restapi-metadata-bookId-element', '_handler' => 'restapi', '_resource' => 'Metadata'], ['bookId', 'element'], null, null, false, true, null]],
        1106 => [[['_route' => 'restapi-metadata-bookId', '_handler' => 'restapi', '_resource' => 'Metadata'], ['bookId'], null, null, false, true, null]],
        1120 => [[['_route' => 'restapi-route', '_handler' => 'restapi'], ['route'], null, null, false, true, null]],
        1165 => [[['_route' => 'identifiers-id-title', 'page' => '42'], ['id', 'title'], null, null, false, true, null]],
        1179 => [[['_route' => 'identifiers-id', 'page' => '42'], ['id'], null, null, false, true, null]],
        1227 => [[['_route' => 'inline-db-data-ignore.type', '_handler' => 'fetch', 'view' => 1], ['db', 'data', 'ignore', 'type'], null, null, false, true, null]],
        1269 => [[['_route' => 'feed-page-id', '_handler' => 'feed'], ['page', 'id'], null, null, false, true, null]],
        1278 => [[['_route' => 'feed-page', '_handler' => 'feed'], ['page'], null, null, false, true, null]],
        1324 => [[['_route' => 'fetch-db-data-ignore.type', '_handler' => 'fetch'], ['db', 'data', 'ignore', 'type'], null, null, false, true, null]],
        1355 => [[['_route' => 'files-db-id-file', '_handler' => 'fetch'], ['db', 'id', 'file'], null, null, false, true, null]],
        1412 => [[['_route' => 'view-data-db-ignore.type', '_handler' => 'fetch', 'view' => 1], ['data', 'db', 'ignore', 'type'], null, null, false, true, null]],
        1441 => [[['_route' => 'view-data-ignore.type', '_handler' => 'fetch', 'view' => 1], ['data', 'ignore', 'type'], null, null, false, true, null]],
        1502 => [[['_route' => 'download-data-db-ignore.type', '_handler' => 'fetch'], ['data', 'db', 'ignore', 'type'], null, null, false, true, null]],
        1531 => [[['_route' => 'download-data-ignore.type', '_handler' => 'fetch'], ['data', 'ignore', 'type'], null, null, false, true, null]],
        1565 => [[['_route' => 'epubfs-db-data-comp', '_handler' => 'epubfs'], ['db', 'data', 'comp'], null, null, false, true, null]],
        1600 => [[['_route' => 'opds-page-id', '_handler' => 'opds'], ['page', 'id'], null, null, false, true, null]],
        1609 => [[['_route' => 'opds-page', '_handler' => 'opds'], ['page'], null, null, false, true, null]],
        1662 => [[['_route' => 'zipper-page-type-id', '_handler' => 'zipper'], ['page', 'type', 'id'], null, null, false, true, null]],
        1671 => [[['_route' => 'zipper-page-type', '_handler' => 'zipper'], ['page', 'type'], null, null, false, true, null]],
        1681 => [[['_route' => 'zipper-page', '_handler' => 'zipper'], ['page'], null, null, false, true, null]],
        1710 => [
            [['_route' => 'zipfs-db-data-comp', '_handler' => 'zipfs'], ['db', 'data', 'comp'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
