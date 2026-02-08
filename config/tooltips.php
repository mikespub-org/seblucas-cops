<?php

$tooltips = [];

$tooltips['calibre_directory'] = <<<'EOT'
    The directory containing calibre's metadata.db file, with sub-directories
    containing all the formats.
    BEWARE : it has to end with a /
    You can enable multiple database with this notation instead of a simple string :
    $config['calibre_directory'] = [
        "My database name" => "/home/directory/calibre1/",
        "My other database name" => "/home/directory/calibre2/",
    ];
    Each user can have its own set of databases in config/local.{remote_user}.php
    Each database can have its own config options in config/local.db-{database}.php
    for common database setup, or config/local.{remote_user}.db-{database}.php for
    user-specific set of databases (see issue #160)
    EOT;

$tooltips['calibre_external_storage'] = <<<'EOT'
    Custom configuration if your Calibre library is stored elsewhere and
    you cannot sync it or mount a remote volume to it on your COPS server.
    For example a static website, AWS S3 bucket, Nextcloud site, Google Drive, ...
    You will still need to copy the metadata.db database file to the calibre
    directory above for this to work though...
    Note: not all features will be available in this configuration, e.g.
    no kepub version or metadata updates, and no monocle epub reader
    Example (test):
    $config['calibre_external_storage'] = 'http://localhost/cops/tests/BaseWithSomeBooks/';
    EOT;

$tooltips['calibre_database_field_cover'] = <<<'EOT'
    Custom configuration if your Calibre library was created with epub-loader,
    and it contains an extra 'cover' field in the books table for special covers
    Example (with epub-loader):
    $config['calibre_database_field_cover'] = 'cover';
    EOT;

$tooltips['calibre_database_field_image'] = <<<'EOT'
    Custom configuration if your Calibre library was created with epub-loader,
    and it contains an extra 'image' field in the authors and/or series tables
    Example (with epub-loader):
    $config['calibre_database_field_image'] = 'image';
    EOT;

$tooltips['cops_full_url'] = <<<'EOT'
    Full URL prefix (with trailing /)
    useful where a full URL is required, COPS is behind a proxy or the script name shows the wrong place
    Older e-readers like Mantano, Aldiko and Marvin may require it for Opensearch too.
    e.g.
    $config['cops_full_url'] = '/cops/';
    $config['cops_full_url'] = 'http://localhost/cops/';
    $config['cops_full_url'] = 'http://192.168.1.xxx/cops/';
    $config['cops_full_url'] = 'https://www.domainname.com/cops/';
    EOT;

$tooltips['cops_trusted_proxies'] = <<<'EOT'
    As an alternative for cops_full_url above, if you're using a reverse proxy and you want to
    change how COPS is accessed depending on the entrypoint (e.g. direct, local network, internet)
    you can define trusted proxies and trusted headers here
    Note: using symfony/http-foundation to support X-Forwarded-* and Forwarded headers from proxies
    @see https://symfony.com/doc/current/deployment/proxies.html
    EOT;

$tooltips['cops_trusted_headers'] = <<<'EOT'
    EOT;

$tooltips['cops_resources_cdn'] = <<<'EOT'
    Use CDN for resources like images, files etc.
    This will replace the base url or full url above
    @see https://github.com/mikespub-org/seblucas-cops/wiki/Reverse-proxy-configurations#using-cdn-for-cops-resources
    EOT;

$tooltips['cops_recentbooks_limit'] = <<<'EOT'
    Number of recent books to show
    EOT;

$tooltips['cops_author_name'] = <<<'EOT'
    Catalog's author name
    EOT;

$tooltips['cops_author_uri'] = <<<'EOT'
    Catalog's author uri
    EOT;

$tooltips['cops_author_email'] = <<<'EOT'
    Catalog's author email
    EOT;

$tooltips['cops_title_default'] = <<<'EOT'
    Catalog's title
    EOT;

$tooltips['cops_subtitle_default'] = <<<'EOT'
    Catalog's subtitle
    EOT;

$tooltips['cops_x_accel_redirect'] = <<<'EOT'
    Wich header to use when downloading books outside the web directory
    Possible values are :
      X-Accel-Redirect   : For Nginx
      X-Sendfile         : For Lightttpd or Apache (with mod_xsendfile)
      No value (default) : Let PHP handle the download
    EOT;

$tooltips['cops_x_accel_mapping'] = <<<'EOT'
    Absolute path mapping for books is assumed to be the same between web server and PHP COPS
    Example: if COPS finds books in /mapped/library, then nginx config must have something like:
    # X-Accel-Redirect uri from COPS - see config/local.php
    location /mapped/library {
        # internal redirect for nginx
        internal;
        # actual path on nginx server
        alias /volume1/Calibre
        # or if COPS and nginx are on the same server
        #root /
    }
    EOT;

$tooltips['cops_opds_thumbnail_height'] = <<<'EOT'
    Height of thumbnail image for OPDS (thumb=opds)
    Note: book detail uses image height x 2 (thumb=opds2)
    EOT;

$tooltips['cops_html_thumbnail_height'] = <<<'EOT'
    Height of thumbnail image for HTML (thumb=html)
    Note: book detail uses image height x 2 (thumb=html2)
    EOT;

$tooltips['cops_icon'] = <<<'EOT'
    Icon for both OPDS and HTML catalog
    Note that this has to be a real icon (.ico)
    EOT;

$tooltips['cops_show_icons'] = <<<'EOT'
    Show icon for authors, series, tags and books on OPDS feed
     1 : enable
     0 : disable
    EOT;

$tooltips['default_timezone'] = <<<'EOT'
    Default timezone
    Check following link for other timezones :
    https://www.php.net/manual/en/timezones.php
    EOT;

$tooltips['cops_prefered_format'] = <<<'EOT'
    Prefered format for HTML catalog
    The two first will be displayed in book entries
    The other only appear in book detail
    EOT;

$tooltips['cops_ignored_formats'] = <<<'EOT'
    Specify the ignored formats that will never display in COPS
    This will also stop downloading them, unless the files are under the web directory
    EOT;

$tooltips['cops_generate_invalid_opds_stream'] = <<<'EOT'
    generate a invalid OPDS stream to allow bad OPDS client to use search
    Example of non compliant OPDS client : Moon+ Reader
    Example of good OPDS client : Mantano, FBReader
     1 : enable support for non compliant OPDS client
     0 : always generate valid OPDS code
    EOT;

$tooltips['cops_max_item_per_page'] = <<<'EOT'
    Max number of items per page
    -1 unlimited
    EOT;

$tooltips['cops_author_split_first_letter'] = <<<'EOT'
    split authors by first letter
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_titles_split_first_letter'] = <<<'EOT'
    split titles by first letter
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_titles_split_publication_year'] = <<<'EOT'
    split titles by publication year (if not split by first letter)
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_publisher_split_first_letter'] = <<<'EOT'
    split publishers by first letter
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_series_split_first_letter'] = <<<'EOT'
    split series by first letter
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_tag_split_first_letter'] = <<<'EOT'
    split tags by first letter
    1 : Yes
    0 : No
    EOT;

$tooltips['cops_use_fancyapps'] = <<<'EOT'
    Enable the Lightboxes (for popups) in 'default' template with client side rendering
    1 : Yes (enable)
    0 : No
    EOT;

$tooltips['cops_update_epub-metadata'] = <<<'EOT'
    Update Epub metadata before download
    1 : Yes (enable)
    0 : No
    EOT;

$tooltips['cops_books_filter'] = <<<'EOT'
    Filter on tags to book list
    Only works with the OPDS catalog
    Usage : [
        "I only want to see books using the tag : Tag1"     => "Tag1",
        "I only want to see books not using the tag : Tag1" => "!Tag1",
        "I want to see every books"                         => "",
    ];
    Example : ["All" => "", "Unread" => "!Read", "Read" => "Read"];
    EOT;

$tooltips['cops_calibre_virtual_libraries'] = <<<'EOT'
    Virtual libraries
    to add as an array containing the library names configured in Calibre
    This is not supported in combination with multiple databases

    For example : ["Short Stories in English", "Fiction from this Century"];

    To select all Calibre virtual libraries you can use wildcard :
    $config['cops_calibre_virtual_libraries'] = ["*"];

    @todo https://manual.calibre-ebook.com/virtual_libraries.html
    Note that search criteria for virtual libraries are very limited in COPS (TODO)
    EOT;

$tooltips['cops_virtual_library'] = <<<'EOT'
    Default virtual library to use (and filter on) in COPS
    based on the Calibre list of virtual libraries (starting with 1)
    This is not supported in combination with multiple databases
    EOT;

$tooltips['cops_database_filter'] = <<<'EOT'
    Filter Calibre database by tags, languages etc.
    Example:
    $config['cops_database_filter'] = [
        "tags": "Short Stories",
        "language": "eng",
    ];

    For negative filters start with !, example: "!Short Stories"

    Can be used as alternative to virtual libraries
    also in multi-database/multi-user configurations
    by putting this in the right local.*.php file(s)
    EOT;

$tooltips['cops_calibre_custom_column'] = <<<'EOT'
    Custom Columns for the index page
    to add as an array containing the lookup names configured in Calibre

    For example : ["genre", "mycolumn"];

    To select all Calibre custom columns you can use wildcard : ["*"];

    Note that the composite custom columns are not supported
    EOT;

$tooltips['cops_calibre_custom_column_list'] = <<<'EOT'
    Custom Columns for the list representation
    to add as an array containing the lookup names configured in Calibre

    For example : ["genre", "mycolumn"];

    To select all Calibre custom columns you can use wildcard : ["*"];

    Note that the composite custom columns are not supported
    EOT;

$tooltips['cops_calibre_custom_column_preview'] = <<<'EOT'
    Custom Columns for the book preview panel
    to add as an array containing the lookup names configured in Calibre

    For example : ["genre", "mycolumn"];

    To select all Calibre custom columns you can use wildcard : ["*"];

    Note that the composite custom columns are not supported
    EOT;

$tooltips['cops_custom_date_split_year'] = <<<'EOT'
    split custom columns of type Date by year
    1 : Yes
    0 : No
    For example for 'last_read' column
    EOT;

$tooltips['cops_custom_integer_split_range'] = <<<'EOT'
    split custom columns of type Integer by range
    >1 : Yes using this number of ranges
    1 : Yes using 'max_item_per_page' ranges
    0 : No
    For example for 'num_pages' column split into 10 ranges:
    $config['cops_custom_integer_split_range'] = 10;
    EOT;

$tooltips['calibre_categories_using_hierarchy'] = <<<'EOT'
    Start working on hierarchical tags or custom columns
    @todo https://manual.calibre-ebook.com/sub_groups.html

    For example for 'tags' and 'Type2' custom column in csv format:
    $config['calibre_categories_using_hierarchy'] = ['tags', 'Type2'];
    Note: here you need to specify the title/name of the custom column, not the lookup name = different from above

    Hierarchy will only be visible in templates that supports them: 'bootstrap2' and 'twigged' for now
    Caution: this requires *write* access to the database if any parents are missing in the hierarchy
    EOT;

$tooltips['cops_provide_kepub'] = <<<'EOT'
    Rename .epub to .kepub.epub if downloaded from a Kobo eReader
    The ebook will then be recognized a Kepub so with chaptered paging, statistics, ...
    You have to enable URL rewriting if you want to enable kepup.epub download
    1 : Yes (enable)
    0 : No
    EOT;

$tooltips['cops_kepubify_path'] = <<<'EOT'
    Use external 'kepubify' tool to convert .epub files to .kepub.epub format for Kobo
    Example:
    $config['cops_kepubify_path'] = '/usr/bin/kepubify';
    EOT;

$tooltips['cops_mail_configuration'] = <<<'EOT'
    Enable and configure Send To Kindle (or Email) feature.

    Don't forget to authorize the sender email you configured in your Kindle's  Approved Personal Document E-mail List.

    If you want to use a simple smtp server (provided by your ISP for example), you can configure it like that :
    $config['cops_mail_configuration'] = [
        "smtp.host"     => "smtp.free.fr",
        "smtp.username" => "",
        "smtp.password" => "",
        "smtp.secure"   => "",
        "smtp.port"     => "", // Not mandatory, if smtp.secure is set then defaults to 465
        "address.from"  => "cops@slucas.fr",
        "subject"       => "Sent by COPS : " // Not mandatory
    ];

    For Gmail (ssl is mandatory) :
    $config['cops_mail_configuration'] = [
        "smtp.host"     => "smtp.gmail.com",
        "smtp.username" => "YOUR GMAIL ADRESS",
        "smtp.password" => "YOUR GMAIL PASSWORD",
        "smtp.secure"   => "ssl",
        "address.from"  => "cops@slucas.fr"
    ];

    For GMX (tls and 587 is mandatory) :
    $config['cops_mail_configuration'] = [
        "smtp.host"   => "mail.gmx.com",
        "smtp.username" => "YOUR GMX ADRESS",
        "smtp.password" => "YOUR GMX PASSWORD",
        "smtp.secure"   => "tls",
        "smtp.port"     => "587",
        "address.from"  => "cops@slucas.fr"
    ];
    EOT;

$tooltips['cops_html_tag_filter'] = <<<'EOT'
    Use filter in HTML catalog
    1 : Yes (enable)
    0 : No
    EOT;

$tooltips['cops_thumbnail_handling'] = <<<'EOT'
    Thumbnails are generated on-the-fly so it can be problematic on servers with slow CPU (Raspberry Pi, Dockstar, Piratebox, ...).
    This configuration item allow to customize how thumbnail will be generated
    "" : Generate thumbnail (CPU hungry)
    "1" : always send the full size image (Network hungry)
    any url : Send a constant image as the thumbnail (you can try "images/bookcover.png")
    EOT;

$tooltips['cops_thumbnail_default'] = <<<'EOT'
    Default thumbnail to use in OPDS and HTML catalog if none is available
    Set to '' if you don't want to use some default thumbnail as fallback
    EOT;

$tooltips['cops_thumbnail_cache_directory'] = <<<'EOT'
    Directory to keep resized thumbnails: allow to resize thumbnails only on first access, then use this cache.
    $config['cops_thumbnail_handling'] must be ""
    "" : don't cache thumbnail
    "/tmp/cache/" (example) : will generate thumbnails in /tmp/cache/
    BEWARE : it has to end with a /
    EOT;

$tooltips['cops_server_side_render'] = <<<'EOT'
    Contains a list of user agent for browsers not compatible with client side rendering
    For now : Kindle, Sony PRS-T1, Sony PRS-T2, All Cybook devices (maybe a little extreme).
    This item is used as regular expression so "." will force server side rendering for all devices
    EOT;

$tooltips['cops_ignored_categories'] = <<<'EOT'
    Specify the ignored categories for the home screen and with search
    Meaning that if you don't want to search in publishers or tags just add them from the list
    Only accepted values :
    - author
    - book
    - series
    - tag
    - publisher
    - rating
    - language
    - format
    - identifier
    - libraries
    EOT;

$tooltips['cops_fetch_protect'] = <<<'EOT'
    If you use a Sony eReader or Aldiko you can't download ebooks if your catalog
    is password protected. A simple workaround is to leave index.php/fetch not protected (see .htaccess).
    But In that case your COPS installation is not completely safe.
    Setting this parameter to "1" ensure that nobody can access index.php/fetch before accessing
    index.php or index.php/feed first.
    BEWARE : Do not touch this if you're not using password, not using PRS-TX or not using Aldiko.
    EOT;

$tooltips['cops_session_timeout'] = <<<'EOT'
    Session timeout for cookie lifetime (client) and garbage collection (server)
    Session is used to validate fetching/zipping books or customize COPS, so we use long timeout here
    EOT;

$tooltips['cops_session_name'] = <<<'EOT'
    Session cookie name - avoid overlap with other PHP session names
    EOT;

$tooltips['cops_normalized_search'] = <<<'EOT'
    WARNING NOT READY FOR PRODUCTION USE
    Make the search better (don't care about diacritics, uppercase should work on Cyrillic) but slower.
    1 : Yes (enable)
    0 : No
    EOT;

$tooltips['cops_http_auth_user'] = <<<'EOT'
    Get remote user authentication from server for:
    1. basic authentication: use 'PHP_AUTH_USER' for Apache or Nginx if configured to pass Authorization header
    2. auth done by reverse proxy: use 'REMOTE_USER' or 'X-WEBAUTH-USER' or ... depending on auth proxy config
    In the 2nd case, authentication is already done by the reverse proxy, and only the remote user is passed
    EOT;

$tooltips['calibre_user_database'] = <<<'EOT'
    Calibre user accounts database as configured for content server
    e.g. '/config/.config/calibre/server-users.sqlite' - WARNING: passwords are in clear!
    This can be used to get user info for auth done by reverse proxy, or to verify password for basic authentication
    EOT;

$tooltips['cops_basic_authentication'] = <<<'EOT'
    Enable PHP password protection (You can use if htpasswd is not possible for you)
    If possible prefer htpasswd or authentication done by reverse proxy!
    Supported values:
        array with ["username" => "xxx", "password" => "secret"] : Enable PHP password protection
        null : Disable PHP password protection (You can still use htpasswd or reverse proxy)
        string with $config['calibre_user_database'] : Calibre user accounts database - WARNING: passwords are in clear!
    EOT;

$tooltips['cops_form_authentication'] = <<<'EOT'
    Enable form password protection via login.html (You can use if htpasswd is not possible for you)
    If possible prefer htpasswd or authentication done by reverse proxy!
    Supported values:
        array with ["username" => "xxx", "password" => "secret"] : Enable form password protection
        null : Disable form password protection (You can still use htpasswd or reverse proxy)
        string with $config['calibre_user_database'] : Calibre user accounts database - WARNING: passwords are in clear!
    EOT;

$tooltips['cops_template'] = <<<'EOT'
    Which template is used by default :
    'default'
    'bootstrap'
    'bootstrap2'
    'bootstrap5'
    'twigged'
    EOT;

$tooltips['cops_style'] = <<<'EOT'
    Which style is used by default :
    'base'
    'default'
    'eink' (only available for the 'default' template)
    'iphone' (only available for the 'default' template)
    'iphone7' (only available for the 'default' template)
    'kindle' (only available for the 'default' template)
    EOT;

$tooltips['cops_twig_templates'] = <<<'EOT'
    Which of these templates use the Twig template engine
    Only the 'twigged' template for now...
    EOT;

$tooltips['cops_assets'] = <<<'EOT'
    Which URL prefix to use in templates for js & css assets (without trailing /)
    EOT;

$tooltips['cops_language'] = <<<'EOT'
    Set language code to force a language (see lang/ directory for available languages).
    When empty it will auto detect the language.
    EOT;

$tooltips['cops_home_page'] = <<<'EOT'
    Set Home page for library
    Can be any of the pages defined as constants in src/Pages/PageId.php
    e.g. ALL_RECENT_BOOKS to get straight to most recent books
         AUTHORS_FIRST_LETTER to list all authors
         ALL_TAGS to list all tags
         INDEX to use the default
    EOT;

$tooltips['cops_show_not_set_filter'] = <<<'EOT'
    Show book count for "Not Set" columns in All<Column> pages and filter "Not Set" books in <Column>Detail pages
    Use cases: unrated books, or books not tagged, or books without custom lastread date, ...
    Note: author, language and publisher are always assumed present and cannot be filtered here

    Available values: ['custom', 'rating', 'series', 'tag', 'identifier', 'format', 'libraries']
    EOT;

$tooltips['cops_html_sort_links'] = <<<'EOT'
    Show links to sort by title, author, pubdate, rating or timestamp in HTML page detail

    Available values: ['title', 'author', 'pubdate', 'rating', 'timestamp']
    EOT;

$tooltips['cops_html_filter_links'] = <<<'EOT'
    Show links to filter by Author, Language, Publisher, Rating, Serie or Tag in HTML page detail
    Note: this replaces 'cops_show_filter_links' in previous release, and now expects an array

    Available values: ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'identifier', 'format']
    EOT;

$tooltips['cops_html_filter_limit'] = <<<'EOT'
    Number of filter links to show per category in HTML page detail
    EOT;

$tooltips['cops_opds_sort_links'] = <<<'EOT'
    Show links to sort by title, author, pubdate, rating or timestamp in OPDS catalog (using facets)
    Note: this will only work if your e-reader supports facets in OPDS feeds, like Thorium Reader for example
    See https://specs.opds.io/opds-1.2.html#4-facets for specification details
    To disable this feature, use an empty array in config/local.php:
    $config['cops_opds_sort_links'] = [];

    Available values: ['title', 'author', 'pubdate', 'rating', 'timestamp']
    EOT;

$tooltips['cops_opds_filter_links'] = <<<'EOT'
    Show links to filter by Author, Language, Publisher, Rating, Serie or Tag in OPDS catalog (using facets)
    Note: this will only work if your e-reader supports facets in OPDS feeds, like Thorium Reader for example
    See https://specs.opds.io/opds-1.2.html#4-facets for specification details
    To disable this feature, use an empty array in config/local.php:
    $config['cops_opds_filter_links'] = [];

    Available values: ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'identifier', 'format']
    EOT;

