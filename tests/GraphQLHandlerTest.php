<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Handlers\GraphQLHandler;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ListOfType;

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

    public function testRenderPlayground(): void
    {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $request = Request::build();

        ob_start();
        $handler = Framework::getHandler(self::$handler);
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = '<title>GraphiQL</title>';
        $this->assertEquals(0, count($headers));
        $this->assertStringContainsString($expected, $output);
    }

    public function testHandleRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = "POST";
        $request = Request::build();
        $request->content = $this->getBasicQuery();

        ob_start();
        $handler = Framework::getHandler(self::$handler);
        $response = $handler->handle($request);
        $response->send();
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

        $queryType = $schema->getQueryType();
        $expected = 24;
        $this->assertCount($expected, $queryType->getFieldNames());
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
        return file_get_contents(__DIR__ . '/graphql/test.query.graphql');
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

    /**
     * Summary of getQueryFields
     * @return array<mixed>
     */
    public static function getQueryFields()
    {
        $request = Request::build();
        $handler = new GraphQLHandler();
        $schema = $handler->getSchema($request);

        $data = [];
        $queryType = $schema->getQueryType();
        foreach ($queryType->getFieldNames() as $name) {
            $queryFile = __DIR__ . '/graphql/' . $name . '.query.json';
            $resultFile = __DIR__ . '/graphql/' . $name . '.result.json';
            if (file_exists($queryFile)) {
                array_push($data, [$name, $queryFile, $resultFile]);
                continue;
            }
            $operation = 'get' . ucfirst($name);
            $field = $queryType->getField($name);
            if ($field->getType() instanceof ListOfType) {
                $type = $field->getType()->getWrappedType()->toString();
                if ($name == 'datas') {
                    $vars = ['bookId' => 17];
                    $query = 'query ' . $operation . "(\$bookId: ID) {\n";
                    $query .= '  ' . $name . "(bookId: \$bookId) {\n";
                } else {
                    $vars = [];
                    $query = 'query ' . $operation . " {\n";
                    $query .= '  ' . $name . " {\n";
                }
                switch ($type) {
                    case 'Entry':
                        $query .= "    id\n";
                        $query .= "    title\n";
                        break;
                    case 'EntryBook':
                        $query .= "    id\n";
                        $query .= "    title\n";
                        break;
                    case 'Data':
                        $query .= "    id\n";
                        $query .= "    name\n";
                        $query .= "    format\n";
                        break;
                }
                $query .= "  }\n";
                $query .= '}';
            } else {
                $type = $field->getType()->toString();
                $vars = match ($name) {
                    'book' => ['id' => 17],
                    'publisher' => ['id' => 2],
                    default => ['id' => 1],
                };
                $query = 'query ' . $operation . "(\$id: ID) {\n";
                $query .= '  ' . $name . "(id: \$id) {\n";
                switch ($type) {
                    case 'Entry':
                        $query .= "    id\n";
                        $query .= "    title\n";
                        break;
                    case 'EntryBook':
                        $query .= "    id\n";
                        $query .= "    title\n";
                        break;
                    case 'Data':
                        $query .= "    id\n";
                        $query .= "    name\n";
                        $query .= "    format\n";
                        break;
                }
                $query .= "  }\n";
                $query .= '}';
            }
            $params = [
                'operationName' => $operation,
                'variables' => $vars,
                'query' => $query,
            ];
            file_put_contents(str_replace('.json', '.graphql', $queryFile), $query);
            $contents = json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($queryFile, $contents);
            array_push($data, [$name, $queryFile, $resultFile]);
        }
        return $data;
    }

    /**
     * Summary of testQueryFields
     * @param string $name
     * @param string $queryFile
     * @param string $resultFile
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getQueryFields')]
    public function testQueryFields($name, $queryFile, $resultFile): void
    {
        $this->assertTrue(file_exists($queryFile));

        $request = Request::build();
        $request->content = file_get_contents($queryFile);

        $handler = new GraphQLHandler();
        $result = $handler->runQuery($request);
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $expected = json_decode(file_get_contents($resultFile), true);
        $this->assertEquals($expected, $result);
    }
}
