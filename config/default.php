<?php

/**
 * COPS (Calibre OPDS PHP Server) default configuration
 * Settings can be overridden in config/local.php and
 * optional config/local.{remote_user}.php
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

if (!isset($config)) {
    $config = [];
}

/*
 * The directory containing calibre's metadata.db file, with sub-directories
 * containing all the formats.
 * BEWARE : it has to end with a /
 * You can enable multiple database with this notation instead of a simple string :
 * $config['calibre_directory'] = [
 *     "My database name" => "/home/directory/calibre1/",
 *     "My other database name" => "/home/directory/calibre2/",
 * ];
 */
$config['calibre_directory'] = './';

/*
 * SPECIFIC TO NGINX
 * The internal directory set in nginx config file
 * Leave empty if you don't know what you're doing
 * @deprecated 1.3.1 use absolute paths in calibre_directory
 */
$config['calibre_internal_directory'] = '';

/**
 * Custom configuration if your Calibre library is stored elsewhere and
 * you cannot sync it or mount a remote volume to it on your COPS server.
 * For example a static website, AWS S3 bucket, Nextcloud site, Google Drive, ...
 * You will still need to copy the metadata.db database file to the calibre
 * directory above for this to work though...
 * Note: not all features will be available in this configuration, e.g.
 * no kepub version or metadata updates, and no monocle epub reader
 * Example (test):
 * $config['calibre_external_storage'] = 'http://localhost/cops/tests/BaseWithSomeBooks/';
 */
$config['calibre_external_storage'] = '';

/**
 * Custom configuration if your Calibre library was created with epub-loader,
 * and it contains an extra 'cover' field in the books table for special covers
 * Example (with epub-loader):
 * $config['calibre_database_field_cover'] = 'cover';
 */
$config['calibre_database_field_cover'] = '';

/**
 * Custom configuration if your Calibre library was created with epub-loader,
 * and it contains an extra 'image' field in the authors and/or series tables
 * Example (with epub-loader):
 * $config['calibre_database_field_image'] = 'image';
 */
$config['calibre_database_field_image'] = '';

/*
 * Full URL prefix (with trailing /)
 * useful where a full URL is required, COPS is behind a proxy or the script name shows the wrong place
 * Older e-readers like Mantano, Aldiko and Marvin may require it for Opensearch too.
 * e.g.
 * $config['cops_full_url'] = '/cops/';
 * $config['cops_full_url'] = 'http://localhost/cops/';
 * $config['cops_full_url'] = 'http://192.168.1.xxx/cops/';
 * $config['cops_full_url'] = 'https://www.domainname.com/cops/';
 */
$config['cops_full_url'] = '';

/*
 * As an alternative for cops_full_url above, if you're using a reverse proxy and you want to
 * change how COPS is accessed depending on the entrypoint (e.g. direct, local network, internet)
 * you can define trusted proxies and trusted headers here (dev only)
 * Note: using symfony/http-foundation to support X-Forwarded-* and Forwarded headers from proxies
 * @see https://symfony.com/doc/current/deployment/proxies.html
 */
$config['cops_trusted_proxies'] = '';
$config['cops_trusted_headers'] = [];

/*
 * Use CDN for resources like images, files etc.
 * This will replace the base url or full url above
 * @see https://github.com/mikespub-org/seblucas-cops/wiki/Reverse-proxy-configurations#using-cdn-for-cops-resources
 */
$config['cops_resources_cdn'] = '';

/*
 * Number of recent books to show
 */
$config['cops_recentbooks_limit'] = '50';

/*
 * Catalog's author name
 */
$config['cops_author_name'] = 'Sébastien Lucas';

/*
 * Catalog's author uri
 */
$config['cops_author_uri'] = 'https://blog.slucas.fr';

/*
 * Catalog's author email
 */
$config['cops_author_email'] = 'sebastien@slucas.fr';

/*
 * Catalog's title
 */
$config['cops_title_default'] = 'COPS';

/*
 * Catalog's subtitle
 */
$config['cops_subtitle_default'] = '';

/*
 * Wich header to use when downloading books outside the web directory
 * Possible values are :
 *   X-Accel-Redirect   : For Nginx
 *   X-Sendfile         : For Lightttpd or Apache (with mod_xsendfile)
 *   No value (default) : Let PHP handle the download
 */
