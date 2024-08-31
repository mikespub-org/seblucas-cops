<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Handlers\GraphQLHandler;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use GraphQL\Type\Schema;

class GraphQLHandlerTest extends TestCase
{
    private static string $handler = 'graphql';
    private static int $numberPerPage;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$numberPerPage = Config::get('max_item_per_page');
        // No pagination
        Config::set('max_item_per_page', -1);
    }

    public static function tearDownAfterClass(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        Config::set('max_item_per_page', self::$numberPerPage);
    }

    /**
     * Summary of testRenderPlayground
     * @runInSeparateProcess
     * @return void
     */
    public function testRenderPlayground(): void
    {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $request = Request::build();

        ob_start();
        $handler = Framework::getHandler(self::$handler);
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = '<title>GraphQL Playground</title>';
        $this->assertEquals(0, count($headers));
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * Summary of testHandleRequest
     * @runInSeparateProcess
     * @return void
     */
    public function testHandleRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = "POST";
        $request = Request::build();
        $request->content = $this->getBasicQuery();

        ob_start();
        $handler = Framework::getHandler(self::$handler);
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $result = json_decode($output, true);

        $expected = 7;
        $this->assertEquals(0, count($headers));
        $this->assertCount($expected, $result['data']['authors']);
    }

    public function testGetSchema(): void
    {
        $request = Request::build();
        $handler = new GraphQLHandler();
        $schema = $handler->getSchema($request);

        $expected = Schema::class;
        $this->assertEquals($expected, $schema::class);

        $schema->assertValid();
        $expected = [];
        $errors = $schema->validate();
        $this->assertEquals($expected, $errors);
    }

    public function testRunQuery(): void
    {
        $request = Request::build();
        $request->content = $this->getBasicQuery();

        $handler = new GraphQLHandler();
        $result = $handler->runQuery($request);

        $expected = 7;
        $this->assertCount($expected, $result['data']['authors']);
    }

    /**
     * Summary of getBasicQuery
     * @return bool|string
     */
    protected function getBasicQuery()
    {
        $query = '{
  authors {
    id
    title
  }
}';
        return json_encode(['query' => $query]);
    }

    /**
     * Summary of getQueryOperation
     * @param string $name
     * @param array<string, mixed> $vars
     * @return bool|string
     */
    protected function getQueryOperation($name, $vars)
    {
        $params = [
            'operationName' => $name,
            'variables' => $vars,
            'query' => $this->getQueryString(),
        ];
        return json_encode($params);
    }

    /**
     * Summary of getQueryString
     * @return string
     */
    protected function getQueryString()
    {
        return file_get_contents(__DIR__ . '/query.test.graphql');
    }

    /**
     * Summary of getAuthorQuery
     * @return bool|string
     */
    protected function getAuthorQuery()
    {
        return $this->getQueryOperation('getAuthor', ['id' => 1]);
    }

    public function testGetAuthor(): void
    {
        $request = Request::build();
        $request->content = $this->getAuthorQuery();

        $handler = new GraphQLHandler();
        $result = $handler->runQuery($request);

        $expected = "cops:authors:1";
        $this->assertEquals($expected, $result['data']['author']['id']);

        $expected = "Arthur Conan Doyle";
        $this->assertEquals($expected, $result['data']['author']['title']);

        $expected = 8;
        $this->assertCount($expected, $result['data']['author']['books']);

        $book = $result['data']['author']['books'][7];

        $expected = "urn:uuid:be99a102-8275-47a0-9bb5-7c341d6a7dda";
        $this->assertEquals($expected, $book['id']);

        $expected = "The Adventures of Sherlock Holmes";
        $this->assertEquals($expected, $book['title']);
    }
}
