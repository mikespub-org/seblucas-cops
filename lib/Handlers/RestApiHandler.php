<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\RestApi;
use Exception;

/**
 * Handle REST API
 * URL format: restapi.php{/route}?db={db} etc.
 */
class RestApiHandler extends BaseHandler
{
    public function handle($request)
    {
        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');

        $path = $request->path();
        if (empty($path)) {
            header('Content-Type:text/html;charset=utf-8');

            $data = ['link' => Route::url(Config::ENDPOINT["restapi"]) . '/openapi'];
            $template = dirname(__DIR__, 2) . '/templates/restapi.html';
            echo Format::template($data, $template);
            return;
        }

        $apiHandler = new RestApi($request);

        header('Content-Type:application/json;charset=utf-8');

        try {
            echo $apiHandler->getOutput();
        } catch (Exception $e) {
            echo json_encode(["Exception" => $e->getMessage()]);
        }
    }
}
