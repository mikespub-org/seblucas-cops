<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Routing\RouteLoader;
use SebLucas\Cops\Routing\Routing;

require_once dirname(__DIR__) . "/config/test.php";
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\UriGenerator;
use Exception;

#[RequiresMethod('\Symfony\Component\Routing\Router', '__construct')]
class RoutingTest extends TestCase
{
    /** @var Routing */
    protected static $routing;

    public static function setUpBeforeClass(): void
    {
        Config::set("calibre_directory", __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$routing = new Routing();
    }

    public function testRouteLoader(): void
    {
        $loader = new RouteLoader();
        $resource = null;
        $routes = $loader->load($resource);

        $expected = Route::count();
        $this->assertCount($expected, $routes);
    }

    public function testGetRouter(): void
    {
        $routing = new Routing();
        $router = $routing->getRouter();
        // force cache generation
        $matcher = $router->getMatcher();
        $generator = $router->getGenerator();

        $expected = Route::count();
        $this->assertCount($expected, $router->getRouteCollection());
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public static function linkProvider()
    {
        return RouteTest::linkProvider();
    }

    /**
     * @param mixed $queryUrl
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("linkProvider")]
    public function testMatchLink($queryUrl, $routeUrl, $route, $params)
    {
        //echo '["' . $queryUrl . '", "' . $routeUrl . '", "' . $route . '", ' . json_encode($params) . "],\n";
        $query = parse_url((string) $routeUrl, PHP_URL_QUERY);
        // parse_url() does not decode URL-encoded characters in the path
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        $result = self::$routing->match($path, $method);
        if (!isset($result)) {
            $this->assertNull($routeUrl);
            return;
        }
        $extra = [];
        if (!empty($query)) {
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        // @todo handle ignore
        //unset($result["ignore"]);

        $expected = $route;
        $this->assertEquals($expected, $result[Route::ROUTE_PARAM]);
        unset($result[Route::ROUTE_PARAM]);
        unset($result[Route::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Route::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            //unset($result[Route::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        $this->assertEquals($expected, $result);

        // @todo check/add default route for each handler?
        $endpoint = parse_url((string) $queryUrl, PHP_URL_PATH);
        $flipped = array_flip(Config::ENDPOINT);
        $handler = "html";
        if (!empty($flipped[$endpoint])) {
            $handler = $flipped[$endpoint];
        }
        if ($handler == "html") {
            return;
        }
        $path = "/$handler";
        try {
            $result = self::$routing->match($path);
        } catch (Exception) {
            // echo "Endpoint handler not supported: $handler\n";
        }
    }

    /**
     * @param mixed $queryUrl
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("linkProvider")]
    public function testGenerateLink($queryUrl, $routeUrl, $route, $params)
    {
        // @todo handle ignore
        // @todo handle restapi/route...
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for opds-path + use _route in params to generate path
        if ($route == "opds-path" && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
            $prefix = "/opds";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = UriGenerator::slugify($params['title']);
        }
        //if (!empty($params['file'])) {
        //    $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        //}
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $prefix . $result);
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        return RouteTest::routeProvider();
    }

    /**
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testMatchRoute($routeUrl, $route, $params)
    {
        //echo '["' . $routeUrl . '", "' . $route . '", ' . json_encode($params) . "],\n";
        $query = parse_url((string) $routeUrl, PHP_URL_QUERY);
        // parse_url() does not decode URL-encoded characters in the path
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        $result = self::$routing->match($path, $method);
        if (!isset($result)) {
            $this->assertNull($routeUrl);
            return;
        }
        if (!isset($routeUrl)) {
            $expected = [];
            $this->assertEquals($expected, $result);
            return;
        }
        $extra = [];
        if (!empty($query)) {
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        $expected = $route;
        $this->assertEquals($expected, $result[Route::ROUTE_PARAM]);
        unset($result[Route::ROUTE_PARAM]);
        unset($result[Route::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Route::ROUTE_PARAM]);
        unset($expected[Route::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            unset($result[Route::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testGenerateRoute($routeUrl, $route, $params)
    {
        // skip feed-page-id routes for Routing::generate() - use feed-path route with default page handler
        if (str_starts_with($route, "feed-page")) {
            $this->markTestSkipped("Skip feed-page routes here");
        }
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for feed-path + use _route in params to generate path
        if ($route == "feed-path" && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
            $prefix = "/feed";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = UriGenerator::slugify($params['title']);
        }
        //if (!empty($params['file'])) {
        //    $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        //}
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $prefix . $result);
    }

    /**
     * @return array<mixed>
     */
    protected function getRoutes()
    {
        return [
            // 'name' => ['path', [ defaults ], [ requirements ], [ methods ], [ options ], [ ... ]],
            'index' => ['/index', ['page' => 'index']],
            'authors-letter-id' => ['/authors/letter/{id}', ['page' => '2']],
            'authors-letter' => ['/authors/letter', ['page' => '1', 'letter' => 1]],
            'authors-id-title' => ['/authors/{id}/{title}', ['page' => '3'], ['id' => '\d+']],
            'authors-id' => ['/authors/{id}', ['page' => '3'], ['id' => '\d+']],
            'authors' => ['/authors', ['page' => '1']],
            'books-letter-id' => ['/books/letter/{id}', ['page' => '5'], ['id' => '\w']],
            'books-letter' => ['/books/letter', ['page' => '4', 'letter' => 1]],
            'books-year-id' => ['/books/year/{id}', ['page' => '50'], ['id' => '\d+']],
            'books-year' => ['/books/year', ['page' => '4', 'year' => 1]],
            'books-id-author-title' => ['/books/{id}/{author}/{title}', ['page' => '13'], ['id' => '\d+']],
            'books-id' => ['/books/{id}', ['page' => '13'], ['id' => '\d+']],
            'books' => ['/books', ['page' => '4']],
            'series-id-title' => ['/series/{id}/{title}', ['page' => '7'], ['id' => '\d+']],
            'series-id' => ['/series/{id}', ['page' => '7'], ['id' => '\d+']],
            'series' => ['/series', ['page' => '6']],
            'typeahead' => ['/typeahead', ['page' => '9', 'search' => 1]],
            'search-query-scope' => ['/search/{query}/{scope}', ['page' => '9']],
            'search-query' => ['/search/{query}', ['page' => '9']],
            'search' => ['/search', ['page' => '8']],
            'recent' => ['/recent', ['page' => '10']],
            'tags-id-title' => ['/tags/{id}/{title}', ['page' => '12'], ['id' => '\d+']],
            'tags-id' => ['/tags/{id}', ['page' => '12'], ['id' => '\d+']],
            'tags' => ['/tags', ['page' => '11']],
            'custom-custom-id' => ['/custom/{custom}/{id}', ['page' => '15'], ['custom' => '\d+']],
            'custom-custom' => ['/custom/{custom}', ['page' => '14'], ['custom' => '\d+']],
            'about' => ['/about', ['page' => '16']],
            'languages-id-title' => ['/languages/{id}/{title}', ['page' => '18'], ['id' => '\d+']],
            'languages-id' => ['/languages/{id}', ['page' => '18'], ['id' => '\d+']],
            'languages' => ['/languages', ['page' => '17']],
            'customize' => ['/customize', ['page' => '19']],
            'publishers-id-title' => ['/publishers/{id}/{title}', ['page' => '21'], ['id' => '\d+']],
            'publishers-id' => ['/publishers/{id}', ['page' => '21'], ['id' => '\d+']],
            'publishers' => ['/publishers', ['page' => '20']],
            'ratings-id-title' => ['/ratings/{id}/{title}', ['page' => '23'], ['id' => '\d+']],
            'ratings-id' => ['/ratings/{id}', ['page' => '23'], ['id' => '\d+']],
            'ratings' => ['/ratings', ['page' => '22']],
            'identifiers-id-title' => ['/identifiers/{id}/{title}', ['page' => '42'], ['id' => '\w+']],
            'identifiers-id' => ['/identifiers/{id}', ['page' => '42'], ['id' => '\w+']],
            'identifiers' => ['/identifiers', ['page' => '41']],
            'formats-id' => ['/formats/{id}', ['page' => '52'], ['id' => '\w+']],
            'formats' => ['/formats', ['page' => '51']],
            'libraries' => ['/libraries', ['page' => '43']],
            'feed-page-id' => ['/feed/{page}/{id}', ['_handler' => 'feed']],
            'feed-page' => ['/feed/{page}', ['_handler' => 'feed']],
            'feed' => ['/feed', ['_handler' => 'feed']],
            'files-db-id-file' => ['/files/{db}/{id}/{file}', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+', 'file' => '.+']],
            'thumbs-db-id-thumb' => ['/thumbs/{db}/{id}/{thumb}.jpg', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+']],
            'covers-db-id' => ['/covers/{db}/{id}.jpg', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+']],
            'inline-db-data-ignore.type' => ['/inline/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1], ['db' => '\d+', 'data' => '\d+']],
            'fetch-db-data-ignore.type' => ['/fetch/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch'], ['db' => '\d+', 'data' => '\d+']],
            'view-data-db-ignore.type' => ['/view/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1]],
            'view-data-ignore.type' => ['/view/{data}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1]],
            'download-data-db-ignore.type' => ['/download/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch']],
            'download-data-ignore.type' => ['/download/{data}/{ignore}.{type}', ['_handler' => 'fetch']],
            'read-db-data-title' => ['/read/{db}/{data}/{title}', ['_handler' => 'read'], ['db' => '\d+', 'data' => '\d+']],
            'read-db-data' => ['/read/{db}/{data}', ['_handler' => 'read'], ['db' => '\d+', 'data' => '\d+']],
            'epubfs-db-data-comp' => ['/epubfs/{db}/{data}/{comp}', ['_handler' => 'epubfs'], ['db' => '\d+', 'data' => '\d+', 'comp' => '.+']],
            'restapi-custom' => ['/restapi/custom', ['_handler' => 'restapi', '_resource' => 'CustomColumnType']],
            'restapi-databases-db-name' => ['/restapi/databases/{db}/{name}', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-databases-db' => ['/restapi/databases/{db}', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-databases' => ['/restapi/databases', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-openapi' => ['/restapi/openapi', ['_handler' => 'restapi', '_resource' => 'openapi']],
            'restapi-routes' => ['/restapi/routes', ['_handler' => 'restapi', '_resource' => 'route']],
            'restapi-groups' => ['/restapi/groups', ['_handler' => 'restapi', '_resource' => 'group']],
            'restapi-notes-type-id-title' => ['/restapi/notes/{type}/{id}/{title}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes-type-id' => ['/restapi/notes/{type}/{id}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes-type' => ['/restapi/notes/{type}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes' => ['/restapi/notes', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-preferences-key' => ['/restapi/preferences/{key}', ['_handler' => 'restapi', '_resource' => 'Preference']],
            'restapi-preferences' => ['/restapi/preferences', ['_handler' => 'restapi', '_resource' => 'Preference']],
            'restapi-annotations-bookId-id' => ['/restapi/annotations/{bookId}/{id}', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-annotations-bookId' => ['/restapi/annotations/{bookId}', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-annotations' => ['/restapi/annotations', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-metadata-bookId-element-name' => ['/restapi/metadata/{bookId}/{element}/{name}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-metadata-bookId-element' => ['/restapi/metadata/{bookId}/{element}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-metadata-bookId' => ['/restapi/metadata/{bookId}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-user-details' => ['/restapi/user/details', ['_handler' => 'restapi', '_resource' => 'User']],
            'restapi-user' => ['/restapi/user', ['_handler' => 'restapi', '_resource' => 'User']],
            'restapi-route' => ['/restapi/{route}', ['_handler' => 'restapi'], ['route' => '.*']],
            'check-more' => ['/check/{more}', ['_handler' => 'check'], ['more' => '.*']],
            'check' => ['/check', ['_handler' => 'check']],
            'opds-page-id' => ['/opds/{page}/{id}', ['_handler' => 'opds']],
            'opds-page' => ['/opds/{page}', ['_handler' => 'opds']],
            'opds' => ['/opds', ['_handler' => 'opds']],
            'loader-action-dbNum-authorId-urlPath' => ['/loader/{action}/{dbNum}/{authorId}/{urlPath}', ['_handler' => 'loader'], ['dbNum' => '\d+', 'authorId' => '\w+', 'urlPath' => '.*']],
            'loader-action-dbNum-authorId' => ['/loader/{action}/{dbNum}/{authorId}', ['_handler' => 'loader'], ['dbNum' => '\d+', 'authorId' => '\w*']],
            'loader-action-dbNum' => ['/loader/{action}/{dbNum}', ['_handler' => 'loader'], ['dbNum' => '\d+']],
            'loader-action-' => ['/loader/{action}/', ['_handler' => 'loader']],
            'loader-action' => ['/loader/{action}', ['_handler' => 'loader']],
            'loader' => ['/loader', ['_handler' => 'loader']],
            'zipper-page-id-type' => ['/zipper/{page}/{id}/{type}.zip', ['_handler' => 'zipper']],
            'zipper-page-type' => ['/zipper/{page}/{type}.zip', ['_handler' => 'zipper']],
            'calres-db-alg-digest' => ['/calres/{db}/{alg}/{digest}', ['_handler' => 'calres'], ['db' => '\d+']],
            'zipfs-db-data-comp' => ['/zipfs/{db}/{data}/{comp}', ['_handler' => 'zipfs'], ['db' => '\d+', 'data' => '\d+', 'comp' => '.+']],
            // @todo 'name' => ['path', [ defaults ], [ requirements ], [ methods ], [ options ], [ ... ]],
            'mail' => ['/mail', ['_handler' => 'mail'], [], ['POST']],
            'graphql' => ['/graphql', ['_handler' => 'graphql'], [], ['GET', 'POST']],
            'tables' => ['/tables', ['_handler' => 'tables']],
            // @todo handle cors options
            'cors' => ['/{route}', ['_handler' => 'TODO'], ['route' => '.*'], ['OPTIONS']],
            // @todo handle unicode!?
            //'authors-letter-id' => ['/authors/letter/{id}', ['page' => '2'], ['id' => '\w'], ['GET], ['utf8' => true]],
        ];
        //$routes->add('article_show', '/articles/{_locale}/search.{_format}')
        //->controller([ArticleController::class, 'search'])
        //->methods(['GET', 'HEAD'])
        //->locale('en')
        //->format('html')
        //->defaults([
        //    'page'  => 1,
        //    'title' => 'Hello world!',
        //])
        //->requirements(['page' => '\d+'])
        //->schemes(['https'])
        //->utf8(true)
        //->options([...])
        // this is added to the beginning of all imported route URLs
        //->prefix('/blog')
        // this is added to the beginning of all imported route names
        //->namePrefix('blog_')

        //$routes->alias('new_route_name', 'original_route_name');
        //$routes->import(...)
        //$framework->router()->defaultUri('https://example.org/my/path/');
    }
}
