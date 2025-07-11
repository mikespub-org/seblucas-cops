# Change Log for COPS (this fork)

For the original releases 0.0.1 to 1.1.3 see [CHANGELOG.seblucas](CHANGELOG.seblucas.md)
or directly at https://github.com/seblucas/cops/blob/master/CHANGELOG

x.x.x - TODO
  * Changes in config/default.php file:
    - new $config['cops_customize'] for default customize values per user (TODO)
  * Experiment with default customize values per user (TODO)
  * Upgrade npm-asset/bootstrap 3.4.1 to 5.3.5

2.8.x - 2024xxxx Maintenance release for 2.x (PHP >= 8.1)
  * ...

1.5.x - 2024xxxx Maintenance release for 1.x (PHP >= 7.4)
  * ...

3.x.x - 2025xxxx 
  * Fix Kindle style in default templates - see PR #156 from @dunxd
  * Increase cookie lifetime - see PR #155 from @dunxd
  * Set ETag and Last-Modified for files and images
  * Upgrade graphiql CDN template for react 19.x - see PR graphql/graphiql#3902
  * Update package.json dependencies (info only)
  * Upgrade mikespub/epub-loader to 3.6.3 and php-epub-meta to 3.5
  * Upgrade mikespub/php-epub-meta to 3.4 and adapt Calibre\Metadata class

3.6.5 - 20250513 Fix legacy browser, count by letter, update translations
  * Changes in config/default.php file:
    - new $config['cops_resources_cdn'] to use CDN for COPS resources
    - drop $config['cops_use_url_rewriting'] as deprecated since 3.1.0
    - mark $config['calibre_internal_directory'] as deprecated since 1.3.1
    - new $config['cops_enable_admin'] to enable admin features in COPS (WIP)
  * Add version to script, style and template links if missing
  * Experiment using CDN for COPS resources + allow caching for template files
  * Set ignore filename for book data links
  * Set session cookie path, httponly, samesite and secure id if needed
  * Set image height and width for OPDS 2.0 feeds (dev only)
  * Upgrade kiwilan/php-opds package to fix OPDS 2.0 search template with route urls (dev only)
  * Update translations in language files - see PR #150 from @horus68, PR #154 from @HaoSs07 etc.
  * Fix count by letter phrases in code - see issue #147 by @dunxd
  * Replace Theme by Style in customize page - see issue #146 by @dunxd
  * Support Javascript ES5 + legacy browser cookies - see issue #140 and PRs by @dunxd
  * Support PHP with session disabled - see issue #140 by @dunxd
  * Start admin handler to enable special server features (WIP)
  * Add session expires and regenerate session id if needed
  * Pass request context to graphql executor instead of request

3.6.1 - 20250409 Improve hierarchy, split by letter, download filename + fixes
  * Changes in config/default.php file:
    - new $config['cops_publisher_split_first_letter'] to split publisher by first letter
    - new $config['cops_series_split_first_letter'] to split series by first letter
    - new $config['cops_tag_split_first_letter'] to split tag by first letter
    - new $config['cops_session_name'] for session cookie name
    - new $config['cops_session_timeout'] for session timeout
    - new $config['cops_download_filename'] to customize download filename
  * Save to disk template for book filenames inside the .zip download file
  * Customize download filename (partial) - see issue #137 by @pigochu
  * Update epubjs-reader to version 2025.04.08 + use assets in template links
  * Fix number of formats and identifiers on index page - see issue #138 by @woidi
  * Split publisher, series or tag by first letter - see issue #139 by @prky
  * Support POST for customize and filter pages + add Session class (WIP)
  * Fix parent count for hierarchical series, tags and custom
  * Clean up handler and endpoint code for uri generation
  * Use short array syntax for arrays in config/default.php
  * Show parent trail for series when using hierarchy - see issue #134 by @HelenaGwyn
  * Use series and tags hierarchy in custom columns test database
  * Disable customize virtual library for multiple databases - see issue #133 by @HelenaGwyn