$tooltips['cops_opds_filter_limit'] = <<<'EOT'
    Number of filter links to show per category in OPDS catalog
    EOT;

$tooltips['cops_download_page'] = <<<'EOT'
    Allow downloading all books shown on a page for a specific format
    Example: $config['cops_download_page'] = ['EPUB'];

    To get any format in prefered_format order you can use : ['ANY'];
    Example: $config['cops_download_page'] = ['ANY'];
    EOT;

$tooltips['cops_download_template'] = <<<'EOT'
    Save to disk template for book filenames inside the .zip download file
    EOT;

$tooltips['cops_download_filename'] = <<<'EOT'
    Save to disk template for book filenames when fetching a book format - partial
    @see https://manual.calibre-ebook.com/template_lang.html please submit PR or issue if you need anything else

    Supported values:
    "" : Use default filename based on:
         ASCII {title} - {author} with .format extension for non-epub or non-updated epub files, or
         {author_sort} - {title} with .epub or .kepub.epub extension for updated epub/kepub files
    "{author_sort}{series:| - | #}{series_index} - {title}" : Use field names and optional prefix/suffix text

    Supported fields:
    {title}, {title_sort}, {authors}, {author_sort}, {author}, {series}, {series_index}

    Note: any extra formats or functions after the field are simply ignored here
    EOT;

