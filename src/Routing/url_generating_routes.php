<?php

// This file has been auto-generated by the Symfony Routing Component.

return [
    'page-index' => [[], ['page' => 'index', '_route' => 'page-index'], [], [['text', '/index']], [], [], []],
    'page-authors-letter' => [['id'], ['page' => 'authors_letter', '_route' => 'page-authors-letter'], [], [['variable', '/', '[^/]++', 'id'], ['text', '/authors/letter']], [], [], []],
    'page-authors-letters' => [[], ['page' => 'authors', 'letter' => 1, '_route' => 'page-authors-letters'], [], [['text', '/authors/letter']], [], [], []],
    'page-author' => [['id', 'title'], ['page' => 'author', '_route' => 'page-author'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/authors']], [], [], []],
    'page-author-id' => [['id'], ['page' => 'author', '_route' => 'page-author-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/authors']], [], [], []],
    'page-authors' => [[], ['page' => 'authors', '_route' => 'page-authors'], [], [['text', '/authors']], [], [], []],
    'page-books-letter' => [['id'], ['page' => 'books_letter', '_route' => 'page-books-letter'], ['id' => '\\w'], [['variable', '/', '\\w', 'id'], ['text', '/books/letter']], [], [], []],
    'page-books-letters' => [[], ['page' => 'books', 'letter' => 1, '_route' => 'page-books-letters'], [], [['text', '/books/letter']], [], [], []],
    'page-books-year' => [['id'], ['page' => 'books_year', '_route' => 'page-books-year'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/books/year']], [], [], []],
    'page-books-years' => [[], ['page' => 'books', 'year' => 1, '_route' => 'page-books-years'], [], [['text', '/books/year']], [], [], []],
    'page-book' => [['id', 'author', 'title'], ['page' => 'book', '_route' => 'page-book'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '[^/]++', 'author'], ['variable', '/', '\\d+', 'id'], ['text', '/books']], [], [], []],
    'page-book-id' => [['id'], ['page' => 'book', '_route' => 'page-book-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/books']], [], [], []],
    'page-books' => [[], ['page' => 'books', '_route' => 'page-books'], [], [['text', '/books']], [], [], []],
    'page-serie' => [['id', 'title'], ['page' => 'serie', '_route' => 'page-serie'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/series']], [], [], []],
    'page-serie-id' => [['id'], ['page' => 'serie', '_route' => 'page-serie-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/series']], [], [], []],
    'page-series' => [[], ['page' => 'series', '_route' => 'page-series'], [], [['text', '/series']], [], [], []],
    'page-typeahead' => [[], ['page' => 'query', 'search' => 1, '_route' => 'page-typeahead'], [], [['text', '/typeahead']], [], [], []],
    'page-query-scope' => [['query', 'scope'], ['page' => 'query', '_route' => 'page-query-scope'], [], [['variable', '/', '[^/]++', 'scope'], ['variable', '/', '[^/]++', 'query'], ['text', '/search']], [], [], []],
    'page-query' => [['query'], ['page' => 'query', '_route' => 'page-query'], [], [['variable', '/', '[^/]++', 'query'], ['text', '/search']], [], [], []],
    'page-search' => [[], ['page' => 'opensearch', '_route' => 'page-search'], [], [['text', '/search']], [], [], []],
    'page-recent' => [[], ['page' => 'recent', '_route' => 'page-recent'], [], [['text', '/recent']], [], [], []],
    'page-tag' => [['id', 'title'], ['page' => 'tag', '_route' => 'page-tag'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/tags']], [], [], []],
    'page-tag-id' => [['id'], ['page' => 'tag', '_route' => 'page-tag-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/tags']], [], [], []],
    'page-tags' => [[], ['page' => 'tags', '_route' => 'page-tags'], [], [['text', '/tags']], [], [], []],
    'page-custom' => [['custom', 'id'], ['page' => 'custom', '_route' => 'page-custom'], ['custom' => '\\d+'], [['variable', '/', '[^/]++', 'id'], ['variable', '/', '\\d+', 'custom'], ['text', '/custom']], [], [], []],
    'page-customtype' => [['custom'], ['page' => 'customtype', '_route' => 'page-customtype'], ['custom' => '\\d+'], [['variable', '/', '\\d+', 'custom'], ['text', '/custom']], [], [], []],
    'page-about' => [[], ['page' => 'about', '_route' => 'page-about'], [], [['text', '/about']], [], [], []],
    'page-language' => [['id', 'title'], ['page' => 'language', '_route' => 'page-language'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/languages']], [], [], []],
    'page-language-id' => [['id'], ['page' => 'language', '_route' => 'page-language-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/languages']], [], [], []],
    'page-languages' => [[], ['page' => 'languages', '_route' => 'page-languages'], [], [['text', '/languages']], [], [], []],
    'page-customize' => [[], ['page' => 'customize', '_route' => 'page-customize'], [], [['text', '/customize']], [], [], []],
    'page-publisher' => [['id', 'title'], ['page' => 'publisher', '_route' => 'page-publisher'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/publishers']], [], [], []],
    'page-publisher-id' => [['id'], ['page' => 'publisher', '_route' => 'page-publisher-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/publishers']], [], [], []],
    'page-publishers' => [[], ['page' => 'publishers', '_route' => 'page-publishers'], [], [['text', '/publishers']], [], [], []],
    'page-rating' => [['id', 'title'], ['page' => 'rating', '_route' => 'page-rating'], ['id' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'id'], ['text', '/ratings']], [], [], []],
    'page-rating-id' => [['id'], ['page' => 'rating', '_route' => 'page-rating-id'], ['id' => '\\d+'], [['variable', '/', '\\d+', 'id'], ['text', '/ratings']], [], [], []],
    'page-ratings' => [[], ['page' => 'ratings', '_route' => 'page-ratings'], [], [['text', '/ratings']], [], [], []],
    'page-identifier' => [['id', 'title'], ['page' => 'identifier', '_route' => 'page-identifier'], ['id' => '\\w+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\w+', 'id'], ['text', '/identifiers']], [], [], []],
    'page-identifier-id' => [['id'], ['page' => 'identifier', '_route' => 'page-identifier-id'], ['id' => '\\w+'], [['variable', '/', '\\w+', 'id'], ['text', '/identifiers']], [], [], []],
    'page-identifiers' => [[], ['page' => 'identifiers', '_route' => 'page-identifiers'], [], [['text', '/identifiers']], [], [], []],
    'page-format' => [['id'], ['page' => 'format', '_route' => 'page-format'], ['id' => '\\w+'], [['variable', '/', '\\w+', 'id'], ['text', '/formats']], [], [], []],
    'page-formats' => [[], ['page' => 'formats', '_route' => 'page-formats'], [], [['text', '/formats']], [], [], []],
    'page-libraries' => [[], ['page' => 'libraries', '_route' => 'page-libraries'], [], [['text', '/libraries']], [], [], []],
    'page-filter' => [[], ['page' => 'filter', '_route' => 'page-filter'], [], [['text', '/filter']], [], [], []],
    'feed-search' => [[], ['page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler', '_route' => 'feed-search'], [], [['text', '/feed/search']], [], [], []],
    'feed-page' => [['page'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler', '_route' => 'feed-page'], ['page' => '\\w+'], [['variable', '/', '\\w+', 'page'], ['text', '/feed']], [], [], []],
    'feed-path' => [['path'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler', '_route' => 'feed-path'], ['path' => '.+'], [['variable', '/', '.+', 'path'], ['text', '/feed']], [], [], []],
    'feed' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler', '_route' => 'feed'], [], [['text', '/feed']], [], [], []],
    'fetch-file' => [['db', 'id', 'file'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-file'], ['db' => '\\d+', 'id' => '\\d+', 'file' => '.+'], [['variable', '/', '.+', 'file'], ['variable', '/', '\\d+', 'id'], ['variable', '/', '\\d+', 'db'], ['text', '/files']], [], [], []],
    'fetch-thumb' => [['db', 'id', 'thumb'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-thumb'], ['db' => '\\d+', 'id' => '\\d+'], [['text', '.jpg'], ['variable', '/', '[^/\\.]++', 'thumb'], ['variable', '/', '\\d+', 'id'], ['variable', '/', '\\d+', 'db'], ['text', '/thumbs']], [], [], []],
    'fetch-cover' => [['db', 'id'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-cover'], ['db' => '\\d+', 'id' => '\\d+'], [['text', '.jpg'], ['variable', '/', '\\d+', 'id'], ['variable', '/', '\\d+', 'db'], ['text', '/covers']], [], [], []],
    'fetch-inline' => [['db', 'data', 'ignore', 'type'], ['view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-inline'], ['db' => '\\d+', 'data' => '\\d+'], [['variable', '.', '[^/]++', 'type'], ['variable', '/', '[^/\\.]++', 'ignore'], ['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/inline']], [], [], []],
    'fetch-data' => [['db', 'data', 'ignore', 'type'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-data'], ['db' => '\\d+', 'data' => '\\d+'], [['variable', '.', '[^/]++', 'type'], ['variable', '/', '[^/\\.]++', 'ignore'], ['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/fetch']], [], [], []],
    'fetch-view' => [['data', 'db', 'ignore', 'type'], ['view' => 1, '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-view'], [], [['variable', '.', '[^/]++', 'type'], ['variable', '/', '[^/\\.]++', 'ignore'], ['variable', '/', '[^/]++', 'db'], ['variable', '/', '[^/]++', 'data'], ['text', '/view']], [], [], []],
    'fetch-download' => [['data', 'db', 'ignore', 'type'], ['_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler', '_route' => 'fetch-download'], [], [['variable', '.', '[^/]++', 'type'], ['variable', '/', '[^/\\.]++', 'ignore'], ['variable', '/', '[^/]++', 'db'], ['variable', '/', '[^/]++', 'data'], ['text', '/download']], [], [], []],
    'read-title' => [['db', 'data', 'title'], ['_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler', '_route' => 'read-title'], ['db' => '\\d+', 'data' => '\\d+'], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/read']], [], [], []],
    'read' => [['db', 'data'], ['_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler', '_route' => 'read'], ['db' => '\\d+', 'data' => '\\d+'], [['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/read']], [], [], []],
    'epubfs' => [['db', 'data', 'comp'], ['_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler', '_route' => 'epubfs'], ['db' => '\\d+', 'data' => '\\d+', 'comp' => '.+'], [['variable', '/', '.+', 'comp'], ['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/epubfs']], [], [], []],
    'restapi-customtypes' => [[], ['_resource' => 'CustomColumnType', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-customtypes'], [], [['text', '/restapi/custom']], [], [], []],
    'restapi-database-table' => [['db', 'name'], ['_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-database-table'], [], [['variable', '/', '[^/]++', 'name'], ['variable', '/', '[^/]++', 'db'], ['text', '/restapi/databases']], [], [], []],
    'restapi-database' => [['db'], ['_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-database'], [], [['variable', '/', '[^/]++', 'db'], ['text', '/restapi/databases']], [], [], []],
    'restapi-databases' => [[], ['_resource' => 'Database', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-databases'], [], [['text', '/restapi/databases']], [], [], []],
    'restapi-openapi' => [[], ['_resource' => 'openapi', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-openapi'], [], [['text', '/restapi/openapi']], [], [], []],
    'restapi-route' => [[], ['_resource' => 'route', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-route'], [], [['text', '/restapi/routes']], [], [], []],
    'restapi-handler' => [[], ['_resource' => 'handler', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-handler'], [], [['text', '/restapi/handlers']], [], [], []],
    'restapi-note' => [['type', 'id', 'title'], ['_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-note'], [], [['variable', '/', '[^/]++', 'title'], ['variable', '/', '[^/]++', 'id'], ['variable', '/', '[^/]++', 'type'], ['text', '/restapi/notes']], [], [], []],
    'restapi-notes-type-id' => [['type', 'id'], ['_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-notes-type-id'], [], [['variable', '/', '[^/]++', 'id'], ['variable', '/', '[^/]++', 'type'], ['text', '/restapi/notes']], [], [], []],
    'restapi-notes-type' => [['type'], ['_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-notes-type'], [], [['variable', '/', '[^/]++', 'type'], ['text', '/restapi/notes']], [], [], []],
    'restapi-notes' => [[], ['_resource' => 'Note', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-notes'], [], [['text', '/restapi/notes']], [], [], []],
    'restapi-preference' => [['key'], ['_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-preference'], [], [['variable', '/', '[^/]++', 'key'], ['text', '/restapi/preferences']], [], [], []],
    'restapi-preferences' => [[], ['_resource' => 'Preference', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-preferences'], [], [['text', '/restapi/preferences']], [], [], []],
    'restapi-annotation' => [['bookId', 'id'], ['_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-annotation'], [], [['variable', '/', '[^/]++', 'id'], ['variable', '/', '[^/]++', 'bookId'], ['text', '/restapi/annotations']], [], [], []],
    'restapi-annotations-book' => [['bookId'], ['_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-annotations-book'], [], [['variable', '/', '[^/]++', 'bookId'], ['text', '/restapi/annotations']], [], [], []],
    'restapi-annotations' => [[], ['_resource' => 'Annotation', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-annotations'], [], [['text', '/restapi/annotations']], [], [], []],
    'restapi-metadata-element-name' => [['bookId', 'element', 'name'], ['_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-metadata-element-name'], [], [['variable', '/', '[^/]++', 'name'], ['variable', '/', '[^/]++', 'element'], ['variable', '/', '[^/]++', 'bookId'], ['text', '/restapi/metadata']], [], [], []],
    'restapi-metadata-element' => [['bookId', 'element'], ['_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-metadata-element'], [], [['variable', '/', '[^/]++', 'element'], ['variable', '/', '[^/]++', 'bookId'], ['text', '/restapi/metadata']], [], [], []],
    'restapi-metadata' => [['bookId'], ['_resource' => 'Metadata', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-metadata'], [], [['variable', '/', '[^/]++', 'bookId'], ['text', '/restapi/metadata']], [], [], []],
    'restapi-user-details' => [[], ['_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-user-details'], [], [['text', '/restapi/user/details']], [], [], []],
    'restapi-user' => [[], ['_resource' => 'User', '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-user'], [], [['text', '/restapi/user']], [], [], []],
    'restapi-path' => [['path'], ['_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler', '_route' => 'restapi-path'], ['path' => '.*'], [['variable', '/', '.*', 'path'], ['text', '/restapi']], [], [], []],
    'check-more' => [['more'], ['_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler', '_route' => 'check-more'], ['more' => '.*'], [['variable', '/', '.*', 'more'], ['text', '/check']], [], [], []],
    'check' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler', '_route' => 'check'], [], [['text', '/check']], [], [], []],
    'opds-search' => [[], ['page' => 'search', '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler', '_route' => 'opds-search'], [], [['text', '/opds/search']], [], [], []],
    'opds-page' => [['page'], ['_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler', '_route' => 'opds-page'], ['page' => '\\w+'], [['variable', '/', '\\w+', 'page'], ['text', '/opds']], [], [], []],
    'opds-path' => [['path'], ['_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler', '_route' => 'opds-path'], ['path' => '.*'], [['variable', '/', '.*', 'path'], ['text', '/opds']], [], [], []],
    'opds' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler', '_route' => 'opds'], [], [['text', '/opds']], [], [], []],
    'loader-action-dbNum-authorId-urlPath' => [['action', 'dbNum', 'authorId', 'urlPath'], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader-action-dbNum-authorId-urlPath'], ['dbNum' => '\\d+', 'authorId' => '\\w+', 'urlPath' => '.*'], [['variable', '/', '.*', 'urlPath'], ['variable', '/', '\\w+', 'authorId'], ['variable', '/', '\\d+', 'dbNum'], ['variable', '/', '[^/]++', 'action'], ['text', '/loader']], [], [], []],
    'loader-action-dbNum-authorId' => [['action', 'dbNum', 'authorId'], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader-action-dbNum-authorId'], ['dbNum' => '\\d+', 'authorId' => '\\w*'], [['variable', '/', '\\w*', 'authorId'], ['variable', '/', '\\d+', 'dbNum'], ['variable', '/', '[^/]++', 'action'], ['text', '/loader']], [], [], []],
    'loader-action-dbNum' => [['action', 'dbNum'], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader-action-dbNum'], ['dbNum' => '\\d+'], [['variable', '/', '\\d+', 'dbNum'], ['variable', '/', '[^/]++', 'action'], ['text', '/loader']], [], [], []],
    'loader-action-' => [['action'], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader-action-'], [], [['text', '/'], ['variable', '/', '[^/]++', 'action'], ['text', '/loader']], [], [], []],
    'loader-action' => [['action'], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader-action'], [], [['variable', '/', '[^/]++', 'action'], ['text', '/loader']], [], [], []],
    'loader' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler', '_route' => 'loader'], [], [['text', '/loader']], [], [], []],
    'zipper-page-id-type' => [['page', 'id', 'type'], ['_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler', '_route' => 'zipper-page-id-type'], [], [['text', '.zip'], ['variable', '/', '[^/\\.]++', 'type'], ['variable', '/', '[^/]++', 'id'], ['variable', '/', '[^/]++', 'page'], ['text', '/zipper']], [], [], []],
    'zipper-page-type' => [['page', 'type'], ['_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler', '_route' => 'zipper-page-type'], [], [['text', '.zip'], ['variable', '/', '[^/\\.]++', 'type'], ['variable', '/', '[^/]++', 'page'], ['text', '/zipper']], [], [], []],
    'calres' => [['db', 'alg', 'digest'], ['_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler', '_route' => 'calres'], ['db' => '\\d+'], [['variable', '/', '[^/]++', 'digest'], ['variable', '/', '[^/]++', 'alg'], ['variable', '/', '\\d+', 'db'], ['text', '/calres']], [], [], []],
    'zipfs' => [['db', 'data', 'comp'], ['_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler', '_route' => 'zipfs'], ['db' => '\\d+', 'data' => '\\d+', 'comp' => '.+'], [['variable', '/', '.+', 'comp'], ['variable', '/', '\\d+', 'data'], ['variable', '/', '\\d+', 'db'], ['text', '/zipfs']], [], [], []],
    'mail' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\MailHandler', '_route' => 'mail'], [], [['text', '/mail']], [], [], []],
    'graphql' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\GraphQLHandler', '_route' => 'graphql'], [], [['text', '/graphql']], [], [], []],
    'tables' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler', '_route' => 'tables'], [], [['text', '/tables']], [], [], []],
    'test' => [[], ['_handler' => 'SebLucas\\Cops\\Handlers\\TestHandler', '_route' => 'test'], [], [['text', '/test']], [], [], []],
];