3.5.7 - 20250307 Fix format issues + switch routing library
  * Remove support for legacy config_local.php file + add warning message
  * Show formats or identifiers on homepage - see issue #134 by @HelenaGwyn
  * Support formats search in virtual library - see issue #132 by @HelenaGwyn
  * Start framework adapter for other frameworks (WIP)
  * Clean up GraphQL schema + update expected test results
  * Add HasContextTrait to BaseHandler and BaseMiddleware
  * Use symfony/routing instead of nikic/fast-route by default

3.5.4 - 20241223 Update translations, adapt templates for PageId + refactor
  * Move base url and slugify from Route to UriGenerator
  * Use RequestContext and instance methods in Framework for future adapters
  * Add HasRouteTrait to simplify handler route() calls and future migrations
  * Split UriGenerator from Route + add HandlerManager for framework adapters
  * Update portuguese translation - see PR #127 from @horus68
  * Rename group params + support idlist for books and expand GraphQL schema
  * Split off GraphQLExecutor class + rename RestApi to RestApiProvider
  * Expand Link models for OPDS 2.0 + use closure for links
  * BC: Switch PageId values to strings + adapt templates
  * Add PageQueryScope enum and PageFilter class (wip)
  * Group entries by filter group for twigged template - see 'filters.html'
  * Split off ProxyRequest class to handle trusted proxy headers for base url

3.5.1 - 20241211 Fix issues, add formats + replace transliterator
  * Use symfony/string package as alternative for normAndUp and slugify with Transliterator
  * Get request locale based on Translation and HTTP_ACCEPT_LANGUAGE
  * Clean up Route url methods and test generating route urls
  * Add Format class, pages and filters to select by book format
  * Fix custom column filters and handle csv with several values
  * Sort series custom column by extra field - see issue #124 by @Mossop
  * Remove deprecated config/default.php settings and clean up Zipper

3.4.6 - 20241208 Fix issues + update css link
  * Changes in config/default.php file:
    - new $config['calibre_database_field_image'] for 'image' field from epub-loader
  * Fix custom column detail page + not set entry - see issue #121 by @Mossop
  * Use minified bootstrap icons css - see PR #120 from @dunxd
  * Fix url-encode query for search in feeds - see issue #119 by @cebo29

3.4.5 - 20241105 Use route names to generate links
  * Changes in config/default.php file:
    - set $config['cops_use_route_urls'] as deprecated (= always enabled)
    - set $config['cops_download_series'] as deprecated (use $config['cops_download_page'] instead)
    - set $config['cops_download_author'] as deprecated (use $config['cops_download_page'] instead)
  * Upgrade swagger-ui-dist package and link to 5.18.0
  * Split off Routing namespace with interface to allow switching routers later
  * Pin nikic/fast-route version to 2.0.0-beta1 and use recommended settings
  * Pass _route param to request in Route::match() and deprecate Route::link()
  * Switch to handler::route() in most places for Calibre and Pages classes
  * Replace Route::link() with handler::page() and handler::link() and generate() with route()
  * Use handler class in params instead of handler name + adapt $handler properties/params
  * Generate openapi.json and dump/load cached routes file (info only)
  * Clean up fetch-thumb and zipper-page-* routes in handler & renderers
  * Change route definitions for handlers + add a few _route params
  * Find route for params via handlers with findRoute() method
  * Move pages to handlers in REST API + add getLink() and request() methods
  * BC: Add `_resource` param to REST API links and move under /restapi prefix
  * Clean up query string build to align with RFC3986 (%20) instead of RFC1738 (+)
  * Provide fallback for urls without intl extension - see issue #118 by @jillmess
  * Remove code for generating links without route urls
  * Remove tests for links generated without route urls

3.4.0 - 20241028 Update package dependencies + translations
  * Upgrade npm-asset/js-cookie 2.2.1 to 3.0.5
  * Upgrade datatables 1.13.11 to 2.1.8 (dev only)
  * Add path parameter validation in page handler routes
  * Add error handler for invalid requests + return not found
  * Attach notes database to sqlite connection on demand
  * Update integration of epub-loader and php-epub-meta
  * Update spanish translation - see PR #117 from @Dunhill69

