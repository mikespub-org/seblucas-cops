<?php return array (
  0 => 
  array (
    'GET' => 
    array (
      '/index' => 
      array (
        0 => 
        array (
          'page' => 'index',
          '_route' => 'page-index',
        ),
        1 => 
        array (
          '_route' => '/index',
          '_name' => 'page-index',
        ),
      ),
      '/authors/letter' => 
      array (
        0 => 
        array (
          'page' => '1',
          'letter' => 1,
          '_route' => 'page-1-letter',
        ),
        1 => 
        array (
          '_route' => '/authors/letter',
          '_name' => 'page-1-letter',
        ),
      ),
      '/authors' => 
      array (
        0 => 
        array (
          'page' => '1',
          '_route' => 'page-authors',
        ),
        1 => 
        array (
          '_route' => '/authors',
          '_name' => 'page-authors',
        ),
      ),
      '/books/letter' => 
      array (
        0 => 
        array (
          'page' => '4',
          'letter' => 1,
          '_route' => 'page-4-letter',
        ),
        1 => 
        array (
          '_route' => '/books/letter',
          '_name' => 'page-4-letter',
        ),
      ),
      '/books/year' => 
      array (
        0 => 
        array (
          'page' => '4',
          'year' => 1,
          '_route' => 'page-4-year',
        ),
        1 => 
        array (
          '_route' => '/books/year',
          '_name' => 'page-4-year',
        ),
      ),
      '/books' => 
      array (
        0 => 
        array (
          'page' => '4',
          '_route' => 'page-books',
        ),
        1 => 
        array (
          '_route' => '/books',
          '_name' => 'page-books',
        ),
      ),
      '/series' => 
      array (
        0 => 
        array (
          'page' => '6',
          '_route' => 'page-series',
        ),
        1 => 
        array (
          '_route' => '/series',
          '_name' => 'page-series',
        ),
      ),
      '/typeahead' => 
      array (
        0 => 
        array (
          'page' => '9',
          'search' => 1,
          '_route' => 'page-typeahead',
        ),
        1 => 
        array (
          '_route' => '/typeahead',
          '_name' => 'page-typeahead',
        ),
      ),
      '/search' => 
      array (
        0 => 
        array (
          'page' => '8',
          '_route' => 'page-search',
        ),
        1 => 
        array (
          '_route' => '/search',
          '_name' => 'page-search',
        ),
      ),
      '/recent' => 
      array (
        0 => 
        array (
          'page' => '10',
          '_route' => 'page-recent',
        ),
        1 => 
        array (
          '_route' => '/recent',
          '_name' => 'page-recent',
        ),
      ),
      '/tags' => 
      array (
        0 => 
        array (
          'page' => '11',
          '_route' => 'page-tags',
        ),
        1 => 
        array (
          '_route' => '/tags',
          '_name' => 'page-tags',
        ),
      ),
      '/about' => 
      array (
        0 => 
        array (
          'page' => '16',
          '_route' => 'page-about',
        ),
        1 => 
        array (
          '_route' => '/about',
          '_name' => 'page-about',
        ),
      ),
      '/languages' => 
      array (
        0 => 
        array (
          'page' => '17',
          '_route' => 'page-languages',
        ),
        1 => 
        array (
          '_route' => '/languages',
          '_name' => 'page-languages',
        ),
      ),
      '/customize' => 
      array (
        0 => 
        array (
          'page' => '19',
          '_route' => 'page-customize',
        ),
        1 => 
        array (
          '_route' => '/customize',
          '_name' => 'page-customize',
        ),
      ),
      '/publishers' => 
      array (
        0 => 
        array (
          'page' => '20',
          '_route' => 'page-publishers',
        ),
        1 => 
        array (
          '_route' => '/publishers',
          '_name' => 'page-publishers',
        ),
      ),
      '/ratings' => 
      array (
        0 => 
        array (
          'page' => '22',
          '_route' => 'page-ratings',
        ),
        1 => 
        array (
          '_route' => '/ratings',
          '_name' => 'page-ratings',
        ),
      ),
      '/identifiers' => 
      array (
        0 => 
        array (
          'page' => '41',
          '_route' => 'page-identifiers',
        ),
        1 => 
        array (
          '_route' => '/identifiers',
          '_name' => 'page-identifiers',
        ),
      ),
      '/libraries' => 
      array (
        0 => 
        array (
          'page' => '43',
          '_route' => 'page-libraries',
        ),
        1 => 
        array (
          '_route' => '/libraries',
          '_name' => 'page-libraries',
        ),
      ),
      '/feed/search' => 
      array (
        0 => 
        array (
          'page' => 'search',
          '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler',
          '_route' => 'feed-search',
        ),
        1 => 
        array (
          '_route' => '/feed/search',
          '_name' => 'feed-search',
        ),
      ),
      '/feed' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler',
          '_route' => 'feed',
        ),
        1 => 
        array (
          '_route' => '/feed',
          '_name' => 'feed',
        ),
      ),
      '/restapi/custom' => 
      array (
        0 => 
        array (
          '_resource' => 'CustomColumnType',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-customtypes',
        ),
        1 => 
        array (
          '_route' => '/restapi/custom',
          '_name' => 'restapi-customtypes',
        ),
      ),
      '/restapi/databases' => 
      array (
        0 => 
        array (
          '_resource' => 'Database',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-databases',
        ),
        1 => 
        array (
          '_route' => '/restapi/databases',
          '_name' => 'restapi-databases',
        ),
      ),
      '/restapi/openapi' => 
      array (
        0 => 
        array (
          '_resource' => 'openapi',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-openapi',
        ),
        1 => 
        array (
          '_route' => '/restapi/openapi',
          '_name' => 'restapi-openapi',
        ),
      ),
      '/restapi/routes' => 
      array (
        0 => 
        array (
          '_resource' => 'route',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-route',
        ),
        1 => 
        array (
          '_route' => '/restapi/routes',
          '_name' => 'restapi-route',
        ),
      ),
      '/restapi/handlers' => 
      array (
        0 => 
        array (
          '_resource' => 'handler',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-handler',
        ),
        1 => 
        array (
          '_route' => '/restapi/handlers',
          '_name' => 'restapi-handler',
        ),
      ),
      '/restapi/notes' => 
      array (
        0 => 
        array (
          '_resource' => 'Note',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-notes',
        ),
        1 => 
        array (
          '_route' => '/restapi/notes',
          '_name' => 'restapi-notes',
        ),
      ),
      '/restapi/preferences' => 
      array (
        0 => 
        array (
          '_resource' => 'Preference',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-preferences',
        ),
        1 => 
        array (
          '_route' => '/restapi/preferences',
          '_name' => 'restapi-preferences',
        ),
      ),
      '/restapi/annotations' => 
      array (
        0 => 
        array (
          '_resource' => 'Annotation',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-annotations',
        ),
        1 => 
        array (
          '_route' => '/restapi/annotations',
          '_name' => 'restapi-annotations',
        ),
      ),
      '/restapi/user/details' => 
      array (
        0 => 
        array (
          '_resource' => 'User',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-user-details',
        ),
        1 => 
        array (
          '_route' => '/restapi/user/details',
          '_name' => 'restapi-user-details',
        ),
      ),
      '/restapi/user' => 
      array (
        0 => 
        array (
          '_resource' => 'User',
          '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
          '_route' => 'restapi-user',
        ),
        1 => 
        array (
          '_route' => '/restapi/user',
          '_name' => 'restapi-user',
        ),
      ),
      '/check' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler',
          '_route' => 'check',
        ),
        1 => 
        array (
          '_route' => '/check',
          '_name' => 'check',
        ),
      ),
      '/opds/search' => 
      array (
        0 => 
        array (
          'page' => 'search',
          '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler',
          '_route' => 'opds-search',
        ),
        1 => 
        array (
          '_route' => '/opds/search',
          '_name' => 'opds-search',
        ),
      ),
      '/opds' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler',
          '_route' => 'opds',
        ),
        1 => 
        array (
          '_route' => '/opds',
          '_name' => 'opds',
        ),
      ),
      '/loader' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
          '_route' => 'loader',
        ),
        1 => 
        array (
          '_route' => '/loader',
          '_name' => 'loader',
        ),
      ),
      '/graphql' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\GraphQLHandler',
          '_route' => 'graphql',
        ),
        1 => 
        array (
          '_route' => '/graphql',
          '_name' => 'graphql',
        ),
      ),
      '/tables' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\TableHandler',
          '_route' => 'tables',
        ),
        1 => 
        array (
          '_route' => '/tables',
          '_name' => 'tables',
        ),
      ),
      '/test' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\TestHandler',
          '_route' => 'test',
        ),
        1 => 
        array (
          '_route' => '/test',
          '_name' => 'test',
        ),
      ),
    ),
    'POST' => 
    array (
      '/mail' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\MailHandler',
          '_route' => 'mail',
        ),
        1 => 
        array (
          '_route' => '/mail',
          '_name' => 'mail',
        ),
      ),
      '/graphql' => 
      array (
        0 => 
        array (
          '_handler' => 'SebLucas\\Cops\\Handlers\\GraphQLHandler',
          '_route' => 'graphql',
        ),
        1 => 
        array (
          '_route' => '/graphql',
          '_name' => 'graphql',
        ),
      ),
    ),
  ),
  1 => 
  array (
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/authors/letter/([^/]+)(*MARK:a)|/authors/(\\d+)/([^/]+)(*MARK:b)|/authors/(\\d+)(*MARK:c)|/books/letter/(\\w)(*MARK:d)|/books/year/(\\d+)(*MARK:e)|/books/(\\d+)/([^/]+)/([^/]+)(*MARK:f)|/books/(\\d+)(*MARK:g)|/series/(\\d+)/([^/]+)(*MARK:h)|/series/(\\d+)(*MARK:i)|/search/([^/]+)/([^/]+)(*MARK:j)|/search/([^/]+)(*MARK:k)|/tags/(\\d+)/([^/]+)(*MARK:l)|/tags/(\\d+)(*MARK:m)|/custom/(\\d+)/([^/]+)(*MARK:n)|/custom/(\\d+)(*MARK:o)|/languages/(\\d+)/([^/]+)(*MARK:p)|/languages/(\\d+)(*MARK:q)|/publishers/(\\d+)/([^/]+)(*MARK:r)|/publishers/(\\d+)(*MARK:s)|/ratings/(\\d+)/([^/]+)(*MARK:t)|/ratings/(\\d+)(*MARK:u)|/identifiers/(\\w+)/([^/]+)(*MARK:v)|/identifiers/(\\w+)(*MARK:w)|/feed/(\\d+)/([^/]+)(*MARK:x)|/feed/(\\d+)(*MARK:y)|/feed/(.+)(*MARK:z)|/files/(\\d+)/(\\d+)/(.+)(*MARK:aa)|/thumbs/(\\d+)/(\\d+)/([^/]+)\\.jpg(*MARK:ab)|/covers/(\\d+)/(\\d+)\\.jpg(*MARK:ac)|/inline/(\\d+)/(\\d+)/([^/]+)\\.([^/]+)(*MARK:ad)|/fetch/(\\d+)/(\\d+)/([^/]+)\\.([^/]+)(*MARK:ae))$~',
        'routeMap' => 
        array (
          'a' => 
          array (
            0 => 
            array (
              'page' => '2',
              '_route' => 'page-authors-letter',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/authors/letter/{id}',
              '_name' => 'page-authors-letter',
            ),
          ),
          'b' => 
          array (
            0 => 
            array (
              'page' => '3',
              '_route' => 'page-author',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/authors/{id:\\d+}/{title}',
              '_name' => 'page-author',
            ),
          ),
          'c' => 
          array (
            0 => 
            array (
              'page' => '3',
              '_route' => 'page-3-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/authors/{id:\\d+}',
              '_name' => 'page-3-id',
            ),
          ),
          'd' => 
          array (
            0 => 
            array (
              'page' => '5',
              '_route' => 'page-books-letter',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/books/letter/{id:\\w}',
              '_name' => 'page-books-letter',
            ),
          ),
          'e' => 
          array (
            0 => 
            array (
              'page' => '50',
              '_route' => 'page-books-year',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/books/year/{id:\\d+}',
              '_name' => 'page-books-year',
            ),
          ),
          'f' => 
          array (
            0 => 
            array (
              'page' => '13',
              '_route' => 'page-book',
            ),
            1 => 
            array (
              'id' => 'id',
              'author' => 'author',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/books/{id:\\d+}/{author}/{title}',
              '_name' => 'page-book',
            ),
          ),
          'g' => 
          array (
            0 => 
            array (
              'page' => '13',
              '_route' => 'page-13-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/books/{id:\\d+}',
              '_name' => 'page-13-id',
            ),
          ),
          'h' => 
          array (
            0 => 
            array (
              'page' => '7',
              '_route' => 'page-serie',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/series/{id:\\d+}/{title}',
              '_name' => 'page-serie',
            ),
          ),
          'i' => 
          array (
            0 => 
            array (
              'page' => '7',
              '_route' => 'page-7-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/series/{id:\\d+}',
              '_name' => 'page-7-id',
            ),
          ),
          'j' => 
          array (
            0 => 
            array (
              'page' => '9',
              '_route' => 'page-query-scope',
            ),
            1 => 
            array (
              'query' => 'query',
              'scope' => 'scope',
            ),
            2 => 
            array (
              '_route' => '/search/{query}/{scope}',
              '_name' => 'page-query-scope',
            ),
          ),
          'k' => 
          array (
            0 => 
            array (
              'page' => '9',
              '_route' => 'page-query',
            ),
            1 => 
            array (
              'query' => 'query',
            ),
            2 => 
            array (
              '_route' => '/search/{query}',
              '_name' => 'page-query',
            ),
          ),
          'l' => 
          array (
            0 => 
            array (
              'page' => '12',
              '_route' => 'page-tag',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/tags/{id:\\d+}/{title}',
              '_name' => 'page-tag',
            ),
          ),
          'm' => 
          array (
            0 => 
            array (
              'page' => '12',
              '_route' => 'page-12-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/tags/{id:\\d+}',
              '_name' => 'page-12-id',
            ),
          ),
          'n' => 
          array (
            0 => 
            array (
              'page' => '15',
              '_route' => 'page-custom',
            ),
            1 => 
            array (
              'custom' => 'custom',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/custom/{custom:\\d+}/{id}',
              '_name' => 'page-custom',
            ),
          ),
          'o' => 
          array (
            0 => 
            array (
              'page' => '14',
              '_route' => 'page-customtype',
            ),
            1 => 
            array (
              'custom' => 'custom',
            ),
            2 => 
            array (
              '_route' => '/custom/{custom:\\d+}',
              '_name' => 'page-customtype',
            ),
          ),
          'p' => 
          array (
            0 => 
            array (
              'page' => '18',
              '_route' => 'page-language',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/languages/{id:\\d+}/{title}',
              '_name' => 'page-language',
            ),
          ),
          'q' => 
          array (
            0 => 
            array (
              'page' => '18',
              '_route' => 'page-18-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/languages/{id:\\d+}',
              '_name' => 'page-18-id',
            ),
          ),
          'r' => 
          array (
            0 => 
            array (
              'page' => '21',
              '_route' => 'page-publisher',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/publishers/{id:\\d+}/{title}',
              '_name' => 'page-publisher',
            ),
          ),
          's' => 
          array (
            0 => 
            array (
              'page' => '21',
              '_route' => 'page-21-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/publishers/{id:\\d+}',
              '_name' => 'page-21-id',
            ),
          ),
          't' => 
          array (
            0 => 
            array (
              'page' => '23',
              '_route' => 'page-rating',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/ratings/{id:\\d+}/{title}',
              '_name' => 'page-rating',
            ),
          ),
          'u' => 
          array (
            0 => 
            array (
              'page' => '23',
              '_route' => 'page-23-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/ratings/{id:\\d+}',
              '_name' => 'page-23-id',
            ),
          ),
          'v' => 
          array (
            0 => 
            array (
              'page' => '42',
              '_route' => 'page-identifier',
            ),
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/identifiers/{id:\\w+}/{title}',
              '_name' => 'page-identifier',
            ),
          ),
          'w' => 
          array (
            0 => 
            array (
              'page' => '42',
              '_route' => 'page-42-id',
            ),
            1 => 
            array (
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/identifiers/{id:\\w+}',
              '_name' => 'page-42-id',
            ),
          ),
          'x' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler',
              '_route' => 'feed-page-id',
            ),
            1 => 
            array (
              'page' => 'page',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/feed/{page:\\d+}/{id}',
              '_name' => 'feed-page-id',
            ),
          ),
          'y' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler',
              '_route' => 'feed-page',
            ),
            1 => 
            array (
              'page' => 'page',
            ),
            2 => 
            array (
              '_route' => '/feed/{page:\\d+}',
              '_name' => 'feed-page',
            ),
          ),
          'z' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FeedHandler',
              '_route' => 'feed-path',
            ),
            1 => 
            array (
              'path' => 'path',
            ),
            2 => 
            array (
              '_route' => '/feed/{path:.+}',
              '_name' => 'feed-path',
            ),
          ),
          'aa' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-file',
            ),
            1 => 
            array (
              'db' => 'db',
              'id' => 'id',
              'file' => 'file',
            ),
            2 => 
            array (
              '_route' => '/files/{db:\\d+}/{id:\\d+}/{file:.+}',
              '_name' => 'fetch-file',
            ),
          ),
          'ab' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-thumb',
            ),
            1 => 
            array (
              'db' => 'db',
              'id' => 'id',
              'thumb' => 'thumb',
            ),
            2 => 
            array (
              '_route' => '/thumbs/{db:\\d+}/{id:\\d+}/{thumb}.jpg',
              '_name' => 'fetch-thumb',
            ),
          ),
          'ac' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-cover',
            ),
            1 => 
            array (
              'db' => 'db',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/covers/{db:\\d+}/{id:\\d+}.jpg',
              '_name' => 'fetch-cover',
            ),
          ),
          'ad' => 
          array (
            0 => 
            array (
              'view' => 1,
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-inline',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
              'ignore' => 'ignore',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/inline/{db:\\d+}/{data:\\d+}/{ignore}.{type}',
              '_name' => 'fetch-inline',
            ),
          ),
          'ae' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-data',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
              'ignore' => 'ignore',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/fetch/{db:\\d+}/{data:\\d+}/{ignore}.{type}',
              '_name' => 'fetch-data',
            ),
          ),
        ),
      ),
      1 => 
      array (
        'regex' => '~^(?|/view/([^/]+)/([^/]+)/([^/]+)\\.([^/]+)(*MARK:a)|/download/([^/]+)/([^/]+)/([^/]+)\\.([^/]+)(*MARK:b)|/read/(\\d+)/(\\d+)/([^/]+)(*MARK:c)|/read/(\\d+)/(\\d+)(*MARK:d)|/epubfs/(\\d+)/(\\d+)/(.+)(*MARK:e)|/restapi/databases/([^/]+)/([^/]+)(*MARK:f)|/restapi/databases/([^/]+)(*MARK:g)|/restapi/notes/([^/]+)/([^/]+)/([^/]+)(*MARK:h)|/restapi/notes/([^/]+)/([^/]+)(*MARK:i)|/restapi/notes/([^/]+)(*MARK:j)|/restapi/preferences/([^/]+)(*MARK:k)|/restapi/annotations/([^/]+)/([^/]+)(*MARK:l)|/restapi/annotations/([^/]+)(*MARK:m)|/restapi/metadata/([^/]+)/([^/]+)/([^/]+)(*MARK:n)|/restapi/metadata/([^/]+)/([^/]+)(*MARK:o)|/restapi/metadata/([^/]+)(*MARK:p)|/restapi/(.*)(*MARK:q)|/check/(.*)(*MARK:r)|/opds/(\\d+)/([^/]+)(*MARK:s)|/opds/(\\d+)(*MARK:t)|/opds/(.*)(*MARK:u)|/loader/([^/]+)/(\\d+)/(\\w+)/(.*)(*MARK:v)|/loader/([^/]+)/(\\d+)/(\\w*)(*MARK:w)|/loader/([^/]+)/(\\d+)(*MARK:x)|/loader/([^/]+)/(*MARK:y)|/loader/([^/]+)(*MARK:z)|/zipper/([^/]+)/([^/]+)/([^/]+)\\.zip(*MARK:aa)|/zipper/([^/]+)/([^/]+)\\.zip(*MARK:ab)|/calres/(\\d+)/([^/]+)/([^/]+)(*MARK:ac)|/zipfs/(\\d+)/(\\d+)/(.+)(*MARK:ad))$~',
        'routeMap' => 
        array (
          'a' => 
          array (
            0 => 
            array (
              'view' => 1,
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-view',
            ),
            1 => 
            array (
              'data' => 'data',
              'db' => 'db',
              'ignore' => 'ignore',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/view/{data}/{db}/{ignore}.{type}',
              '_name' => 'fetch-view',
            ),
          ),
          'b' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\FetchHandler',
              '_route' => 'fetch-download',
            ),
            1 => 
            array (
              'data' => 'data',
              'db' => 'db',
              'ignore' => 'ignore',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/download/{data}/{db}/{ignore}.{type}',
              '_name' => 'fetch-download',
            ),
          ),
          'c' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler',
              '_route' => 'read-title',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/read/{db:\\d+}/{data:\\d+}/{title}',
              '_name' => 'read-title',
            ),
          ),
          'd' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\ReadHandler',
              '_route' => 'read',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
            ),
            2 => 
            array (
              '_route' => '/read/{db:\\d+}/{data:\\d+}',
              '_name' => 'read',
            ),
          ),
          'e' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\EpubFsHandler',
              '_route' => 'epubfs',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
              'comp' => 'comp',
            ),
            2 => 
            array (
              '_route' => '/epubfs/{db:\\d+}/{data:\\d+}/{comp:.+}',
              '_name' => 'epubfs',
            ),
          ),
          'f' => 
          array (
            0 => 
            array (
              '_resource' => 'Database',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-database-table',
            ),
            1 => 
            array (
              'db' => 'db',
              'name' => 'name',
            ),
            2 => 
            array (
              '_route' => '/restapi/databases/{db}/{name}',
              '_name' => 'restapi-database-table',
            ),
          ),
          'g' => 
          array (
            0 => 
            array (
              '_resource' => 'Database',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-database',
            ),
            1 => 
            array (
              'db' => 'db',
            ),
            2 => 
            array (
              '_route' => '/restapi/databases/{db}',
              '_name' => 'restapi-database',
            ),
          ),
          'h' => 
          array (
            0 => 
            array (
              '_resource' => 'Note',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-note',
            ),
            1 => 
            array (
              'type' => 'type',
              'id' => 'id',
              'title' => 'title',
            ),
            2 => 
            array (
              '_route' => '/restapi/notes/{type}/{id}/{title}',
              '_name' => 'restapi-note',
            ),
          ),
          'i' => 
          array (
            0 => 
            array (
              '_resource' => 'Note',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-notes-type-id',
            ),
            1 => 
            array (
              'type' => 'type',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/restapi/notes/{type}/{id}',
              '_name' => 'restapi-notes-type-id',
            ),
          ),
          'j' => 
          array (
            0 => 
            array (
              '_resource' => 'Note',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-notes-type',
            ),
            1 => 
            array (
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/restapi/notes/{type}',
              '_name' => 'restapi-notes-type',
            ),
          ),
          'k' => 
          array (
            0 => 
            array (
              '_resource' => 'Preference',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-preference',
            ),
            1 => 
            array (
              'key' => 'key',
            ),
            2 => 
            array (
              '_route' => '/restapi/preferences/{key}',
              '_name' => 'restapi-preference',
            ),
          ),
          'l' => 
          array (
            0 => 
            array (
              '_resource' => 'Annotation',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-annotation',
            ),
            1 => 
            array (
              'bookId' => 'bookId',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/restapi/annotations/{bookId}/{id}',
              '_name' => 'restapi-annotation',
            ),
          ),
          'm' => 
          array (
            0 => 
            array (
              '_resource' => 'Annotation',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-annotations-book',
            ),
            1 => 
            array (
              'bookId' => 'bookId',
            ),
            2 => 
            array (
              '_route' => '/restapi/annotations/{bookId}',
              '_name' => 'restapi-annotations-book',
            ),
          ),
          'n' => 
          array (
            0 => 
            array (
              '_resource' => 'Metadata',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-metadata-element-name',
            ),
            1 => 
            array (
              'bookId' => 'bookId',
              'element' => 'element',
              'name' => 'name',
            ),
            2 => 
            array (
              '_route' => '/restapi/metadata/{bookId}/{element}/{name}',
              '_name' => 'restapi-metadata-element-name',
            ),
          ),
          'o' => 
          array (
            0 => 
            array (
              '_resource' => 'Metadata',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-metadata-element',
            ),
            1 => 
            array (
              'bookId' => 'bookId',
              'element' => 'element',
            ),
            2 => 
            array (
              '_route' => '/restapi/metadata/{bookId}/{element}',
              '_name' => 'restapi-metadata-element',
            ),
          ),
          'p' => 
          array (
            0 => 
            array (
              '_resource' => 'Metadata',
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-metadata',
            ),
            1 => 
            array (
              'bookId' => 'bookId',
            ),
            2 => 
            array (
              '_route' => '/restapi/metadata/{bookId}',
              '_name' => 'restapi-metadata',
            ),
          ),
          'q' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\RestApiHandler',
              '_route' => 'restapi-path',
            ),
            1 => 
            array (
              'path' => 'path',
            ),
            2 => 
            array (
              '_route' => '/restapi/{path:.*}',
              '_name' => 'restapi-path',
            ),
          ),
          'r' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\CheckHandler',
              '_route' => 'check-more',
            ),
            1 => 
            array (
              'more' => 'more',
            ),
            2 => 
            array (
              '_route' => '/check/{more:.*}',
              '_name' => 'check-more',
            ),
          ),
          's' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler',
              '_route' => 'opds-page-id',
            ),
            1 => 
            array (
              'page' => 'page',
              'id' => 'id',
            ),
            2 => 
            array (
              '_route' => '/opds/{page:\\d+}/{id}',
              '_name' => 'opds-page-id',
            ),
          ),
          't' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler',
              '_route' => 'opds-page',
            ),
            1 => 
            array (
              'page' => 'page',
            ),
            2 => 
            array (
              '_route' => '/opds/{page:\\d+}',
              '_name' => 'opds-page',
            ),
          ),
          'u' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\OpdsHandler',
              '_route' => 'opds-path',
            ),
            1 => 
            array (
              'path' => 'path',
            ),
            2 => 
            array (
              '_route' => '/opds/{path:.*}',
              '_name' => 'opds-path',
            ),
          ),
          'v' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
              '_route' => 'loader-action-dbNum-authorId-urlPath',
            ),
            1 => 
            array (
              'action' => 'action',
              'dbNum' => 'dbNum',
              'authorId' => 'authorId',
              'urlPath' => 'urlPath',
            ),
            2 => 
            array (
              '_route' => '/loader/{action}/{dbNum:\\d+}/{authorId:\\w+}/{urlPath:.*}',
              '_name' => 'loader-action-dbNum-authorId-urlPath',
            ),
          ),
          'w' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
              '_route' => 'loader-action-dbNum-authorId',
            ),
            1 => 
            array (
              'action' => 'action',
              'dbNum' => 'dbNum',
              'authorId' => 'authorId',
            ),
            2 => 
            array (
              '_route' => '/loader/{action}/{dbNum:\\d+}/{authorId:\\w*}',
              '_name' => 'loader-action-dbNum-authorId',
            ),
          ),
          'x' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
              '_route' => 'loader-action-dbNum',
            ),
            1 => 
            array (
              'action' => 'action',
              'dbNum' => 'dbNum',
            ),
            2 => 
            array (
              '_route' => '/loader/{action}/{dbNum:\\d+}',
              '_name' => 'loader-action-dbNum',
            ),
          ),
          'y' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
              '_route' => 'loader-action-',
            ),
            1 => 
            array (
              'action' => 'action',
            ),
            2 => 
            array (
              '_route' => '/loader/{action}/',
              '_name' => 'loader-action-',
            ),
          ),
          'z' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\LoaderHandler',
              '_route' => 'loader-action',
            ),
            1 => 
            array (
              'action' => 'action',
            ),
            2 => 
            array (
              '_route' => '/loader/{action}',
              '_name' => 'loader-action',
            ),
          ),
          'aa' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler',
              '_route' => 'zipper-page-id-type',
            ),
            1 => 
            array (
              'page' => 'page',
              'id' => 'id',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/zipper/{page}/{id}/{type}.zip',
              '_name' => 'zipper-page-id-type',
            ),
          ),
          'ab' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\ZipperHandler',
              '_route' => 'zipper-page-type',
            ),
            1 => 
            array (
              'page' => 'page',
              'type' => 'type',
            ),
            2 => 
            array (
              '_route' => '/zipper/{page}/{type}.zip',
              '_name' => 'zipper-page-type',
            ),
          ),
          'ac' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\CalResHandler',
              '_route' => 'calres',
            ),
            1 => 
            array (
              'db' => 'db',
              'alg' => 'alg',
              'digest' => 'digest',
            ),
            2 => 
            array (
              '_route' => '/calres/{db:\\d+}/{alg}/{digest}',
              '_name' => 'calres',
            ),
          ),
          'ad' => 
          array (
            0 => 
            array (
              '_handler' => 'SebLucas\\Cops\\Handlers\\ZipFsHandler',
              '_route' => 'zipfs',
            ),
            1 => 
            array (
              'db' => 'db',
              'data' => 'data',
              'comp' => 'comp',
            ),
            2 => 
            array (
              '_route' => '/zipfs/{db:\\d+}/{data:\\d+}/{comp:.+}',
              '_name' => 'zipfs',
            ),
          ),
        ),
      ),
    ),
  ),
  2 => 
  array (
    'page-index' => 
    array (
      0 => 
      array (
        0 => '/index',
      ),
    ),
    'page-authors-letter' => 
    array (
      0 => 
      array (
        0 => '/authors/letter/',
        1 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-1-letter' => 
    array (
      0 => 
      array (
        0 => '/authors/letter',
      ),
    ),
    'page-author' => 
    array (
      0 => 
      array (
        0 => '/authors/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-3-id' => 
    array (
      0 => 
      array (
        0 => '/authors/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-authors' => 
    array (
      0 => 
      array (
        0 => '/authors',
      ),
    ),
    'page-books-letter' => 
    array (
      0 => 
      array (
        0 => '/books/letter/',
        1 => 
        array (
          0 => 'id',
          1 => '\\w',
        ),
      ),
    ),
    'page-4-letter' => 
    array (
      0 => 
      array (
        0 => '/books/letter',
      ),
    ),
    'page-books-year' => 
    array (
      0 => 
      array (
        0 => '/books/year/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-4-year' => 
    array (
      0 => 
      array (
        0 => '/books/year',
      ),
    ),
    'page-book' => 
    array (
      0 => 
      array (
        0 => '/books/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'author',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-13-id' => 
    array (
      0 => 
      array (
        0 => '/books/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-books' => 
    array (
      0 => 
      array (
        0 => '/books',
      ),
    ),
    'page-serie' => 
    array (
      0 => 
      array (
        0 => '/series/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-7-id' => 
    array (
      0 => 
      array (
        0 => '/series/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-series' => 
    array (
      0 => 
      array (
        0 => '/series',
      ),
    ),
    'page-typeahead' => 
    array (
      0 => 
      array (
        0 => '/typeahead',
      ),
    ),
    'page-query-scope' => 
    array (
      0 => 
      array (
        0 => '/search/',
        1 => 
        array (
          0 => 'query',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'scope',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-query' => 
    array (
      0 => 
      array (
        0 => '/search/',
        1 => 
        array (
          0 => 'query',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-search' => 
    array (
      0 => 
      array (
        0 => '/search',
      ),
    ),
    'page-recent' => 
    array (
      0 => 
      array (
        0 => '/recent',
      ),
    ),
    'page-tag' => 
    array (
      0 => 
      array (
        0 => '/tags/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-12-id' => 
    array (
      0 => 
      array (
        0 => '/tags/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-tags' => 
    array (
      0 => 
      array (
        0 => '/tags',
      ),
    ),
    'page-custom' => 
    array (
      0 => 
      array (
        0 => '/custom/',
        1 => 
        array (
          0 => 'custom',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-customtype' => 
    array (
      0 => 
      array (
        0 => '/custom/',
        1 => 
        array (
          0 => 'custom',
          1 => '\\d+',
        ),
      ),
    ),
    'page-about' => 
    array (
      0 => 
      array (
        0 => '/about',
      ),
    ),
    'page-language' => 
    array (
      0 => 
      array (
        0 => '/languages/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-18-id' => 
    array (
      0 => 
      array (
        0 => '/languages/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-languages' => 
    array (
      0 => 
      array (
        0 => '/languages',
      ),
    ),
    'page-customize' => 
    array (
      0 => 
      array (
        0 => '/customize',
      ),
    ),
    'page-publisher' => 
    array (
      0 => 
      array (
        0 => '/publishers/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-21-id' => 
    array (
      0 => 
      array (
        0 => '/publishers/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-publishers' => 
    array (
      0 => 
      array (
        0 => '/publishers',
      ),
    ),
    'page-rating' => 
    array (
      0 => 
      array (
        0 => '/ratings/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-23-id' => 
    array (
      0 => 
      array (
        0 => '/ratings/',
        1 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
      ),
    ),
    'page-ratings' => 
    array (
      0 => 
      array (
        0 => '/ratings',
      ),
    ),
    'page-identifier' => 
    array (
      0 => 
      array (
        0 => '/identifiers/',
        1 => 
        array (
          0 => 'id',
          1 => '\\w+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'page-42-id' => 
    array (
      0 => 
      array (
        0 => '/identifiers/',
        1 => 
        array (
          0 => 'id',
          1 => '\\w+',
        ),
      ),
    ),
    'page-identifiers' => 
    array (
      0 => 
      array (
        0 => '/identifiers',
      ),
    ),
    'page-libraries' => 
    array (
      0 => 
      array (
        0 => '/libraries',
      ),
    ),
    'feed-page-id' => 
    array (
      0 => 
      array (
        0 => '/feed/',
        1 => 
        array (
          0 => 'page',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'feed-search' => 
    array (
      0 => 
      array (
        0 => '/feed/search',
      ),
    ),
    'feed-page' => 
    array (
      0 => 
      array (
        0 => '/feed/',
        1 => 
        array (
          0 => 'page',
          1 => '\\d+',
        ),
      ),
    ),
    'feed-path' => 
    array (
      0 => 
      array (
        0 => '/feed/',
        1 => 
        array (
          0 => 'path',
          1 => '.+',
        ),
      ),
    ),
    'feed' => 
    array (
      0 => 
      array (
        0 => '/feed',
      ),
    ),
    'fetch-file' => 
    array (
      0 => 
      array (
        0 => '/files/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'file',
          1 => '.+',
        ),
      ),
    ),
    'fetch-thumb' => 
    array (
      0 => 
      array (
        0 => '/thumbs/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'thumb',
          1 => '[^/]+',
        ),
        6 => '.jpg',
      ),
    ),
    'fetch-cover' => 
    array (
      0 => 
      array (
        0 => '/covers/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '\\d+',
        ),
        4 => '.jpg',
      ),
    ),
    'fetch-inline' => 
    array (
      0 => 
      array (
        0 => '/inline/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'ignore',
          1 => '[^/]+',
        ),
        6 => '.',
        7 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
      ),
    ),
    'fetch-data' => 
    array (
      0 => 
      array (
        0 => '/fetch/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'ignore',
          1 => '[^/]+',
        ),
        6 => '.',
        7 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
      ),
    ),
    'fetch-view' => 
    array (
      0 => 
      array (
        0 => '/view/',
        1 => 
        array (
          0 => 'data',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'db',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'ignore',
          1 => '[^/]+',
        ),
        6 => '.',
        7 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
      ),
    ),
    'fetch-download' => 
    array (
      0 => 
      array (
        0 => '/download/',
        1 => 
        array (
          0 => 'data',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'db',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'ignore',
          1 => '[^/]+',
        ),
        6 => '.',
        7 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
      ),
    ),
    'read-title' => 
    array (
      0 => 
      array (
        0 => '/read/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'read' => 
    array (
      0 => 
      array (
        0 => '/read/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
      ),
    ),
    'epubfs' => 
    array (
      0 => 
      array (
        0 => '/epubfs/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'comp',
          1 => '.+',
        ),
      ),
    ),
    'restapi-customtypes' => 
    array (
      0 => 
      array (
        0 => '/restapi/custom',
      ),
    ),
    'restapi-database-table' => 
    array (
      0 => 
      array (
        0 => '/restapi/databases/',
        1 => 
        array (
          0 => 'db',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'name',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-database' => 
    array (
      0 => 
      array (
        0 => '/restapi/databases/',
        1 => 
        array (
          0 => 'db',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-databases' => 
    array (
      0 => 
      array (
        0 => '/restapi/databases',
      ),
    ),
    'restapi-openapi' => 
    array (
      0 => 
      array (
        0 => '/restapi/openapi',
      ),
    ),
    'restapi-route' => 
    array (
      0 => 
      array (
        0 => '/restapi/routes',
      ),
    ),
    'restapi-handler' => 
    array (
      0 => 
      array (
        0 => '/restapi/handlers',
      ),
    ),
    'restapi-note' => 
    array (
      0 => 
      array (
        0 => '/restapi/notes/',
        1 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'title',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-notes-type-id' => 
    array (
      0 => 
      array (
        0 => '/restapi/notes/',
        1 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-notes-type' => 
    array (
      0 => 
      array (
        0 => '/restapi/notes/',
        1 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-notes' => 
    array (
      0 => 
      array (
        0 => '/restapi/notes',
      ),
    ),
    'restapi-preference' => 
    array (
      0 => 
      array (
        0 => '/restapi/preferences/',
        1 => 
        array (
          0 => 'key',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-preferences' => 
    array (
      0 => 
      array (
        0 => '/restapi/preferences',
      ),
    ),
    'restapi-annotation' => 
    array (
      0 => 
      array (
        0 => '/restapi/annotations/',
        1 => 
        array (
          0 => 'bookId',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-annotations-book' => 
    array (
      0 => 
      array (
        0 => '/restapi/annotations/',
        1 => 
        array (
          0 => 'bookId',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-annotations' => 
    array (
      0 => 
      array (
        0 => '/restapi/annotations',
      ),
    ),
    'restapi-metadata-element-name' => 
    array (
      0 => 
      array (
        0 => '/restapi/metadata/',
        1 => 
        array (
          0 => 'bookId',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'element',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'name',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-metadata-element' => 
    array (
      0 => 
      array (
        0 => '/restapi/metadata/',
        1 => 
        array (
          0 => 'bookId',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'element',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-metadata' => 
    array (
      0 => 
      array (
        0 => '/restapi/metadata/',
        1 => 
        array (
          0 => 'bookId',
          1 => '[^/]+',
        ),
      ),
    ),
    'restapi-user-details' => 
    array (
      0 => 
      array (
        0 => '/restapi/user/details',
      ),
    ),
    'restapi-user' => 
    array (
      0 => 
      array (
        0 => '/restapi/user',
      ),
    ),
    'restapi-path' => 
    array (
      0 => 
      array (
        0 => '/restapi/',
        1 => 
        array (
          0 => 'path',
          1 => '.*',
        ),
      ),
    ),
    'check-more' => 
    array (
      0 => 
      array (
        0 => '/check/',
        1 => 
        array (
          0 => 'more',
          1 => '.*',
        ),
      ),
    ),
    'check' => 
    array (
      0 => 
      array (
        0 => '/check',
      ),
    ),
    'opds-page-id' => 
    array (
      0 => 
      array (
        0 => '/opds/',
        1 => 
        array (
          0 => 'page',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
      ),
    ),
    'opds-search' => 
    array (
      0 => 
      array (
        0 => '/opds/search',
      ),
    ),
    'opds-page' => 
    array (
      0 => 
      array (
        0 => '/opds/',
        1 => 
        array (
          0 => 'page',
          1 => '\\d+',
        ),
      ),
    ),
    'opds-path' => 
    array (
      0 => 
      array (
        0 => '/opds/',
        1 => 
        array (
          0 => 'path',
          1 => '.*',
        ),
      ),
    ),
    'opds' => 
    array (
      0 => 
      array (
        0 => '/opds',
      ),
    ),
    'loader-action-dbNum-authorId-urlPath' => 
    array (
      0 => 
      array (
        0 => '/loader/',
        1 => 
        array (
          0 => 'action',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'dbNum',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'authorId',
          1 => '\\w+',
        ),
        6 => '/',
        7 => 
        array (
          0 => 'urlPath',
          1 => '.*',
        ),
      ),
    ),
    'loader-action-dbNum-authorId' => 
    array (
      0 => 
      array (
        0 => '/loader/',
        1 => 
        array (
          0 => 'action',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'dbNum',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'authorId',
          1 => '\\w*',
        ),
      ),
    ),
    'loader-action-dbNum' => 
    array (
      0 => 
      array (
        0 => '/loader/',
        1 => 
        array (
          0 => 'action',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'dbNum',
          1 => '\\d+',
        ),
      ),
    ),
    'loader-action-' => 
    array (
      0 => 
      array (
        0 => '/loader/',
        1 => 
        array (
          0 => 'action',
          1 => '[^/]+',
        ),
        2 => '/',
      ),
    ),
    'loader-action' => 
    array (
      0 => 
      array (
        0 => '/loader/',
        1 => 
        array (
          0 => 'action',
          1 => '[^/]+',
        ),
      ),
    ),
    'loader' => 
    array (
      0 => 
      array (
        0 => '/loader',
      ),
    ),
    'zipper-page-id-type' => 
    array (
      0 => 
      array (
        0 => '/zipper/',
        1 => 
        array (
          0 => 'page',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'id',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
        6 => '.zip',
      ),
    ),
    'zipper-page-type' => 
    array (
      0 => 
      array (
        0 => '/zipper/',
        1 => 
        array (
          0 => 'page',
          1 => '[^/]+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'type',
          1 => '[^/]+',
        ),
        4 => '.zip',
      ),
    ),
    'calres' => 
    array (
      0 => 
      array (
        0 => '/calres/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'alg',
          1 => '[^/]+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'digest',
          1 => '[^/]+',
        ),
      ),
    ),
    'zipfs' => 
    array (
      0 => 
      array (
        0 => '/zipfs/',
        1 => 
        array (
          0 => 'db',
          1 => '\\d+',
        ),
        2 => '/',
        3 => 
        array (
          0 => 'data',
          1 => '\\d+',
        ),
        4 => '/',
        5 => 
        array (
          0 => 'comp',
          1 => '.+',
        ),
      ),
    ),
    'mail' => 
    array (
      0 => 
      array (
        0 => '/mail',
      ),
    ),
    'graphql' => 
    array (
      0 => 
      array (
        0 => '/graphql',
      ),
    ),
    'tables' => 
    array (
      0 => 
      array (
        0 => '/tables',
      ),
    ),
    'test' => 
    array (
      0 => 
      array (
        0 => '/test',
      ),
    ),
  ),
);