$config['cops_x_accel_redirect'] = '';

/*
 * Height of thumbnail image for OPDS (thumb=opds)
 * Note: book detail uses image height x 2 (thumb=opds2)
 */
$config['cops_opds_thumbnail_height'] = '164';

/*
 * Height of thumbnail image for HTML (thumb=html)
 * Note: book detail uses image height x 2 (thumb=html2)
 */
$config['cops_html_thumbnail_height'] = '225';

/*
 * Icon for both OPDS and HTML catalog
 * Note that this has to be a real icon (.ico)
 */
$config['cops_icon'] = 'favicon.ico';

/*
 * Show icon for authors, series, tags and books on OPDS feed
 *  1 : enable
 *  0 : disable
 */
$config['cops_show_icons'] = '1';

/*
 * Default timezone
 * Check following link for other timezones :
 * https://www.php.net/manual/en/timezones.php
 */
$config['default_timezone'] = 'UTC';

/*
 * Prefered format for HTML catalog
 * The two first will be displayed in book entries
 * The other only appear in book detail
 */
$config['cops_prefered_format'] = ['EPUB', 'PDF', 'AZW3', 'AZW', 'MOBI', 'CBR', 'CBZ'];

/*
 * Specify the ignored formats that will never display in COPS
 * This will also stop downloading them, unless the files are under the web directory
 */
$config['cops_ignored_formats'] = [];

/*
 * generate a invalid OPDS stream to allow bad OPDS client to use search
 * Example of non compliant OPDS client : Moon+ Reader
 * Example of good OPDS client : Mantano, FBReader
 *  1 : enable support for non compliant OPDS client
 *  0 : always generate valid OPDS code
 */
$config['cops_generate_invalid_opds_stream'] = '0';

/*
 * Max number of items per page
 * -1 unlimited
 */
$config['cops_max_item_per_page'] = '48';

/*
 * split authors by first letter
 * 1 : Yes
 * 0 : No
 */
$config['cops_author_split_first_letter'] = '1';

/*
 * split titles by first letter
 * 1 : Yes
 * 0 : No
 */
$config['cops_titles_split_first_letter'] = '1';

/*
 * split titles by publication year (if not split by first letter)
 * 1 : Yes
 * 0 : No
 */
$config['cops_titles_split_publication_year'] = '1';

/*
 * split publishers by first letter
 * 1 : Yes
 * 0 : No
 */
$config['cops_publisher_split_first_letter'] = '0';

/*
 * split series by first letter
 * 1 : Yes
 * 0 : No
 */
$config['cops_series_split_first_letter'] = '0';

/*
 * split tags by first letter
 * 1 : Yes
 * 0 : No
 */
$config['cops_tag_split_first_letter'] = '0';

/*
 * Enable the Lightboxes (for popups) in 'default' template with client side rendering
 * 1 : Yes (enable)
 * 0 : No
 */
$config['cops_use_fancyapps'] = '1';

/*
 * Update Epub metadata before download
 * 1 : Yes (enable)
 * 0 : No
 */
$config['cops_update_epub-metadata'] = '0';

/*
 * Filter on tags to book list
 * Only works with the OPDS catalog
 * Usage : [
 *     "I only want to see books using the tag : Tag1"     => "Tag1",
 *     "I only want to see books not using the tag : Tag1" => "!Tag1",
 *     "I want to see every books"                         => "",
 * ];
 * Example : ["All" => "", "Unread" => "!Read", "Read" => "Read"];
 */
$config['cops_books_filter'] = [];

/*
 * Virtual libraries
 * to add as an array containing the library names configured in Calibre
 * This is not supported in combination with multiple databases
 *
 * For example : ["Short Stories in English", "Fiction from this Century"];
 *
 * To select all Calibre virtual libraries you can use wildcard :
 * $config['cops_calibre_virtual_libraries'] = ["*"];
 *
 * @todo https://manual.calibre-ebook.com/virtual_libraries.html
 * Note that search criteria for virtual libraries are very limited in COPS (TODO)
 */
$config['cops_calibre_virtual_libraries'] = [];

