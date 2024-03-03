<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * Resources:
 *     hash = alg:digest
 *     name = imagefile.jpg
 *     path = ./.calnotes/resources/di/alg-digest
 *     meta = ./.calnotes/resources/di/alg-digest.metadata
 *     link = calres://alg/digest?placement=uuid4
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

class Resource
{
    // https://github.com/kovidgoyal/calibre/blob/master/src/calibre/gui2/dialogs/edit_category_notes.py
    public const IMAGE_EXTENSIONS = [
        'png' => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
    ];
    public const RESOURCE_URL_SCHEME = 'calres';
    public static string $endpoint = Config::ENDPOINT["calres"];
    public string $hash;
    public string $name;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param object $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->hash = $post->hash;
        $this->name = $post->name;
        $this->databaseId = $database;
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $database = $this->databaseId ?? 0;
        return '/' . $database . '/' . str_replace(':', '/', $this->hash);
    }

    /**
     * Summary of fixResourceLinks
     * @param string $doc
     * @param ?int $database
     * @return string
     */
    public static function fixResourceLinks($doc, $database = null)
    {
        $database ??= 0;
        $baseurl = Route::url(static::$endpoint);
        return str_replace(static::RESOURCE_URL_SCHEME . '://', $baseurl . '/' . $database . '/', $doc);
    }

    /**
     * Summary of getResourcePath
     * @param string $hash
     * @param ?int $database
     * @return string|null
     */
    public static function getResourcePath($hash, $database = null)
    {
        [$alg, $digest] = explode(':', $hash);
        $resourcesDir = dirname(Database::getDbFileName($database)) . '/' . Database::NOTES_DIR_NAME . '/resources';
        $resourcePath = $resourcesDir . '/' . substr($digest, 0, 2) . '/' . $alg . '-' . $digest;
        if (file_exists($resourcePath)) {
            return $resourcePath;
        }
        return null;
    }

    /**
     * Summary of sendImageResource
     * @param string $hash
     * @param ?string $name
     * @param ?int $database
     * @return bool
     */
    public static function sendImageResource($hash, $name = null, $database = null)
    {
        $path = static::getResourcePath($hash, $database);
        if (empty($path)) {
            return false;
        }
        if (empty($name)) {
            $content = file_get_contents($path . '.metadata');
            $info = json_decode($content, true);
            $name = $info['name'];
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, static::IMAGE_EXTENSIONS)) {
            return false;
        }
        $mime = static::IMAGE_EXTENSIONS[$ext];

        $expires = 60 * 60 * 24 * 14;
        header('Pragma: public');
        header('Cache-Control: max-age=' . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        header('Content-Type: ' . $mime);

        readfile($path);
        return true;
    }
}
