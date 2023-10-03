<h2>Detected browser</h2>
<?php
require 'vendor/autoload.php';
print($_SERVER['HTTP_USER_AGENT'] . "\n\n");

use MatthiasMullie\Scrapbook\Psr16\SimpleCache;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use MatthiasMullie\Scrapbook\Adapters\Flysystem;
use Monolog\Logger;

$dir        = __DIR__ . "/vendor/browscap/browscap-php/resources";
$adapter    = new LocalFilesystemAdapter($dir);
$filesystem = new Filesystem($adapter);
$cache      = new SimpleCache(
    new Flysystem($filesystem)
);

$logger = new Logger('name');
$parser = new Browscap($cache, $logger);

$result = $parser->getBrowser($useragent);
print($result);
?>