/*
 * Default virtual library to use (and filter on) in COPS
 * based on the Calibre list of virtual libraries (starting with 1)
 * This is not supported in combination with multiple databases
 */
$config['cops_virtual_library'] = '0';

/*
 * Custom Columns for the index page
 * to add as an array containing the lookup names configured in Calibre
 *
 * For example : ["genre", "mycolumn"];
 *
 * To select all Calibre custom columns you can use wildcard : ["*"];
 *
 * Note that the composite custom columns are not supported
 */
$config['cops_calibre_custom_column'] = [];

/*
 * Custom Columns for the list representation
 * to add as an array containing the lookup names configured in Calibre
 *
 * For example : ["genre", "mycolumn"];
 *
 * To select all Calibre custom columns you can use wildcard : ["*"];
 *
 * Note that the composite custom columns are not supported
 */
$config['cops_calibre_custom_column_list'] = [];

/*
 * Custom Columns for the book preview panel
 * to add as an array containing the lookup names configured in Calibre
 *
 * For example : ["genre", "mycolumn"];
 *
 * To select all Calibre custom columns you can use wildcard : ["*"];
 *
 * Note that the composite custom columns are not supported
 */
$config['cops_calibre_custom_column_preview'] = [];

/*
 * split custom columns of type Date by year
 * 1 : Yes
 * 0 : No
 * For example for 'last_read' column
 */
$config['cops_custom_date_split_year'] = '1';

/*
 * split custom columns of type Integer by range
 * >1 : Yes using this number of ranges
 * 1 : Yes using 'max_item_per_page' ranges
 * 0 : No
 * For example for 'num_pages' column split into 10 ranges:
 * $config['cops_custom_integer_split_range'] = 10;
 */
$config['cops_custom_integer_split_range'] = 0;

/*
 * Start working on hierarchical tags or custom columns
 * @todo https://manual.calibre-ebook.com/sub_groups.html
 *
 * For example for 'tags' and 'Type2' custom column in csv format:
 * $config['calibre_categories_using_hierarchy'] = ['tags', 'Type2'];
 * Note: here you need to specify the title/name of the custom column, not the lookup name = different from above
 *
 * Hierarchy will only be visible in templates that supports them: 'bootstrap2' and 'twigged' for now
 * Caution: this requires *write* access to the database if any parents are missing in the hierarchy
 */
$config['calibre_categories_using_hierarchy'] = [];

/*
 * Rename .epub to .kepub.epub if downloaded from a Kobo eReader
 * The ebook will then be recognized a Kepub so with chaptered paging, statistics, ...
 * You have to enable URL rewriting if you want to enable kepup.epub download
 * 1 : Yes (enable)
 * 0 : No
 */
$config['cops_provide_kepub'] = '0';

/*
 * Use external 'kepubify' tool to convert .epub files to .kepub.epub format for Kobo
 * Example:
 * $config['cops_kepubify_path'] = '/usr/bin/kepubify';
 */
$config['cops_kepubify_path'] = '';

/*
 * Enable and configure Send To Kindle (or Email) feature.
 *
 * Don't forget to authorize the sender email you configured in your Kindle's  Approved Personal Document E-mail List.
 *
 * If you want to use a simple smtp server (provided by your ISP for example), you can configure it like that :
 * $config['cops_mail_configuration'] = [
 *     "smtp.host"     => "smtp.free.fr",
 *     "smtp.username" => "",
 *     "smtp.password" => "",
 *     "smtp.secure"   => "",
 *     "smtp.port"     => "", // Not mandatory, if smtp.secure is set then defaults to 465
 *     "address.from"  => "cops@slucas.fr",
 *     "subject"       => "Sent by COPS : " // Not mandatory
 * ];
 *
 * For Gmail (ssl is mandatory) :
 * $config['cops_mail_configuration'] = [
 *     "smtp.host"     => "smtp.gmail.com",
 *     "smtp.username" => "YOUR GMAIL ADRESS",
 *     "smtp.password" => "YOUR GMAIL PASSWORD",
 *     "smtp.secure"   => "ssl",
 *     "address.from"  => "cops@slucas.fr"
 * ];
 *
 * For GMX (tls and 587 is mandatory) :
 * $config['cops_mail_configuration'] = [
 *     "smtp.host"   => "mail.gmx.com",
 *     "smtp.username" => "YOUR GMX ADRESS",
 *     "smtp.password" => "YOUR GMX PASSWORD",
 *     "smtp.secure"   => "tls",
 *     "smtp.port"     => "587",
 *     "address.from"  => "cops@slucas.fr"
 * ];
 */
