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
        '/custom' => [[['_route' => 'custom', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/databases' => [[['_route' => 'databases', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/openapi' => [[['_route' => 'openapi', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/routes' => [[['_route' => 'routes', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/pages' => [[['_route' => 'pages', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/notes' => [[['_route' => 'notes', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/preferences' => [[['_route' => 'preferences', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/annotations' => [[['_route' => 'annotations', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/user/details' => [[['_route' => 'user-details', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/user' => [[['_route' => 'user', '_handler' => 'restapi'], null, null, null, false, false, null]],
        '/opds' => [[['_route' => 'opds', '_handler' => 'opds'], null, null, null, false, false, null]],
        '/loader' => [[['_route' => 'loader', '_handler' => 'loader'], null, null, null, false, false, null]],
        '/mail' => [[['_route' => 'mail', '_handler' => 'mail'], null, null, null, false, false, null]],
        '/graphql' => [[['_route' => 'graphql', '_handler' => 'graphql'], null, null, null, false, false, null]],
        '/tables' => [[['_route' => 'tables', '_handler' => 'tables'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/a(?'
                    .'|uthors/(?'
                        .'|letter/([^/]++)(*:37)'
                        .'|(\\d+)/([^/]++)(*:58)'
                        .'|(\\d+)(*:70)'
                    .')'
                    .'|nnotations/([^/]++)(?'
                        .'|/([^/]++)(*:109)'
                        .'|(*:117)'
                    .')'
                .')'
                .'|/books/(?'
                    .'|letter/(\\w)(*:148)'
                    .'|year/(\\d+)(*:166)'
                    .'|(\\d+)/([^/]++)/([^/]++)(*:197)'
                    .'|(\\d+)(*:210)'
                .')'
                .'|/se(?'
                    .'|ries/(?'
                        .'|(\\d+)/([^/]++)(*:247)'
                        .'|(\\d+)(*:260)'
                    .')'
                    .'|arch/([^/]++)(?'
                        .'|/([^/]++)(*:294)'
                        .'|(*:302)'
                    .')'
                .')'
                .'|/query/([^/]++)(?'
                    .'|/([^/]++)(*:339)'
                    .'|(*:347)'
                .')'
                .'|/t(?'
                    .'|ags/(?'
                        .'|(\\d+)/([^/]++)(*:382)'
                        .'|(\\d+)(*:395)'
                    .')'
                    .'|humbs/([^/]++)/(\\d+)/(\\d+)\\.jpg(*:435)'
                .')'
                .'|/c(?'
                    .'|ustom/(?'
                        .'|(\\d+)/([^/]++)(*:472)'
                        .'|(\\d+)(*:485)'
                    .')'
                    .'|overs/(\\d+)/(\\d+)\\.jpg(*:516)'
                    .'|heck(?'
                        .'|/(.*)(*:536)'
                        .'|(*:544)'
                    .')'
                    .'|alres/(\\d+)/([^/]++)/([^/]++)(*:582)'
                .')'
                .'|/l(?'
                    .'|anguages/(?'
                        .'|(\\d+)/([^/]++)(*:622)'
                        .'|(\\d+)(*:635)'
                    .')'
                    .'|oader/([^/]++)(?'
                        .'|/(?'
                            .'|(\\d+)/(\\w+)/(.*)(*:681)'
                            .'|(\\d+)/(\\w*)(*:700)'
                            .'|(\\d+)(*:713)'
                        .')'
                        .'|(*:722)'
                        .'|(*:730)'
                    .')'
                .')'
                .'|/p(?'
                    .'|ublishers/(?'
                        .'|(\\d+)/([^/]++)(*:772)'
                        .'|(\\d+)(*:785)'
                    .')'
                    .'|references/([^/]++)(*:813)'
                .')'
                .'|/r(?'
                    .'|atings/(?'
                        .'|(\\d+)/([^/]++)(*:851)'
                        .'|(\\d+)(*:864)'
                    .')'
                    .'|e(?'
                        .'|ad/(?'
                            .'|(\\d+)/(\\d+)/([^/]++)(*:903)'
                            .'|(\\d+)/(\\d+)(*:922)'
                        .')'
                        .'|stapi(?'
                            .'|/(.*)(*:944)'
                            .'|(*:952)'
                        .')'
                    .')'
                .')'
                .'|/i(?'
                    .'|dentifiers/(?'
                        .'|(\\w+)/([^/]++)(*:996)'
                        .'|(\\w+)(*:1009)'
                    .')'
                    .'|nline/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1057)'
                .')'
                .'|/f(?'
                    .'|e(?'
                        .'|ed/([^/]++)(?'
                            .'|/([^/]++)(*:1099)'
                            .'|(*:1108)'
                        .')'
                        .'|tch/(\\d+)/(\\d+)/([^/\\.]++)\\.([^/]++)(*:1154)'
                    .')'
                    .'|iles/(\\d+)/(\\d+)/(.+)(*:1185)'
                .')'
                .'|/view/([^/]++)/(?'
                    .'|([^/]++)/([^/\\.]++)\\.([^/]++)(*:1242)'
                    .'|([^/\\.]++)\\.([^/]++)(*:1271)'
                .')'
                .'|/d(?'
                    .'|ownload/([^/]++)/(?'
                        .'|([^/]++)/([^/\\.]++)\\.([^/]++)(*:1335)'
                        .'|([^/\\.]++)\\.([^/]++)(*:1364)'
                    .')'
                    .'|atabases/([^/]++)(?'
                        .'|/([^/]++)(*:1403)'
                        .'|(*:1412)'
                    .')'
                .')'
                .'|/epubfs/(\\d+)/(\\d+)/(.+)(*:1447)'
                .'|/notes/([^/]++)(?'
                    .'|/([^/]++)(?'
                        .'|/([^/]++)(*:1495)'
                        .'|(*:1504)'
                    .')'
                    .'|(*:1514)'
                .')'
                .'|/metadata/([^/]++)(?'
                    .'|/([^/]++)(?'
                        .'|/([^/]++)(*:1566)'
                        .'|(*:1575)'
                    .')'
                    .'|(*:1585)'
                .')'
                .'|/opds/([^/]++)(?'
                    .'|/([^/]++)(*:1621)'
                    .'|(*:1630)'
                .')'
                .'|/zip(?'
                    .'|per/([^/]++)(?'
                        .'|/([^/]++)(?'
                            .'|/([^/]++)(*:1683)'
                            .'|(*:1692)'
                        .')'
                        .'|(*:1702)'
                    .')'
                    .'|fs/(\\d+)/(\\d+)/(.+)(*:1731)'
                .')'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        37 => [[['_route' => 'authors-letter-id', 'page' => '2'], ['id'], null, null, false, true, null]],
        58 => [[['_route' => 'authors-id-title', 'page' => '3'], ['id', 'title'], null, null, false, true, null]],
        70 => [[['_route' => 'authors-id', 'page' => '3'], ['id'], null, null, false, true, null]],
        109 => [[['_route' => 'annotations-bookId-id', '_handler' => 'restapi'], ['bookId', 'id'], null, null, false, true, null]],
        117 => [[['_route' => 'annotations-bookId', '_handler' => 'restapi'], ['bookId'], null, null, false, true, null]],
        148 => [[['_route' => 'books-letter-id', 'page' => '5'], ['id'], null, null, false, true, null]],
        166 => [[['_route' => 'books-year-id', 'page' => '50'], ['id'], null, null, false, true, null]],
        197 => [[['_route' => 'books-id-author-title', 'page' => '13'], ['id', 'author', 'title'], null, null, false, true, null]],
        210 => [[['_route' => 'books-id', 'page' => '13'], ['id'], null, null, false, true, null]],
        247 => [[['_route' => 'series-id-title', 'page' => '7'], ['id', 'title'], null, null, false, true, null]],
        260 => [[['_route' => 'series-id', 'page' => '7'], ['id'], null, null, false, true, null]],
        294 => [[['_route' => 'search-query-scope', 'page' => '9'], ['query', 'scope'], null, null, false, true, null]],
        302 => [[['_route' => 'search-query', 'page' => '9'], ['query'], null, null, false, true, null]],
        339 => [[['_route' => 'query-query-scope', 'page' => '9', 'search' => 1], ['query', 'scope'], null, null, false, true, null]],
        347 => [[['_route' => 'query-query', 'page' => '9', 'search' => 1], ['query'], null, null, false, true, null]],
        382 => [[['_route' => 'tags-id-title', 'page' => '12'], ['id', 'title'], null, null, false, true, null]],
        395 => [[['_route' => 'tags-id', 'page' => '12'], ['id'], null, null, false, true, null]],
        435 => [[['_route' => 'thumbs-thumb-db-id', '_handler' => 'fetch'], ['thumb', 'db', 'id'], null, null, false, false, null]],
        472 => [[['_route' => 'custom-custom-id', 'page' => '15'], ['custom', 'id'], null, null, false, true, null]],
        485 => [[['_route' => 'custom-custom', 'page' => '14'], ['custom'], null, null, false, true, null]],
        516 => [[['_route' => 'covers-db-id', '_handler' => 'fetch'], ['db', 'id'], null, null, false, false, null]],
        536 => [[['_route' => 'check-more', '_handler' => 'check'], ['more'], null, null, false, true, null]],
        544 => [[['_route' => 'check', '_handler' => 'check'], [], null, null, false, false, null]],
        582 => [[['_route' => 'calres-db-alg-digest', '_handler' => 'calres'], ['db', 'alg', 'digest'], null, null, false, true, null]],
        622 => [[['_route' => 'languages-id-title', 'page' => '18'], ['id', 'title'], null, null, false, true, null]],
        635 => [[['_route' => 'languages-id', 'page' => '18'], ['id'], null, null, false, true, null]],
        681 => [[['_route' => 'loader-action-dbNum-authorId-urlPath', '_handler' => 'loader'], ['action', 'dbNum', 'authorId', 'urlPath'], null, null, false, true, null]],
        700 => [[['_route' => 'loader-action-dbNum-authorId', '_handler' => 'loader'], ['action', 'dbNum', 'authorId'], null, null, false, true, null]],
        713 => [[['_route' => 'loader-action-dbNum', '_handler' => 'loader'], ['action', 'dbNum'], null, null, false, true, null]],
        722 => [[['_route' => 'loader-action-', '_handler' => 'loader'], ['action'], null, null, true, true, null]],
        730 => [[['_route' => 'loader-action', '_handler' => 'loader'], ['action'], null, null, false, true, null]],
        772 => [[['_route' => 'publishers-id-title', 'page' => '21'], ['id', 'title'], null, null, false, true, null]],
        785 => [[['_route' => 'publishers-id', 'page' => '21'], ['id'], null, null, false, true, null]],
        813 => [[['_route' => 'preferences-key', '_handler' => 'restapi'], ['key'], null, null, false, true, null]],
        851 => [[['_route' => 'ratings-id-title', 'page' => '23'], ['id', 'title'], null, null, false, true, null]],
        864 => [[['_route' => 'ratings-id', 'page' => '23'], ['id'], null, null, false, true, null]],
        903 => [[['_route' => 'read-db-data-title', '_handler' => 'read'], ['db', 'data', 'title'], null, null, false, true, null]],
        922 => [[['_route' => 'read-db-data', '_handler' => 'read'], ['db', 'data'], null, null, false, true, null]],
        944 => [[['_route' => 'restapi-route', '_handler' => 'restapi'], ['route'], null, null, false, true, null]],
        952 => [[['_route' => 'restapi', '_handler' => 'restapi'], [], null, null, false, false, null]],
        996 => [[['_route' => 'identifiers-id-title', 'page' => '42'], ['id', 'title'], null, null, false, true, null]],
        1009 => [[['_route' => 'identifiers-id', 'page' => '42'], ['id'], null, null, false, true, null]],
        1057 => [[['_route' => 'inline-db-data-ignore.type', '_handler' => 'fetch', 'view' => 1], ['db', 'data', 'ignore', 'type'], null, null, false, true, null]],
        1099 => [[['_route' => 'feed-page-id', '_handler' => 'feed'], ['page', 'id'], null, null, false, true, null]],
        1108 => [[['_route' => 'feed-page', '_handler' => 'feed'], ['page'], null, null, false, true, null]],
        1154 => [[['_route' => 'fetch-db-data-ignore.type', '_handler' => 'fetch'], ['db', 'data', 'ignore', 'type'], null, null, false, true, null]],
        1185 => [[['_route' => 'files-db-id-file', '_handler' => 'fetch'], ['db', 'id', 'file'], null, null, false, true, null]],
        1242 => [[['_route' => 'view-data-db-ignore.type', '_handler' => 'fetch', 'view' => 1], ['data', 'db', 'ignore', 'type'], null, null, false, true, null]],
        1271 => [[['_route' => 'view-data-ignore.type', '_handler' => 'fetch', 'view' => 1], ['data', 'ignore', 'type'], null, null, false, true, null]],
        1335 => [[['_route' => 'download-data-db-ignore.type', '_handler' => 'fetch'], ['data', 'db', 'ignore', 'type'], null, null, false, true, null]],
        1364 => [[['_route' => 'download-data-ignore.type', '_handler' => 'fetch'], ['data', 'ignore', 'type'], null, null, false, true, null]],
        1403 => [[['_route' => 'databases-db-name', '_handler' => 'restapi'], ['db', 'name'], null, null, false, true, null]],
        1412 => [[['_route' => 'databases-db', '_handler' => 'restapi'], ['db'], null, null, false, true, null]],
        1447 => [[['_route' => 'epubfs-db-data-comp', '_handler' => 'epubfs'], ['db', 'data', 'comp'], null, null, false, true, null]],
        1495 => [[['_route' => 'notes-type-id-title', '_handler' => 'restapi'], ['type', 'id', 'title'], null, null, false, true, null]],
        1504 => [[['_route' => 'notes-type-id', '_handler' => 'restapi'], ['type', 'id'], null, null, false, true, null]],
        1514 => [[['_route' => 'notes-type', '_handler' => 'restapi'], ['type'], null, null, false, true, null]],
        1566 => [[['_route' => 'metadata-bookId-element-name', '_handler' => 'restapi'], ['bookId', 'element', 'name'], null, null, false, true, null]],
        1575 => [[['_route' => 'metadata-bookId-element', '_handler' => 'restapi'], ['bookId', 'element'], null, null, false, true, null]],
        1585 => [[['_route' => 'metadata-bookId', '_handler' => 'restapi'], ['bookId'], null, null, false, true, null]],
        1621 => [[['_route' => 'opds-page-id', '_handler' => 'opds'], ['page', 'id'], null, null, false, true, null]],
        1630 => [[['_route' => 'opds-page', '_handler' => 'opds'], ['page'], null, null, false, true, null]],
        1683 => [[['_route' => 'zipper-page-type-id', '_handler' => 'zipper'], ['page', 'type', 'id'], null, null, false, true, null]],
        1692 => [[['_route' => 'zipper-page-type', '_handler' => 'zipper'], ['page', 'type'], null, null, false, true, null]],
        1702 => [[['_route' => 'zipper-page', '_handler' => 'zipper'], ['page'], null, null, false, true, null]],
        1731 => [
            [['_route' => 'zipfs-db-data-comp', '_handler' => 'zipfs'], ['db', 'data', 'comp'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
