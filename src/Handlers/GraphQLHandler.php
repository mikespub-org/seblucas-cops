<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Context;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format as OutputFormat;
use SebLucas\Cops\Output\GraphQLExecutor;
use SebLucas\Cops\Output\Response;

/**
 * Summary of GraphQLHandler
 */
class GraphQLHandler extends BaseHandler
{
    public const HANDLER = "graphql";
    public const PREFIX = "/graphql";

    public static int $numberPerPage = 100;

    public static function getRoutes()
    {
        return [
            "graphql" => ["/graphql", [], ["GET", "POST"]],
        ];
    }

    /**
     * Summary of handle
     * @param Request $request
     * @return Response
     */
    public function handle($request)
    {
        if ($request->method() !== 'POST') {
            return $this->renderPlayground();
        }

        // update request context if needed here - see tests
        $this->getContext()->setRequest($request);
        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');
        // @todo override pagination
        Config::set('max_item_per_page', self::$numberPerPage);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($this->getContext());

        $response = new Response(Response::MIME_TYPE_JSON);
        return $response->setContent(json_encode($result));
    }

    /**
     * Render GraphQL Playground
     * @return Response
     */
    public function renderPlayground()
    {
        $data = ['link' => self::link()];
        $template = dirname(__DIR__, 2) . '/templates/graphql.html';

        $response = new Response(Response::MIME_TYPE_HTML);
        return $response->setContent(OutputFormat::template($data, $template));
    }
}