$config['cops_mail_configuration'] = null;

/*
 * Use filter in HTML catalog
 * 1 : Yes (enable)
 * 0 : No
 */
$config['cops_html_tag_filter'] = '0';

/*
 * Thumbnails are generated on-the-fly so it can be problematic on servers with slow CPU (Raspberry Pi, Dockstar, Piratebox, ...).
 * This configuration item allow to customize how thumbnail will be generated
 * "" : Generate thumbnail (CPU hungry)
 * "1" : always send the full size image (Network hungry)
 * any url : Send a constant image as the thumbnail (you can try "images/bookcover.png")
 */
$config['cops_thumbnail_handling'] = '';

/*
 * Default thumbnail to use in OPDS and HTML catalog if none is available
 * Set to '' if you don't want to use some default thumbnail as fallback
 */
$config['cops_thumbnail_default'] = 'images/icons/icon144.png';

/*
 * Directory to keep resized thumbnails: allow to resize thumbnails only on first access, then use this cache.
 * $config['cops_thumbnail_handling'] must be ""
 * "" : don't cache thumbnail
 * "/tmp/cache/" (example) : will generate thumbnails in /tmp/cache/
 * BEWARE : it has to end with a /
 */
$config['cops_thumbnail_cache_directory'] = '';

/*
 * Contains a list of user agent for browsers not compatible with client side rendering
 * For now : Kindle, Sony PRS-T1, Sony PRS-T2, All Cybook devices (maybe a little extreme).
 * This item is used as regular expression so "." will force server side rendering for all devices
 */
$config['cops_server_side_render'] = 'Kindle\/1\.\d|Kindle\/2\.\d|Kindle\/3\.\d|EBRD1101|EBRD1201|cybook|Kobo';

/*
 * Specify the ignored categories for the home screen and with search
 * Meaning that if you don't want to search in publishers or tags just add them from the list
 * Only accepted values :
 * - author
 * - book
 * - series
 * - tag
 * - publisher
 * - rating
 * - language
 * - format
 * - identifier
 * - libraries
 */
$config['cops_ignored_categories'] = ['format', 'identifier'];

/*
 * If you use a Sony eReader or Aldiko you can't download ebooks if your catalog
 * is password protected. A simple workaround is to leave index.php/fetch not protected (see .htaccess).
 * But In that case your COPS installation is not completely safe.
 * Setting this parameter to "1" ensure that nobody can access index.php/fetch before accessing
 * index.php or index.php/feed first.
 * BEWARE : Do not touch this if you're not using password, not using PRS-TX or not using Aldiko.
 */
$config['cops_fetch_protect'] = '0';

/*
 * Session timeout for cookie lifetime (client) and garbage collection (server)
 * Session is used to validate fetching/zipping books or customize COPS, so we use long timeout here
 */
$config['cops_session_timeout'] = 365 * 24 * 60 * 60;

/*
 * Session cookie name - avoid overlap with other PHP session names
 */
$config['cops_session_name'] = 'COPS_SESSID';

/*
 * WARNING NOT READY FOR PRODUCTION USE
 * Make the search better (don't care about diacritics, uppercase should work on Cyrillic) but slower.
 * 1 : Yes (enable)
 * 0 : No
 */
$config['cops_normalized_search'] = '0';

/*
 * Get remote user authentication from server for:
 * 1. basic authentication: use 'PHP_AUTH_USER' for Apache or Nginx if configured to pass Authorization header
 * 2. auth done by reverse proxy: use 'REMOTE_USER' or 'X-WEBAUTH-USER' or ... depending on auth proxy config
 * In the 2nd case, authentication is already done by the reverse proxy, and only the remote user is passed
 */
$config['cops_http_auth_user'] = 'PHP_AUTH_USER';

