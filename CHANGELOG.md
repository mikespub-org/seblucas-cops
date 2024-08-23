# Change Log for COPS (this fork)

For the original releases 0.0.1 to 1.1.3 see [CHANGELOG.seblucas](CHANGELOG.seblucas.md)
or directly at https://github.com/seblucas/cops/blob/master/CHANGELOG

x.x.x - TODO
  * Upgrade npm-asset/bootstrap 3.4.1 to 5.3.3
  * Upgrade npm-asset/js-cookie 2.2.1 to 3.0.5

1.5.x - 2024xxxx Maintenance release for 1.x (PHP >= 7.4)
  * ...

2.7.x - 2024xxxx
  * ...

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