3.3.1 - 20241020 Update translations + remove old endpoints
  * Update russian translation - see PR #116 from @Carmina16
  * Add TRANSLATIONS.md for Gitlocalize - see PR #115 from @horus68
  * Update loader config + add table handler (dev only)
  * Fix rewrite rules in web.config (IIS) - in theory at least
  * Remove deprecated COPS endpoints - see BC for COPS 3.x
  * Fix sortoptions warning for server-side doT templates

3.3.0 - 20241004 Fix issues, update languages, improvements & licensing
  * Changes in config/default.php file:
    - new $config['calibre_database_field_cover'] for 'cover' field from epub-loader
  * Filter out ignored formats in Book::getBookByDataId() - see issue #113 by @tomchiverton
  * Fix slugify for titles with slashes etc. in route urls
  * Get extra information for publisher, serie or tag + series for author without books
  * Clarify license GPL version 2 or later + fix file headers
  * Add dummy sqlite functions for table views in REST API
  * Update language files via Gitlocalize - see PRs from @horus68 + translators/moderators
  * Improve filters for Not Set tags, ratings, series or identifiers
  * Show search result text for no result - see issue #107 by @HaoSs07
  * Support query list arguments (limit, offset, where, order) in GraphQL (dev only)
  * Sort by book count in navigation lists if paginated for twigged and bootstrap2 template

3.2.2 - 20240914 Update bootstrap5 template + fix integrity
  * Fix integrity checks for bootstrap5 template - see PR #106 from @dunxd
  * Fix thumbnails and add series list to author page in bootstrap5 template - see PR #106 from @dunxd

3.2.0 - 20240914 Return response from handlers + replace GraphQL Playground (dev only)
  * Enable running other handlers in REST API and return response
  * Return response from most handlers to allow middleware to work on response
  * Add test middleware to check functionality (test only)
  * Replace graphql-playground with graphiql for security and future (dev only)

3.1.3 - 20240912 Fix mail link + show list of series in author details
  * Fix mail link producing an [object Object] message - see issue #105 by @marioscube
  * Show list of series in author details for bootstrap2 and twigged templates
  * Add extra params in links generated by getFilters() + use canFilter()
  * Simplify adding entries in pages + clean up constructors

3.1.2 - 20240909 Minor fix for docker release
  * Handle index.php/check to check configuration
  * Upgrade twig/twig package to 3.14.0

3.1.1 - 20240909 Release candidate for 3.x
  * Changes in config/default.php file:
    - new $config['cops_front_controller'] = '' value
  * Set front controller to remove index.php/ from route URLs

