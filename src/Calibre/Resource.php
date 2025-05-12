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
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Handlers\CalResHandler;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Routing\UriGenerator;

class Resource
{
    use HasRouteTrait;

    public const ROUTE_DETAIL = "calres";
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

    public string $hash;
    public string $name;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->hash = $post->hash;
        $this->name = $post->name;
        $this->databaseId = $database;
        $this->setHandler(CalResHandler::class);
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $database = $this->databaseId ?? 0;
        [$alg, $digest] = explode(':', $this->hash);
        $params['db'] = $database;
        $params['alg'] = $alg;
        $params['digest'] = $digest;
        return $this->getRoute(self::ROUTE_DETAIL, $params);
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
        // create link to resource with dummy alg & digest
        $params = [];
        $params['db'] = $database;
        $params['alg'] = 'ALG';
        $params['digest'] = 'DIGEST';
        $baseurl = UriGenerator::cached(CalResHandler::route(self::ROUTE_DETAIL, $params));
        // remove dummy alg & digest
        $baseurl = str_replace(['/ALG', '/DIGEST'], [], $baseurl);
        return str_replace(self::RESOURCE_URL_SCHEME . '://', $baseurl . '/', $doc);
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
     * @param FileResponse $response
     * @param ?string $name
     * @param ?int $database
     * @return FileResponse|null
     */
    public static function sendImageResource($hash, $response, $name = null, $database = null)
    {
        $path = self::getResourcePath($hash, $database);
        if (empty($path)) {
            return null;
        }
        if (empty($name)) {
            $content = file_get_contents($path . '.metadata');
            $info = json_decode($content, true);
            $name = $info['name'];
        }
        $ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        if (!array_key_exists($ext, self::IMAGE_EXTENSIONS)) {
            return null;
        }
        $mime = self::IMAGE_EXTENSIONS[$ext];

        $response->setHeaders($mime, 0);
        return $response->setFile($path, true);
    }
}
