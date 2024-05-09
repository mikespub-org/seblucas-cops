<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Resource;

/**
 * Handle calres:// resources for Calibre notes
 * URL format: calres.php/{db}/{alg}/{digest} with {hash} = {alg}:{digest}
 */
class CalResHandler extends BaseHandler
{
    public function handle($request)
    {
        // don't try to match path params here
        $path = $request->path();
        if (empty($path) || $path == '/') {
            // this will call exit()
            $request->notFound();
        }
        $path = substr($path, 1);
        if (!preg_match('/^\d+\/\w+\/\w+$/', $path)) {
            // this will call exit()
            $request->notFound();
        }
        [$database, $alg, $digest] = explode('/', $path);
        $hash = $alg . ':' . $digest;
        if (!Resource::sendImageResource($hash, null, intval($database))) {
            $request->notFound();
        }
    }
}