/*
 * Calibre user accounts database as configured for content server
 * e.g. '/config/.config/calibre/server-users.sqlite' - WARNING: passwords are in clear!
 * This can be used to get user info for auth done by reverse proxy, or to verify password for basic authentication
 */
$config['calibre_user_database'] = null;

/*
 * Enable PHP password protection (You can use if htpasswd is not possible for you)
 * If possible prefer htpasswd or authentication done by reverse proxy!
 * Supported values:
 *     array with ["username" => "xxx", "password" => "secret"] : Enable PHP password protection
 *     null : Disable PHP password protection (You can still use htpasswd or reverse proxy)
 *     string with $config['calibre_user_database'] : Calibre user accounts database - WARNING: passwords are in clear!
 */
$config['cops_basic_authentication'] = null;

/*
 * Which template is used by default :
 * 'default'
 * 'bootstrap'
 * 'bootstrap2'
 * 'bootstrap5'
 * 'twigged'
 */
$config['cops_template'] = 'bootstrap2';

/*
 * Which style is used by default :
 * 'base'
 * 'default'
 * 'eink' (only available for the 'default' template)
 * 'iphone' (only available for the 'default' template)
 * 'iphone7' (only available for the 'default' template)
 * 'kindle' (only available for the 'default' template)
 */
$config['cops_style'] = 'default';

/*
 * Which of these templates use the Twig template engine
 * Only the 'twigged' template for now...
 */
$config['cops_twig_templates'] = ['twigged'];

/*
 * Which URL prefix to use in templates for js & css assets (without trailing /)
 */
$config['cops_assets'] = 'vendor/npm-asset';

/*
 * Set language code to force a language (see lang/ directory for available languages).
 * When empty it will auto detect the language.
 */
$config['cops_language'] = '';

/*
 * Set Home page for library
 * Can be any of the pages defined as constants in src/Pages/PageId.php
 * e.g. ALL_RECENT_BOOKS to get straight to most recent books
 *      AUTHORS_FIRST_LETTER to list all authors
 *      ALL_TAGS to list all tags
 *      INDEX to use the default
 */
$config['cops_home_page'] = 'INDEX';

/*
 * Show book count for "Not Set" columns in All<Column> pages and filter "Not Set" books in <Column>Detail pages
 * Use cases: unrated books, or books not tagged, or books without custom lastread date, ...
 * Note: author, language and publisher are always assumed present and cannot be filtered here
 *
 * Available values: ['custom', 'rating', 'series', 'tag', 'identifier', 'format', 'libraries']
 */
$config['cops_show_not_set_filter'] = ['custom', 'rating', 'series', 'tag', 'identifier', 'format', 'libraries'];

/*
 * Show links to sort by title, author, pubdate, rating or timestamp in HTML page detail
 *
 * Available values: ['title', 'author', 'pubdate', 'rating', 'timestamp']
 */
$config['cops_html_sort_links'] = ['title', 'author', 'pubdate', 'rating', 'timestamp'];

/*
 * Show links to filter by Author, Language, Publisher, Rating, Serie or Tag in HTML page detail
 * Note: this replaces 'cops_show_filter_links' in previous release, and now expects an array
 *
 * Available values: ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'identifier', 'format']
 */
$config['cops_html_filter_links'] = ['author', 'language', 'publisher', 'rating', 'series', 'tag'];

/*
 * Number of filter links to show per category in HTML page detail
 */
$config['cops_html_filter_limit'] = '8';

/*
 * Show links to sort by title, author, pubdate, rating or timestamp in OPDS catalog (using facets)
 * Note: this will only work if your e-reader supports facets in OPDS feeds, like Thorium Reader for example
 * See https://specs.opds.io/opds-1.2.html#4-facets for specification details
 * To disable this feature, use an empty array in config/local.php:
 * $config['cops_opds_sort_links'] = [];
 *
 * Available values: ['title', 'author', 'pubdate', 'rating', 'timestamp']
 */
$config['cops_opds_sort_links'] = ['title', 'author', 'pubdate', 'rating', 'timestamp'];

