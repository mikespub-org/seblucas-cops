<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Output\Response;

/**
 * Handle calres:// resources for Calibre notes
 * URL format: index.php/calres/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 */
class CalResHandler extends BaseHandler
{
    public const HANDLER = "calres";
    public const PREFIX = "/calres";
    public const PARAMLIST = ["db", "alg", "digest"];

    public static function getRoutes()
    {
        return [
            "calres" => ["/calres/{db:\d+}/{alg}/{digest}"],
        ];
    }

    public function handle($request)
    {
        $database = $request->getId('db');
        $alg = $request->get('alg');
        $digest = $request->get('digest');

        $hash = $alg . ':' . $digest;

        // create empty file response to start with!?
        $response = new FileResponse();

        $result = Resource::sendImageResource($hash, $response, null, intval($database));
        if (is_null($result)) {
            return Response::notFound($request);
        }
        return $result;
    }
}
