# COPS for PHP 8.x

## Breaking changes for 4.x release (PHP >= 8.4)

None if you upgrade from 3.x, besides the PHP version

## Breaking changes for 3.x release (PHP >= 8.2)

### For everyone
- move your customized *config_local.php* file to *config/local.php* on the web server/container
- replace any *feed.php* links with *index.php/feed* for OPDS feeds in your e-reader

### Less common
- if you map the *lib/* or *test/* directory somewhere, like a docker compose file, web server config etc. you'll need to replace that with *src/* or *tests/* respectively
- if you use other endpoints for links elsewhere, like the REST API for a widget, you'll need to use route URLs and replace *restapi.php* with *index.php/restapi* etc.

## Prerequisites for this fork

-	PHP 8.x with DOM, GD, Intl, Json, PDO SQLite, SQLite3, XML, Zip and ZLib support (PHP 8.4 or later recommended)
- Release 4.x.x will only work with PHP >= 8.4 - typical for most source code & docker image installs in 2025 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 3.x.x will only work with PHP >= 8.2 - typical for most source code & docker image installs in 2024 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 2.x.x will only work with PHP >= 8.1 - typical for most source code & docker image installs in 2023 and later. *Note: updating .epub files for metadata or cover requires a 64-bit platform*
- Release 1.x.x still works with PHP 7.4 if necessary - earlier PHP 7.x (or 5.x) versions are *not* supported with this fork

User support issues remain available at https://github.com/seblucas/cops/issues - please use it if you need help with COPS in general.
For the 2.x, 3.x and 4.x versions please report any new issues at https://github.com/mikespub-org/seblucas-cops/issues

Pull requests should be against the latest source code at https://github.com/mikespub-org/seblucas-cops/pulls - thanks for any contributions :-)

See [CHANGELOG](CHANGELOG.md) for changes compared to upstream repository https://github.com/seblucas/cops from @seblucas

## Installation of this fork

Same options as original:

1. Release package
  - get latest cops-3.x.x-php8x.zip file from https://github.com/mikespub-org/seblucas-cops/releases

  Note: the release packages `cops-3.x.x-php8x.zip` include the vendor/ packages for a particular PHP version. If you have an older or newer (supported) PHP version, you can download the `Source code (zip)` for that release, and run *composer* to update the dependencies:
  ```
  $ wget -O cops-3.x.x.zip https://github.com/mikespub-org/seblucas-cops/archive/refs/tags/3.x.x.zip
  $ unzip cops-3.x.x.zip
  $ cd seblucas-cops-3.x.x
  $ composer update --no-dev -o
  ```

2. Source code
  - git clone https://github.com/mikespub-org/seblucas-cops.git  # or download [latest main as zip](https://github.com/mikespub-org/seblucas-cops/archive/refs/heads/main.zip)
  - run *composer* to install project dependencies
  ```
  $ cd seblucas-cops
  $ composer install --no-dev -o
  ```

3. Docker image
  - use latest [linuxserver/cops](https://hub.docker.com/r/linuxserver/cops) image from [linuxserver.io](https://github.com/linuxserver/docker-cops) - see setup and usage there
  - or use [docker/Dockerfile.alpine](docker/Dockerfile.alpine) and [docker-compose.yaml](docker-compose.yaml) (or [docker-compose-dev.yaml](docker-compose-dev.yaml)) as starting point - **not** optimized for size/performance

4. Home Assistant Add-on
  - see [HA COPS](https://github.com/dunxd/HomeAssistantAddons/tree/main/COPS) from [dunxd](https://github.com/dunxd/HomeAssistantAddons)

The rest of the installation process is very similar to the original below. But if you install from source, just use your regular composer 2.x - you don't need to download an old composer 1.x version or install global asset plugins anymore :-)

Notice: for a first-time installation, you need to copy *[config/local.php.example](config/local.php.example)* to *config/local.php* and customize the calibre directory etc. as needed. Afterwards, if you get an error or blank page the first time you browse to COPS, you can check for common issues by browsing to http://.../index.php/check or http://.../checkconfig.php

---

# COPS (original)

COPS stands for Calibre OPDS (and HTML) Php Server.

See : [COPS's home](https://blog.slucas.fr/en/oss/calibre-opds-php-server) for more details.

Don't forget to check the [Wiki](https://github.com/seblucas/cops/wiki).

# Why ?

In my opinion Calibre is a marvelous tool but is too big and has too much
dependencies to be used for its content server.

That's the main reason why I coded this OPDS server. I needed a simple
tool to be installed on a small server (Seagate Dockstar in my case).

I initially thought of Calibre2OPDS but as it generate static file no
search was possible.

Later I added an simple HTML catalog that should be usable on my Kobo.

So COPS's main advantages are :
 * No need for many dependencies.
 * No need for a lot of CPU or RAM.
 * Not much code.
 * Search is available.
 * It was fun to code.

If you want to use the OPDS feed don't forget to specify feed.php at the end of your URL.

You just have to sync your Calibre directory to your COPS server the way you prefer (Dropbox, Bt Sync, Syncthing, use a directory shared with Nextcloud, ...).

# Prerequisites (outdated)

1. 	PHP 5.3, 5.4, 5.5, 5.6, 7.X or hhvm with GD image processing, Libxml, Intl, Json & SQLite3 support (PHP 5.6 or later recommended).
2. 	A web server with PHP support. I tested with various version of Nginx and Apache.
    Other people reported it working with Apache and Cherokee. You can also use PHP embedded server (https://github.com/seblucas/cops/wiki/Howto---PhpEmbeddedServer)
3.  The path to a calibre library (metadata.db, format, & cover files).

On any Debian based Linux you can use :
 `apt-get install php5-gd php5-sqlite php5-json php5-intl`

If you use Debian Stretch :
 `apt-get install php7.0-gd php7.0-sqlite3 php7.0-json php7.0-intl php7.0-xml php7.0-mbstring php7.0-zip`

On Centos you may have to add :
 yum install php-xml

# Install a release (Easiest way)

1.  Extract the zip file you got from [the release page](https://github.com/seblucas/cops/releases) to a folder in web space (visible to the web server).
2.  If you're doing a first-time install, copy *[config/local.php.example](config/local.php.example)* to *config/local.php* (this differs from the original project)
3.  Edit config/local.php to match your config.
4.  If needed add other configuration item from config/default.php

If you like Docker, you can also try this multiarch docker container from [linuxserver.io](https://hub.docker.com/r/linuxserver/cops/)  It has builds for x64 and arm64. 

# Install from sources

```bash
git clone https://github.com/seblucas/cops.git # or download lastest zip see below
cd cops
# use standard composer 2.x now, no need to install older 1.x version and plugin for PHP 8.x version
#wget https://getcomposer.org/composer.phar
#php composer.phar global require "fxp/composer-asset-plugin:~1.1"
#php composer.phar install --no-dev --optimize-autoloader
composer install --no-dev --optimize-autoloader
```

After that you can use the previous how-to starting at the second step.

Note that instead of cloning you can also get [latest master as zip](https://github.com/seblucas/cops/archive/master.zip)

Note that if your PHP version is lower that 5.6, then you may have to remove `composer.lock` before starting the last line.

# Where to put my Calibre directory ?

Long story short : ALWAYS outside of COPS's directory especially if COPS is installed on a VPS / Server. If you follow my advice then your data will be safe.

If you choose to put your Calibre directory inside your web directory and use Nginx then you will have to edit /etc/nginx/mime.types to add these lines :

```
application/epub+zip epub;
application/x-mobipocket-ebook mobi prc azw;
```

# Known problems

Not a lot, except for the bad quality of the code (first PHP project ever) ;)

Please see https://github.com/seblucas/cops/issues for open issues

# Need help

Please read https://github.com/seblucas/cops/wiki and check the FAQ.

# Contributing

As you could see [here](https://github.com/seblucas/cops/graphs/contributors), I appreciate every contributions and there were a lot over time. So don't be shy and submit your Pull Requests.

Note to translators : please prefer using [Transifex](https://github.com/seblucas/cops/wiki/Update-translations) instead of doing a PR.

I only have one limit (I may have more but that one is the worse) : COPS' goal is to provide an alternative to Calibre's content server and not to replace Calibre entirely. So I will refuse any PR making changes to the database content.

# Credits

 * Locale message handling is inspired of https://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php
 * str_format function come from https://tmont.com/blargh/2010/1/string-format-in-php
 * All icons come from Font Awesome : https://github.com/FortAwesome/Font-Awesome
 * The unofficial OPDS validator : https://opds-validator.appspot.com/
 * Thanks to all testers, translators and contributors.
 * Feed icons made by Freepik from Flaticon website licensed under Creative Commons BY 3.0 https://www.flaticon.com and https://www.freepik.com
 * A huge thanks to Jetbrains for supporting COPS by providing a set of free licenses to their products for several years now!

External libraries used :
 * JQuery : https://jquery.com/
 * Magnific Popup : https://dimsemenov.com/plugins/magnific-popup/
 * Php-epub-meta : https://github.com/splitbrain/php-epub-meta with some modification by me (https://github.com/seblucas/php-epub-meta)
 * TbsZip : https://www.tinybutstrong.com/apps/tbszip/tbszip_help.html
 * DoT.js : https://olado.github.io/doT/index.html
 * PHPMailer : https://github.com/PHPMailer/PHPMailer
 * js-lru : https://github.com/rsms/js-lru

# Copyright & License

COPS - 2012-2019 (c) Sébastien Lucas

See LICENSE and file headers for license info

