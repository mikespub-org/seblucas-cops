<!DOCTYPE html>
<html lang="en">
<?php
/**
 * COPS (Calibre OPDS PHP Server) Configuration check
 * URL format: checkconfig.php?err={err}&full={full}
 *
 * Keep as fallback endpoint for any CheckHandler issues for now
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\ProxyRequest;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Routing\UriGenerator;

require_once __DIR__ . '/config/config.php';
/** @var array<mixed> $config */

$request = new Request();
$err   = $request->get('err', -1);
$full  = $request->get('full');
$error = null;
if (!empty($err) && $err == 1) {
    $error = 'Database error';
}

?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>COPS Configuration Check</title>
    <link rel="stylesheet" type="text/css" href="<?php echo UriGenerator::path($request->style()) ?>" media="screen" />
</head>
<body>
<div class="container">
    <header>
        <div class="headcenter">
            <h1>COPS Configuration Check</h1>
        </div>
    </header>
    <div id="content" style="display: none;"></div>
    <section>
        <?php
        if (!is_null($error)) {
            ?>
        <article class="frontpage">
            <h2>You've been redirected because COPS is not configured properly</h2>
            <h4><?php echo $error ?></h4>
        </article>
        <?php
        }
?>
        <article class="frontpage">
            <h2>Check if PHP version is correct</h2>
            <h4>
            <?php
    if (defined('PHP_VERSION_ID')) {
        if (PHP_VERSION_ID >= 80400) {
            echo 'OK (' . PHP_VERSION . ')';
        } else {
            echo 'Please install PHP >= 8.4 (' . PHP_VERSION . ')';
        }
    } else {
        echo 'Please install PHP >= 8.4';
    }
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if GD is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo 'OK';
} else {
    echo 'Please install the php-gd extension and make sure it\'s enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Sqlite is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('pdo_sqlite')) {
    echo 'OK';
} else {
    echo 'Please install the php-sqlite / php-sqlite3 extension and make sure it\'s enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if libxml is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('libxml')) {
    echo 'OK';
} else {
    echo 'Please make sure libxml is enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if DOM is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('dom')) {
    echo 'OK';
} else {
    echo 'Please install the php-xml extension and make sure DOM is enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if XMLWriter is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('xmlwriter')) {
    echo 'OK';
} else {
    echo 'Please install the php-xml extension and make sure XMLWriter is enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Json is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('json')) {
    echo 'OK';
} else {
    echo 'Please install the php-json extension and make sure it\'s enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if mbstring is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('mbstring')) {
    echo 'OK';
} else {
    echo 'Please install the php-mbstring extension and make sure it\'s enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if intl is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('intl')) {
    echo 'OK';
} else {
    echo 'Please install the php-intl extension and make sure it\'s enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Normalizer class is properly installed and loaded</h2>
            <h4>
            <?php
if (class_exists('Normalizer', $autoload = false)) {
    echo 'OK';
} else {
    echo 'Please make sure intl is enabled in your php.ini';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if zlib is properly installed and loaded</h2>
            <h4>
            <?php
if (extension_loaded('zlib')) {
    echo 'OK';
} else {
    echo 'Please make sure zlib is enabled';
}
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if the base URL looks OK</h2>
            <h4>
            <?php
$base = dirname((string) $_SERVER['SCRIPT_NAME']);
if (!str_ends_with($base, '/')) {
    $base .= '/';
}
echo 'Base URL detected by the script: ' . $base . '<br>';
echo 'Full URL specified in $config[\'cops_full_url\']: ' . $config['cops_full_url'] . '<br>';
if (ProxyRequest::hasTrustedProxies()) {
    echo 'Trusted proxies configured: ' . $config['cops_trusted_proxies'] . '<br>';
    echo 'Trusted headers configured: ' . json_encode($config['cops_trusted_headers']) . '<br>';
    foreach ($config['cops_trusted_headers'] as $name) {
        $header = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        echo $header . ': ' . ($_SERVER[$header] ?? '') . '<br>';
    }
    echo 'Base URL via trusted proxies: ' . UriGenerator::base() . '<br>';
}
echo 'REMOTE_ADDR: ' . ($_SERVER['REMOTE_ADDR'] ?? '') . '<br>';
echo '<br>';
echo 'SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? '') . '<br>';
echo 'HTTP_HOST: ' . ($_SERVER['HTTP_HOST'] ?? '') . '<br>';
echo 'SERVER_NAME: ' . ($_SERVER['SERVER_NAME'] ?? '') . '<br>';
echo 'SERVER_ADDR: ' . ($_SERVER['SERVER_ADDR'] ?? '') . '<br>';
echo 'SERVER_PORT: ' . ($_SERVER['SERVER_PORT'] ?? '') . '<br>';
echo 'REQUEST_SCHEME: ' . ($_SERVER['REQUEST_SCHEME'] ?? '') . '<br>';
echo 'REQUEST_URI: ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . '<br>';
?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if the rendering will be done on client side or server side</h2>
            <h4>
            <?php
if ($request->render()) {
    echo 'Server side rendering';
} else {
    echo 'Client side rendering';
}
?>
            </h4>
            <h2>User agent detected</h2>
            <h4><?php echo $_SERVER["HTTP_USER_AGENT"] ?></h4>
            <h2>Accept language detected</h2>
            <h4><?php echo $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?></h4>
        </article>
<?php
$i = 0;
foreach (Database::getDbList() as $name => $database) {
    ?>
        <article class="frontpage">
            <h2>Check if Calibre database path is not an URL</h2>
            <h4>
            <?php
                if (!preg_match('#^http#', $database)) {
                    echo $name . ' OK';
                } else {
                    echo $name . ' Calibre path has to be local (no URL allowed)';
                }
    ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file exists and is readable</h2>
            <h4>
            <?php
    if (is_readable(Database::getDbFileName($i))) {
        echo $name . ' OK';
    } else {
        echo $name . ' File ' . Database::getDbFileName($i) . ' not found,
Please check
<ul>
<li>Value of $config[\'calibre_directory\'] in config/local.php <strong>(Does it end with a \'/\'?)</strong></li>
<li>Value of <a href="https://php.net/manual/en/ini.core.php#ini.open-basedir">open_basedir</a> in your php.ini</li>
<li>The access rights of the Calibre Database</li>
<li>Synology users please read <a href="https://github.com/seblucas/cops/wiki/Howto---Synology">this</a></li>
<li>Note that hosting your Calibre Library in /home is almost impossible due to access rights restriction</li>
</ul>';
    }
    ?>
            </h4>
        </article>
    <?php if (is_readable(Database::getDbFileName($i))) { ?>
        <article class="frontpage">
            <h2>Check if Calibre database file can be opened with PHP</h2>
            <h4>
            <?php
    try {
        $db = new PDO('sqlite:' . Database::getDbFileName($i));
        echo $name . ' OK';
    } catch (Exception $e) {
        echo $name . ' If the file is readable, check your php configuration. Exception detail : ' . $e;
    }
        ?>
            </h4>
        </article>
        <article class="frontpage">
            <h2>Check if Calibre database file contains at least some of the needed tables</h2>
            <h4>
            <?php
        try {
            $db = new PDO('sqlite:' . Database::getDbFileName($i));
            $count = $db->query('select count(*) FROM sqlite_master WHERE type="table" AND name in ("books", "authors", "tags", "series")')->fetchColumn();
            if ($count == 4) {
                echo $name . ' OK';
            } else {
                echo $name . ' Not all Calibre tables were found. Are you sure you\'re using the correct database.';
            }
        } catch (Exception $e) {
            echo $name . ' If the file is readable, check your php configuration. Exception detail : ' . $e;
        }
        ?>
            </h4>
        </article>
        <?php if ($full) { ?>
        <article class="frontpage">
            <h2>Check if all Calibre books are found</h2>
            <h4>
            <?php
            echo "This option has been disabled by default - see Admin if you are sure you want to do this...";
            ?>
            </h4>
        </article>
<?php
        }
    }
    $i++;
}
?>
    </section>
    <footer></footer>
</div>
</body>
</html>