3.1.0 - 20240908 Breaking changes for 3.x
  * Changes in config/default.php file:
    - set $config['cops_use_route_urls'] = '1' as default
    - new $config['cops_twig_templates'] = ['twigged'] list
  * Split off templates from HtmlRenderer
  * BC: Redirect other endpoints + add deprecations
  * Split off RouteTest + fix route urls
  * Add basic middleware dispatcher
  * BC: Enable route urls by default in config/default.php
  * BC: Move config_*.php files to config/*.php to align dir structure
  * BC: Rename lib/ to src/ and test/ to tests/ to align dir structure

3.0.0 - 20240905 Update requirements (PHP >= 8.2)
  * Update phpunit tests + upgrade code with rector

2.8.2 - 20240905 More clean-up of handlers & renderers
  * Expand Data mimetypes to cover common EPUB file components
  * Add Response class + move notFound() + add redirect() and sendError()
  * Rename FileRenderer to FileResponse and inherit from Response
  * Replace header() and echo with Response() in handlers
  * Use FileResponse::sendFile() for covers and thumbnails
  * Fix monocle epub reader when using route urls
  * Move getting zip content from ZipFsHandler to EPubReader
  * Add GraphQL query field tests
  * Show filters in main page for bootstrap2 and twigged templates
  * Show links to PAGE_ALL in filters page to find all authors etc. for a filter
  * Pass along Response() from handler to renderer + align constructor args

2.8.1 - 20240902 Fix download filenames
  * Fix FileRenderer to send the right Content-Disposition - see issue #102 by @Chirishman

2.8.0 - 20240901 Support 'kepubify' tool for Kobo
  * Add FileRenderer class to send files + use sendHeaders
  * Fix Zipper to allow unicode chars in file names
  * Refactor FetchHandler and getUpdatedEpub to support kepubify
  * Use optional kepubify tool to convert EPUB files for Kobo - see #77 by @SenorSmartyPants

2.7.5 - 20240831 Show extra data files in book detail + start GraphQL
  * Changes in config_default.php file:
    - new $config['cops_kepubify_path']
  * Start experimental GraphQL interface (dev only)
  * Upgrade twig/twig package and add webonyx/graphql-php package (dev only)
  * Show extra data files in book detail - see feature #97 by @russell-phillips
  * Add tests for new handler classes + clean-up tests
  * Remove deprecated methods for releases older than 2.7.4

2.7.4 - 20240828 Replace Transliteration + prepare using kepubify
  * Support splitting books or authors by non-ascii first letter
  * Add kepubify tool to linuxserver docker image - see issue #77 by @SenorSmartyPants and linuxserver/docker-cops#56
  * Drop old Transliteration class and use PHP Transliterator for normalized search option

2.7.3 - 20240823 Update language files + add fixes
  * Upgrade magnific-popup package to 1.2.0
  * Upgrade swagger-ui-dist package and link to 5.17.14
  * Update language files via Gitlocalize - see PRs from @horus68 and his intrepid band of translators ;-)
  * Fix transparent search suggestions box - see pull request #96 from @dunxd for issue #95 by @marioscube
  * Catch potential null custom columns for multi-database setup - see issue #89 by @Chirishman
  * Use link handler for database entries with multi-database setup - see issue #85 by @erdoking and @shaoyangx
  * Upgrade kiwilan, mikespub, symfony and twig composer packages

2.7.1 - 20240526 Use external storage + settings for epubjs reader
  * Changes in config_default.php file:
    - new $config['calibre_external_storage']
  * Support external storage for Calibre library - see seblucas/cops#506 and seblucas/cops#513
  * Pass along request handler in baselist, booklist and virtual libraries
  * Adjust default settings for epubjs-reader - see pull request #81 from @dunxd
  * Rename IndexHandler to HtmlHandler and use default 'index' in request
  * Rename download.php etc. to zipper* to avoid conflict with url rewrite

2.7.0 - 20240512 Use handlers instead of endpoints
  * Start front-end controller and router script (WIP)
  * Use handlers instead of endpoints for route links
  * Fix path_info for handlers when using route urls
  * Add minimal framework + move endpoint code to handlers
  * Change restapi routes to use endpoint instead of dummy pageId
  * Add more endpoints to routes and return instead of exit
  * Add getUri() for annotations and notes

2.6.1 - 20240507 Reverse proxies, url rewriting with docker + clean-up
  * Changes in config_default.php file:
    - new $config['cops_trusted_proxies'] (dev only)
    - new $config['cops_trusted_headers'] (dev only)
  * Upgrade swagger-ui-dist package and link to 5.17.6
  * Fix rewriting rules in nginx default site conf - see #79 and linuxserver/docker-cops#31
  * Support X-Forwarded-* and Forwarded headers from trusted proxies (dev only)
  * Add Wiki page to clarify [Reverse proxy configurations](https://github.com/mikespub-org/seblucas-cops/wiki/Reverse-proxy-configurations)
  * Rename JSON_renderer and OPDS_renderer files and classes
  * Add HtmlRenderer class and move html template rendering from index.php
  * Use dcterms:modified instead of mtime as link attribute in OPDS feeds

2.5.6 - 20240503 Support TXT files in OPDS feeds + add length and mtime
  * Add length + mtime to OPDS acquisition links - perhaps for #79
  * Fix Opds connection under docker deployment cannot display books in TXT files - see #79 by @shaoyangx

2.5.5 - 20240423 Update epubjs-reader
  * Update epubjs-reader version + template

2.5.4 - 20240409 Add settings for epubjs-reader
  * Changes in config_default.php file:
    - new $config['cops_epubjs_reader_settings']
  * Configurable epubjs-reader settings - see issue mikespub-org/intity-epubjs-reader#2 by @intity

2.5.3 - 20240404 Expand rest api + update epubjs reader
  * Upgrade mikespub/epubjs-reader from @intity theme - see issue #76
  * Upgrade mikespub/epub-loader to 3.0 for wikidata (dev only)
  * Upgrade swagger-ui-dist package and link to 5.12.0
  * Get annotations from database or metadata.opf file
  * Add Annotation and Metadata classes
  * Add annotations in test data files
  * Add cover and thumbnail route urls
  * Match routes with endpoints in rest api
  * Get user details in rest api

2.5.1 - 20240307 User accounts database + route to endpoints
  * Changes in config_default.php file:
    - new $config['cops_http_auth_user']
    - new $config['calibre_user_database']
    - add $config['cops_basic_authentication'] option
  * Upgrade mikespub/epub-loader to 2.5 to use route urls (dev only)
  * Start use of Calibre user accounts database (TODO)
  * Add support for authentication via reverse proxy

2.5.0 - 20240306 Use virtual libraries + support epubjs reader
  * Changes in config_default.php file:
    - new $config['cops_virtual_library']
    - new $config['cops_epub_reader']
  * Select virtual library via customize page or config_local
  * Propose epubjs-reader as alternative for monocle
  * Clarify WebDriver tests with selenium container (dev only)
  * Split off index page and filter by virtual library

2.4.3 - 20240302 Start virtual libraries + switch to phpunit 10.5
  * Changes in config_default.php file:
    - new $config['cops_calibre_virtual_libraries']
  * Update dependencies + switch to phpunit 10.5
  * Add identifier filter links
  * Start support for virtual libraries from Calibre (TODO)

2.4.2 - 20240227 Show category notes for Calibre 7.x (bootstrap2 & twigged)
  * Show use of db parameter in openapi for REST API
  * Add notes and preferences routes in REST API
  * Add Preference class for Calibre preferences
  * Show notes in page detail for bootstrap2 & twigged templates
  * Get notes for author, publisher, serie and tag if available

2.4.1 - 20240226 Support cops_full_url in REST API swagger ui
  * Fix restapi.php when cops_full_url is needed - see issue #74 from @bcleonard

2.4.0 - 20240225 Add rating and instance link if available
  * Changes in config_default.php file:
    - new $config['cops_download_template']
  * Add instance link for extra information on author, publisher, serie and tag
  * Save to disk template for book filenames inside the .zip download file (TODO)
  * Upgrade mikespub/epub-loader to 2.4 to get rid of superglobals (dev only)
  * Add missing rating to bookdetail templates

2.3.1 - 20240220 Fix cover popup for default template
  * Fix no large book covers and white screen with viewer - see issue #73 from @marioscube

2.3.0 - 20240218 Update OPDS 2.0 and EPub Loader (dev only)
  * Upgrade kiwilan/php-opds to 2.0 to fix OPDS 2.0 pagination
  * Upgrade mikespub/epub-loader to 2.3 to include OpenLibrary lookup

2.2.2 - 20240215 Fix multi-database for epub reader and email
  * Error sending or reading book from additional dbs - see issue #72 from @malkavi

2.2.1 - 20231116 Consolidate PRs for next release (PHP >= 8.1)
  * Support display settings for custom columns - see pull request #69 from @Mikescher
  * Add Japanese language file - see pull request #67 from @horus68 translated by Rentaro Yoshidumi
  * Use server side rendering for Kobo - see pull request #62 from @dunxd
  * Add bootstrap2 Kindle theme - see pull request #61 from @dunxd
  * Improve Kindle style - see pull request #60 from @dunxd
  * Fix default values in util.js for Kindle - see pull request #58 from @dunxd

2.2.0 - 20230925 Update dependencies (PHP >= 8.1)
  * Upgrade mikespub/epub-loader to 2.2 (dev only)
  * Upgrade mikespub/php-epub-meta to 2.2

2.1.5 - 20230925 Tweaks and fixes on previous release (PHP >= 8.1)
  * Fix download by page with route urls, customize link in default footer, header links in bootstrap5
  * Add first & last paging in bootstrap2 & twigged templates
  * Refresh page on style change - see pull request #55 from @dunxd
  * Fix style css not being prefixed with Route::base() - see pull request #54 from @Mikescher

2.1.4 - 20230924 Translations, Bootstrap5, Route URLs and REST API (PHP >= 8.1)
  * Changes in config_default.php file:
    - new $config['cops_use_route_urls']
    - new $config['cops_api_key']
  * Translations update sept 2023 - see pull request #52 from @horus68
  * Improve submenu and filters in bootstrap5 template - see pull requests from @dunxd
  * Fix distinct count for identifiers
  * Add swagger-ui interface and api key config for REST API tests
  * Add json schema validation for OPDS 2.0 tests - ok with 1.0.30 except pagination
  * Use nikic/fast-route to match route urls (if enabled)
  * Use route urls in code and absolute paths in templates

2.1.3 - 20230919 Try route urls + improve sort in bootstrap5 (PHP >= 8.1)
  * Use nikic/fast-route to match route urls (dev only)
  * Start route urls in code and absolute paths in templates
  * Improve sorting in bootstrap5 template - see pull requests from @dunxd

2.1.2 - 20230917 Fix TOC children + improve bootstrap5 template (PHP >= 8.1)
  * Fix sort asc/desc for author and rating - see issue #44
  * Show TOC with children in epub reader with mikespub/php-epub-meta 2.1+
  * Improve bootstrap5 template some more - see pull requests from @dunxd

2.1.1 - 20230914 Download books per page/series/author, fix search form + add epub-loader (PHP >= 8.1)
  * Changes in config_default.php file:
    - new $config['cops_download_page']
    - new $config['cops_download_series']
    - new $config['cops_download_author']
  * Use kiwilan/php-opds to generate OPDS 2.0 catalog with opds.php (besides OPDS 1.2 with feed.php) (dev only)
  * Add download.php to allow downloading all books of a series or author, or all books on a page
  * Fix search form with server-side rendering in bootstrap* templates - see pull request #38 from @dunxd
  * Add loader.php for integration of epub-loader (development mode only)

2.0.1 - 20230910 Initial release for PHP >= 8.1 with new EPub update package
  * More spacing tweaks on the bootstrap5 template - see pull request #35 from @dunxd
  * Use maennchen/zipstream-php to update epub files on the fly (PHP 8.x)

1.5.4 - 20230910 Split off resources in preparation of 2.x
  * Changes in config_default.php file:
    - new $config['cops_assets']
  * Use it.assets variable in doT templates to refer to 'vendor/npm-asset'
  * Use asset() function in Twig templates to get versioned asset URLs
  * Split off epub-loader, php-epub-meta and tbszip resources again
  * Align resources folders to src and app in code

1.5.0 - 20230909 New baseline for next releases
  * Support class inheritance for most COPS lib and resource classes in code
  * Minor updates for templates - pass ignored categories #30 + set document title #31
  * Add resources/epub-loader actions for books, series and wikidata
  * Update bootstrap5 template - see pull request #29 from @dunxd - feedback still appreciated
  * Add support for .m4b files in COPS - see issue #28 from @Chirishman
  * Add twigged template using Twig template engine as alternative for doT

1.4.5 - 20230905 Make sort links optional in OPDS feeds for old e-readers
  * Changes in config_default.php file:
    - new $config['cops_opds_sort_links']
    - new $config['cops_html_sort_links']
  * Make sort links optional in HTML page detail and OPDS catalog - see #27

1.4.4 - 20230904 Revert OPDS feed changes for old e-readers
  * Switch section to subsection in OPDS link rel for koreader and Kybook3 - see #26 and #27
  * Add class label for #24 + authors & tags for #25 in JSON renderer
  * Prepare move from clsTbsZip to ZipEdit when updating EPUB in code

1.4.3 - 20230831 Sort & Filter in OPDS Catalog + Add bootstrap v5 template
  * Changes in config_default.php file:
    - new $config['cops_thumbnail_default']
    - new $config['cops_opds_filter_limit']
    - new $config['cops_opds_filter_links']
    - new $config['cops_html_filter_limit']
    - new $config['cops_html_filter_links']
    - drop $config['cops_show_filter_links']
  * Add bootstrap5 template for modern devices - see pull request #22 from @dunxd - feedback appreciated
  * Add optional Identifier pages in code
  * Fix updating author & date in epub-loader
  * Start WebDriver and BrowserKit test classes for functional testing
  * Split off new Calibre\Cover class + move various thumbnail code there
  * Add default thumbnail and link numbers for OPDS catalog if e-reader uses them
  * Add first & last links + sorting & filtering options for OPDS catalog (if e-reader supports facets)
  * Keep track of changes in ZipFile + fix setCoverInfo() in EPub in code
  * Split off new Pages\PageId class + move PAGE_ID constants there in code
  * Mark combined getsetters for EPub() as deprecated for 1.5.0 in php-epub-meta
  * Add updated php-epub-meta methods and classes to version in resources - see https://github.com/epubli/epub
  * Fix code base to work with phpstan level 6

1.4.2 - 20230814 Fix OPDS renderer + add sorting & filtering options to bootstrap2
  * Changes in config_default.php file:
    - new $config['calibre_categories_using_hierarchy']
    - set $config['cops_template'] = 'bootstrap2' again
    - new $config['cops_custom_integer_split_range']
    - new $config['cops_custom_date_split_year']
    - new $config['cops_titles_split_publication_year'] (if not $config['cops_titles_split_first_letter'])
  * Add optional hierarchical tags and custom columns in bootstrap2 template
  * Split off new Calibre\Category class + support hierarchical tags and custom columns in code
  * Remove global $config everywhere and replace with Config::get() except in config.php
  * Switch back to bootstrap2 as standard template in config_default.php
  * Move endpoint dependency from LinkNavigation to JSON/OPDS renderer
  * Update checkconfig.php to better reflect current requirements
  * Downgrade level set to PHP 7.4 with rector to fix a few compatibility issues
  * Rebase Calibre\Book and Calibre\CustomColumnType classes
  * Split off new Calibre\BaseList class and move SQL statements
  * Add sorting of non-book lists with URL param in code
  * Add optional filter links in bootstrap2 template
  * Add filtering of non-book lists in pages
  * Add sort options for book lists in bootstrap2 template
  * Add pagination for custom columns + split by range for Integer
  * Add pagination for non-book lists if not already split
  * Add option to split custom columns of type Date by year
  * Fix OPDS renderer for HTML content - see pull request seblucas/cops#488 from @cbckly
  * Add other .title translations to i18n array for use in templates - see pull request #11 from @dunxd
  * Add sorting of booklist entries with URL param in code
  * Add option to split titles by publication year
  * Add Librera reader to detected OPDS compatible readers - see pull request #10 from @dunxd

1.4.1 - 20230728 Clean-up before next release
  * Changes in config_default.php file:
    - new $config['cops_home_page'] for @dunxd
    - new $config['cops_show_not_set_filter']
  * Add parent url and customize link in templates
  * Allow filtering non-book queries on other params in code ('a', 'l', 'p', 'r', 's', 't', 'c') = e.g. get Series for AuthorId
  * Allow filtering booklist queries on other params in code ('a', 'l', 'p', 'r', 's', 't', 'c') = get books for a combination
  * Expand OpenAPI document for REST API
  * Fix cookie javascript code for server-side rendering
  * Fix tag filter, multi-database navigation and feed link
  * Split off new Calibre\Database, Calibre\BookList, Calibre\Filter, Input\Config and Output\Mail classes

1.4.0 - 20230721 Use namespaces in PHP lib, upgrade jquery npm asset + sausage package
  * Split off new Input\Request, Language\Translation, Output\Format and Output\EPubReader classes
  * Pass database and/or request param in static method calls to remove dependency on global $_GET
  * Update OPDSValidator, jing and tests for OPDS 1.2 (last updated in 2018)
  * Add namespace hierarchy, move page constants + make Calibre classes a bit more generic
  * Switch from npm-asset/typeahead.js 0.11.0 to npm-asset/corejs-typeahead 1.3.3
  * Upgrade sauce/sausage 0.18.0 to dev-php8x = PHP 8 compatible fork from https://github.com/IMrahulpai/sausage
  * Upgrade npm-asset/jquery 1.12.4 to 3.7.0
  * Use PHP namespace in lib: SebLucas\Cops

1.3.6 - 20230714 Add REST API, limit email address, clean up constants + fix book test
  * Add REST API endpoint (basic)
  * Limit sending to a single email address - see pull request #7 from @dunxd
  * Clean up global base constants
  * Fix kindle book text

1.3.5 - 20230712 Send EPUB, fix custom columns, support wildcard + add tests
  * Changes in config_default.php file:
    - set $config['cops_template'] = 'default' for issues with Kindle Paperwhite
    - set $config['default_timezone'] = 'UTC'
    - new wildcard option for $config['cops_calibre_custom_column'] =  ["*"];
    - new wildcard option for $config['cops_calibre_custom_column_list'] =  ["*"];
    - new wildcard option for $config['cops_calibre_custom_column_preview'] =  ["*"];
  * Replace offering to email MOBI with EPUB - see pull request #6 from @dunxd
  * Use wildcard to get all custom columns : ["*"]
  * Update tests for custom columns
  * Fix multiple values of custom columns for csv text
  * Fix display value of custom columns for series
  * Revert octal number notation in tbszip for PHP 8.0
  * Add tests for JSON renderer
  * Add tests for book methods called by epub reader

1.3.4 - 20230609 Fix EPUB 3 TOC, replace other npm assets and use namespace in PHP resources
  * Fix TOC for EPUB 3 files in resources/php-epub-meta for epubreader
  * Switch from dimsemenov/magnific-popup 1.1.0 to npm-asset/magnific-popup 1.1.0 (last updated in 2016)
  * Switch from twitter/typeahead.js 0.11.1 to npm-asset/typeahead.js 0.11.1 (last updated in 2015)
  * Switch from twbs/bootstrap 3.4.1 to npm-asset/bootstrap 3.4.1
  * Use PHP namespace in resources/dot-php: SebLucas\Template
  * Use PHP namespace in resources/epub-loader: Marsender\EPubLoader
  * Use PHP namespace in resources/php-epub-meta: SebLucas\EPubMeta
  * Use PHP namespace in resources/tbszip: SebLucas\TbsZip

1.3.3 - 20230327 Update npm asset dependencies
  * Fix link to typeahead.css for bootstrap2 templates
  * Move simonpioli/sortelements dev-master to resources (last updated in 2012)
  * Switch from bower-asset/dot 1.1.3 to npm-asset/dot 1.1.3
  * Switch from bower-asset/jquery 1.12.4 to npm-asset/jquery 1.12.4
  * Switch from bower-asset/jquery-cookie 1.4.1 to npm-asset/js-cookie 2.2.1
  * Switch from bower-asset/normalize.css 7.0.0 to npm-asset/normalize.css 8.0.1
  * Switch from rsms/js-lru dev-v2 to npm-asset/lru-fast 0.2.2

1.3.2 - 20230325 Improve tests and security
  * Merge branch 'master' of https://github.com/peltos/cops - see @peltos

1.3.1 - 20230325 Update epub-loader resources
  * Merge commit 'refs/pull/424/head' of https://github.com/seblucas/cops - see seblucas/cops#424 from @marsender

1.3.0 - 20230324 Add bootstrap2 templates
  * Merge branch 'master' of https://github.com/SenorSmartyPants/cops - see seblucas/cops#497 and earlier from @SenorSmartyPants

1.2.3 - 20230324 Add fixes for PHP 8.2

1.2.2 - 20230324 Update fetch.php to lower memory consumption
  * Merge commit 'refs/pull/518/head' of https://github.com/seblucas/cops - see seblucas/cops#518 from @allandanton

1.2.1 - 20230321 Add phpstan baseline + fixes

1.2.0 - 20230319 Migration to PHP 8.x

1.1.3 - 20190624
to
0.0.1 - 20120302

Moved to [CHANGELOG.seblucas](CHANGELOG.seblucas.md)