/*
 * Show links to filter by Author, Language, Publisher, Rating, Serie or Tag in OPDS catalog (using facets)
 * Note: this will only work if your e-reader supports facets in OPDS feeds, like Thorium Reader for example
 * See https://specs.opds.io/opds-1.2.html#4-facets for specification details
 * To disable this feature, use an empty array in config/local.php:
 * $config['cops_opds_filter_links'] = [];
 *
 * Available values: ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'identifier', 'format']
 */
$config['cops_opds_filter_links'] = ['author', 'language', 'rating', 'tag'];

/*
 * Number of filter links to show per category in OPDS catalog
 */
$config['cops_opds_filter_limit'] = '8';

/*
 * Allow downloading all books shown on a page for a specific format
 * Example: $config['cops_download_page'] = ['EPUB'];
 *
 * To get any format in prefered_format order you can use : ['ANY'];
 * Example: $config['cops_download_page'] = ['ANY'];
 */
$config['cops_download_page'] = [];

/*
 * Save to disk template for book filenames inside the .zip download file
 */
$config['cops_download_template'] = '{author}{series:| - | #}{series_index} - {title}';

/*
 * Save to disk template for book filenames when fetching a book format - partial
 * @see https://manual.calibre-ebook.com/template_lang.html please submit PR or issue if you need anything else
 *
 * Supported values:
 * "" : Use default filename based on:
 *      ASCII {title} - {author} with .format extension for non-epub or non-updated epub files, or
 *      {author_sort} - {title} with .epub or .kepub.epub extension for updated epub/kepub files
 * "{author_sort}{series:| - | #}{series_index} - {title}" : Use field names and optional prefix/suffix text
 *
 * Supported fields:
 * {title}, {title_sort}, {authors}, {author_sort}, {author}, {series}, {series_index}
 *
 * Note: any extra formats or functions after the field are simply ignored here
 */
$config['cops_download_filename'] = '';

/*
 * Set front controller to remove index.php/ from route URLs generated in COPS
 *
 * Note: this assumes your web server config will rewrite /... to /index.php/...
 * - Apache: .htaccess
 * - Nginx: nginx.conf
 * - PHP built-in: router.php
 * - ...
 *
 * $config['cops_front_controller'] = 'index.php';
 */
$config['cops_front_controller'] = '';

/*
 * Specify api key to access some restricted features via REST API (dev only)
 *
 * Example: generate a random api key once via command line
 * $ php -r 'echo bin2hex(random_bytes(20));'
 */
$config['cops_api_key'] = '';

/*
 * Choose preferred epub reader when viewing epub files online:
 * 'monocle' (default)
 * 'epubjs'
 */
$config['cops_epub_reader'] = 'monocle';

/*
 * Default customize values per user - @todo
 */
$config['cops_customize'] = [];

/*
 * Enable admin features in COPS - @todo
 * Supported values:
 *     false = disable admin features (default)
 *     true = enable admin features for everyone (*)
 *     "xxx" = enable admin features for authenticated user "xxx"
 *     ["xxx", "yyy"] = enable admin features for authenticated users "xxx" and "yyy"
 * (*) Warning: do *not* enable for everyone if COPS can be accessed via Internet
 */
$config['cops_enable_admin'] = false;

/*
 * Configure epubjs-reader as used in templates/epubjs-reader.html
 * This is a javascript object stored as text string for the template
 *
 * const settings = {{=it.settings}};
 *
 * Note: we could put this into a separate .json file that is downloaded
 * by the template javascript at some point
 *
 * See https://github.com/mikespub-org/intity-epubjs-reader/issues/2#issuecomment-2043469571
 * for examples
 *
$config['cops_epubjs_reader_settings'] = '{
    arrows: "content", // none | content | toolbar - depending on this.isMobile
    restore: true,
    history: true,
    openbook: false,
    language: "en",
    sectionId: undefined,
    bookmarks: [],   // array | false
    annotations: [], // array | false
    flow: "paginated", // paginated | scrolled
    spread: {
        mod: "auto", // auto | none
        min: 800
    },
    styles: {
        fontSize: 100
    },
    pagination: undefined, // not implemented
    fullscreen: document.fullscreenEnabled // default behaviour
}';
 */
$config['cops_epubjs_reader_settings'] = '{ arrows: "content", flow: "paginated", openbook: false }';