$tooltips['cops_front_controller'] = <<<'EOT'
    Set front controller to remove index.php/ from route URLs generated in COPS

    Note: this assumes your web server config will rewrite /... to /index.php/...
    - Apache: .htaccess
    - Nginx: nginx.conf
    - PHP built-in: router.php
    - ...

    $config['cops_front_controller'] = 'index.php';
    EOT;

$tooltips['cops_api_key'] = <<<'EOT'
    Specify api key to access some restricted features via REST API (dev only)

    Example: generate a random api key once via command line
    $ php -r 'echo bin2hex(random_bytes(20));'
    EOT;

$tooltips['cops_epub_reader'] = <<<'EOT'
    Choose preferred epub reader when viewing epub files online:
    'monocle' (default)
    'epubjs'
    'custom-reader.html?url=' (custom reader template - adapt as needed)
    EOT;

$tooltips['cops_comic_reader'] = <<<'EOT'
    Choose comic-reader template URL when viewing comic files online:
    '' (default)
    'comic-reader.html?url='
    'codedread-kthoom.html?bookUri=' (with mikespub/codedread-kthoom package)

    Note: for kthoom please install the package with composer:

    $ composer require mikespub/codedread-kthoom
    EOT;

$tooltips['cops_pdfjs_viewer'] = <<<'EOT'
    Choose pdfjs-viewer template URL when viewing pdf files online:
    '' (default)
    'pdfjs-viewer.html?file='

    Note: the release package cops-3.x.x-php82.zip only contains
    minimal parts of the mozilla/pdfjs-dist package. If your PDF
    shows errors, please install the full package with composer:

    $ rm -r vendor/mozilla/pdfjs-dist/
    $ composer install -o
    EOT;

$tooltips['cops_customize'] = <<<'EOT'
    Default customize values per user - @todo
    EOT;

$tooltips['cops_enable_admin'] = <<<'EOT'
    Enable admin features in COPS - @todo
    Supported values:
        false = disable admin features (default)
        true = enable admin features for everyone (*)
        "xxx" = enable admin features for authenticated user "xxx"
        ["xxx", "yyy"] = enable admin features for authenticated users "xxx" and "yyy"
    (*) Warning: do *not* enable for everyone if COPS can be accessed via Internet
    EOT;

$tooltips['cops_epubjs_reader_settings'] = <<<'EOT'
    Configure epubjs-reader as used in templates/epubjs-reader.html
    This is a javascript object stored as text string for the template

    const settings = {{=it.settings}};

    Note: we could put this into a separate .json file that is downloaded
    by the template javascript at some point

    See https://github.com/mikespub-org/intity-epubjs-reader/issues/2#issuecomment-2043469571
    for examples

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
    EOT;
