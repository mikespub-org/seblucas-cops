<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\JsonRenderer;

/**
 * Handle JSON ajax requests
 * URL format: getJSON.php?page={page}&...
 */
class JsonHandler extends BaseHandler
{
    public function handle($request)
    {
        header('Content-Type:application/json;charset=utf-8');

        echo json_encode(JsonRenderer::getJson($request));
    }
}
