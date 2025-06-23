<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Handlers\GraphQLHandler;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\GraphQLExecutor;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ListOfType;

#[RequiresMethod('\GraphQL\GraphQL', 'executeQuery')]
class GraphQLHandlerTest extends TestCase
{
    /** @var class-string */
    private static $handler = GraphQLHandler::class;
    private static int $numberPerPage;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$numberPerPage = Config::get('max_item_per_page');
        // @todo override pagination
        Config::set('max_item_per_page', GraphQLHandler::$numberPerPage);
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('max_item_per_page', self::$numberPerPage);
    }

    public function testRenderPlayground(): void
    {
        $server = ['REQUEST_METHOD' => "GET"];
        $request = Request::build([], null, $server);

        ob_start();
        $handler = Framework::createHandler(self::$handler);
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = '<title>GraphiQL 4 with React 19 and GraphiQL Explorer</title>';
        $this->assertEquals(0, count($headers));
        $this->assertStringContainsString($expected, $output);
    }

    public function testHandleRequest(): void
    {
        $server = ['REQUEST_METHOD' => "POST"];
        $request = Request::build([], null, $server);
        $request->content = $this->getBasicQuery();

        ob_start();
        $handler = Framework::createHandler(self::$handler);
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
        $executor = new GraphQLExecutor();
        $schema = $executor->getSchema();

        $expected = Schema::class;
        $this->assertEquals($expected, $schema::class);

        $schema->assertValid();
        $expected = [];
        $errors = $schema->validate();
        $this->assertEquals($expected, $errors);

        $queryType = $schema->getQueryType();
        $expected = 27;
        $this->assertCount($expected, $queryType->getFieldNames());
    }

    public function testRunQuery(): void
    {
        $request = Request::build();
        $request->content = $this->getBasicQuery();
        $context = new RequestContext($request);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($context);

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
     * @param string $id
     * @return bool|string
     */
    protected function getAuthorQuery($id)
    {
        return $this->getQueryOperation('getAuthor', ['id' => $id]);
    }

    public function testGetAuthor(): void
    {
        $id = 3;
        $request = Request::build();
        $request->content = $this->getAuthorQuery((string) $id);
        $context = new RequestContext($request);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($context);

        $resultFile = __DIR__ . '/graphql/getAuthor.' . $id . '.result.json';
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, Format::json($result));
        }
        $expected = json_decode(file_get_contents($resultFile), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Summary of getAuthorsQuery
     * @param array<mixed> $where
     * @return bool|string
     */
    protected function getAuthorsQuery($where)
    {
        return $this->getQueryOperation('getAuthors', [
            'limit' => 5,
            'offset' => 1,
            // apply filter here
            'where' => json_encode($where),
            'order' => "count",
        ]);
    }

    public function testGetAuthors(): void
    {
        // filter by language id 2 = French
        $where = ['l' => 2];
        $request = Request::build();
        $request->content = $this->getAuthorsQuery($where);
        $context = new RequestContext($request);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($context);

        $resultFile = __DIR__ . '/graphql/getAuthors.l.' . $where['l'] . '.result.json';
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, Format::json($result));
        }
        $expected = json_decode(file_get_contents($resultFile), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Summary of getNodeProvider
     * @return array<mixed>
     */
    public static function getNodeProvider()
    {
        $data = [];
        $idlist = ['/authors/3', '/books/17', '/datas/20', '/oops/42'];
        foreach ($idlist as $id) {
            $resultFile = __DIR__ . '/graphql/node' . str_replace('/', '.', $id) . '.result.json';
            if (file_exists($resultFile)) {
                $result = json_decode(file_get_contents($resultFile), true);
                $data[] = [$id, $result['data']];
            } else {
                $data[] = [$id, ['node' => []]];
            }
        }
        return $data;
    }

    /**
     * Summary of getNodeQuery
     * @param string $id
     * @return bool|string
     */
    protected function getNodeQuery($id)
    {
        return $this->getQueryOperation('getNode', [
            'id' => $id,
        ]);
    }

    /**
     * Summary of testGetNode
     * @param string $id
     * @param array<mixed> $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getNodeProvider')]
    public function testGetNode($id, $expected): void
    {
        $request = Request::build();
        $request->content = $this->getNodeQuery($id);
        $context = new RequestContext($request);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($context);

        $resultFile = __DIR__ . '/graphql/getNode' . str_replace('/', '.', $id) . '.result.json';
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, Format::json($result));
        }
        $expected = json_decode(file_get_contents($resultFile), true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Summary of getQueryFields
     * @return array<mixed>
     */
    public static function getQueryFields()
    {
        if (!class_exists('\GraphQL\GraphQL')) {
            // dummy data to satisfy phpunit when graphql is not installed
            return [
                [],
            ];
        }
        $executor = new GraphQLExecutor();
        $schema = $executor->getSchema();

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
                } elseif ($name == 'nodelist') {
                    $vars = ['idlist' => ['/authors/3', '/books/17', '/datas/20', '/oops/42']];
                    $query = 'query ' . $operation . "(\$idlist: [ID!]!) {\n";
                    $query .= '  ' . $name . "(idlist: \$idlist) {\n";
                } elseif ($name == 'search') {
                    $vars = ['query' => 'car'];
                    $query = 'query ' . $operation . "(\$query: String!, \$scope: String) {\n";
                    $query .= '  ' . $name . "(query: \$query, scope: \$scope) {\n";
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
                    case 'Node':
                        $query .= "    __typename\n";
                        $query .= "    id\n";
                        break;
                    case 'SearchResult':
                        $query .= "    __typename\n";
                        $query .= "    ... on Entry {\n";
                        $query .= "      id\n";
                        $query .= "      title\n";
                        $query .= "      content\n";
                        $query .= "      numberOfElement\n";
                        $query .= "    }\n";
                        $query .= "    ... on EntryBook {\n";
                        $query .= "      id\n";
                        $query .= "      title\n";
                        $query .= "      authors {\n";
                        $query .= "        id\n";
                        $query .= "        title\n";
                        $query .= "      }\n";
                        $query .= "    }\n";
                        break;
                }
                $query .= "  }\n";
                $query .= '}';
            } else {
                $type = $field->getType()->toString();
                $vars = match ($name) {
                    'book' => ['id' => 17],
                    'publisher' => ['id' => 2],
                    'identifier' => ['id' => 'isbn'],
                    'format' => ['id' => 'EPUB'],
                    'node' => ['id' => '/books/17'],
                    default => ['id' => 1],
                };
                if ($name == 'node') {
                    $query = 'query ' . $operation . "(\$id: ID!) {\n";
                } else {
                    $query = 'query ' . $operation . "(\$id: ID) {\n";
                }
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
                    case 'Node':
                        $query .= "    __typename\n";
                        $query .= "    id\n";
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
            $graphqlFile = str_replace('.json', '.graphql', $queryFile);
            file_put_contents($graphqlFile, $query);
            $contents = Format::json($params);
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
        $context = new RequestContext($request);

        $executor = new GraphQLExecutor();
        $result = $executor->runQuery($context);
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, Format::json($result));
        }
        $expected = json_decode(file_get_contents($resultFile), true);
        $this->assertEquals($expected, $result);
    }
}
