<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use JsonException;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Context;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;
use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Executor;
use GraphQL\Error\DebugFlag;

/**
 * Summary of GraphQLHandler
 */
class GraphQLHandler extends BaseHandler
{
    public const HANDLER = "graphql";
    public const DEBUG = DebugFlag::INCLUDE_DEBUG_MESSAGE;

    public static function getRoutes()
    {
        return [
            "/graphql" => [static::PARAM => static::HANDLER],
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

        // override splitting authors and books by first letter here?
        Config::set('author_split_first_letter', '0');
        Config::set('titles_split_first_letter', '0');
        //Config::set('titles_split_publication_year', '0');
        // @todo override pagination
        Config::set('max_item_per_page', 100);

        $result = $this->runQuery($request);

        $response = new Response('application/json;charset=utf-8');
        return $response->setContent(json_encode($result));
    }

    /**
     * Summary of runQuery
     * @param Request $request
     * @return array<string, mixed>
     */
    public function runQuery($request)
    {
        $input = json_decode((string) $request->content(), true);

        $schema = $this->getSchema($request);

        $queryString = $input['query'];
        $rootValue = 'query';
        // @see https://github.com/webonyx/graphql-php/blob/master/examples/02-schema-definition-language/graphql.php
        // use $rootValue to resolve query fields
        //$rootValue = $this->getFieldResolvers($request);
        $context = new Context($request);
        $variableValues = $input['variables'] ?? null;
        $operationName = $input['operationName'] ?? null;
        //$fieldResolver = $this->getFieldResolver($request, $resolvers);
        //$validationRules = [];

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            $rootValue,
            $context,
            $variableValues,
            $operationName,
            $fieldResolver = null,
            $validationRules = null
        );
        //$result = array_merge($result->toArray(), ['input' => $input]);

        return $result->toArray(static::DEBUG);
    }

    /**
     * Summary of getSchema
     * @phpstan-import-type TypeConfigDecorator from \GraphQL\Utils\ASTDefinitionBuilder
     * @param Request $request
     * @return \GraphQL\Type\Schema
     */
    public function getSchema($request)
    {
        $resolvers = $this->mapTypeFieldResolvers();

        $typeConfigDecorator = function (array $typeConfig, TypeDefinitionNode $typeDefinitionNode) use ($resolvers, $request) {
            $name = $typeConfig['name'];
            // ... add missing options to $typeConfig based on type $name
            if (empty($typeConfig['resolveField']) && !empty($resolvers[$name])) {
                $typeConfig['resolveField'] = $resolvers[$name]($request);
            }
            return $typeConfig;
        };

        $contents = file_get_contents(dirname(__DIR__, 2) . '/schema.graphql');
        //$schema = BuildSchema::build($contents);
        $schema = BuildSchema::build($contents, $typeConfigDecorator);

        return $schema;
    }

    /**
     * Summary of mapTypeFieldResolvers
     * @return array<string, callable>
     */
    public function mapTypeFieldResolvers()
    {
        return [
            'Query' => $this->getQueryFieldResolver(...),
            'Entry' => $this->getEntryFieldResolver(...),
            'EntryBook' => $this->getEntryBookFieldResolver(...),
        ];
    }

    /**
     * Summary of getQueryFieldResolver
     * @param Request $request
     * @return callable
     */
    public function getQueryFieldResolver($request)
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) use ($request) {
            $fieldName = $info->fieldName;
            switch ($fieldName) {
                case 'books':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $booklist = new BookList($current, null, $numberPerPage);
                    [$entryArray, $totalNumber] = $booklist->getAllBooks($n);
                    return $entryArray;
                case 'book':
                    $book = Book::getBookById($args['id'], $request->database());
                    if (is_null($book)) {
                        return $book;
                    }
                    $book->setHandler("index");
                    return $book->getEntry();
                case 'datas':
                    $book = Book::getBookById($args['bookId'], $request->database());
                    if (is_null($book)) {
                        return $book;
                    }
                    $book->setHandler("index");
                    return $book->getDatas();
                case 'data':
                    $book = Book::getBookByDataId($args['id'], $request->database());
                    if (is_null($book)) {
                        return $book;
                    }
                    $data = $book->datas[0];
                    return $data;
                case 'authors':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Author::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'author':
                    $instance = Author::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'identifiers':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Identifier::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'identifier':
                    $instance = Identifier::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'languages':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Language::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'language':
                    $instance = Language::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'publishers':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Publisher::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'publisher':
                    $instance = Publisher::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'ratings':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Rating::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'rating':
                    $instance = Rating::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'series':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Serie::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'serie':
                    $instance = Serie::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
                case 'tags':
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $baselist = new BaseList(Tag::class, $current, null, $numberPerPage);
                    $entryArray = $baselist->getRequestEntries($n);
                    return $entryArray;
                case 'tag':
                    $instance = Tag::getInstanceById($args['id'], $request->database());
                    $instance->setHandler("index");
                    return $instance->getEntry();
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getEntryFieldResolver
     * @param Request $request
     * @return callable
     */
    public function getEntryFieldResolver($request)
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) use ($request) {
            $fieldName = $info->fieldName;
            switch ($fieldName) {
                case 'books':
                    // @todo get books for parent instance(s)
                    $instance = $objectValue->instance;
                    [$numberPerPage, $n, $current] = static::parseListArgs($args, $request);
                    $booklist = new BookList($current, null, $numberPerPage);
                    [$entryArray, $totalNumber] = $booklist->getBooksByInstance($instance, $n);
                    return $entryArray;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getEntryBookFieldResolver
     * @param Request $request
     * @return callable
     */
    public function getEntryBookFieldResolver($request)
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) use ($request) {
            $fieldName = $info->fieldName;
            //if (is_object($objectValue) && isset($objectValue->{$fieldName})) {
            //    return $objectValue->{$fieldName};
            //}
            // coming from Data
            if ($objectValue instanceof Book) {
                $objectValue = $objectValue->getEntry();
            }
            /** @var Book $book */
            $book = $objectValue->book;
            switch ($fieldName) {
                case 'path':
                    return $book->path;
                case 'authors':
                    $authors = $book->getAuthors();
                    $entryArray = [];
                    foreach ($authors as $author) {
                        array_push($entryArray, $author->getEntry());
                    }
                    return $entryArray;
                case 'datas':
                    $datas = $book->getDatas();
                    return $datas;
                case 'identifiers':
                    $identifiers = $book->getIdentifiers();
                    $entryArray = [];
                    foreach ($identifiers as $identifier) {
                        array_push($entryArray, $identifier->getEntry());
                    }
                    return $entryArray;
                case 'languages':
                    $languages = $book->getLanguages();
                    return $languages;
                case 'publisher':
                    $publisher = $book->getPublisher();
                    return $publisher->getEntry();
                case 'rating':
                    $rating = $book->getRating();
                    return $rating;
                case 'serie':
                    $serie = $book->getSerie();
                    return $serie->getEntry();
                case 'tags':
                    $tags = $book->getTags();
                    $entryArray = [];
                    foreach ($tags as $tag) {
                        array_push($entryArray, $tag->getEntry());
                    }
                    return $entryArray;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of parseListArgs
     * @param array<string, mixed> $args
     * @param Request $request
     * @return array{0: ?int, 1: int, 2: Request}
     */
    public static function parseListArgs($args, $request)
    {
        if (empty($args)) {
            return [null, 1, $request];
        }
        // input = {"query":"...","variables":{"limit":5,"offset":0,"where":"{\"l\": 2}","order":"sort"},"operationName":"getAuthors"}
        $numberPerPage = null;
        if (!empty($args['limit']) && is_int($args['limit']) && $args['limit'] > 0 && $args['limit'] < 1001) {
            $numberPerPage = $args['limit'];
        }
        // offset only works by multiples of limit here, e.g. 0, 5, 10, ...
        $n = 1;
        if (!empty($args['offset']) && is_int($args['offset']) && $args['offset'] > 0) {
            $n = intval($args['offset'] / $numberPerPage) + 1;
        }
        // handle where and order by updating $request
        $current = clone $request;
        if (!empty($args['where'])) {
            try {
                $filterParams = json_decode($args['where'], true, 512, JSON_THROW_ON_ERROR);
                // see list of acceptable filter params in Filter.php
                $find = Filter::URL_PARAMS;
                $params = array_intersect_key($filterParams, $find);
                $params['db'] = $request->database();
                $current = Request::build($params, $request->getHandler());
            } catch (JsonException $e) {
                error_log('COPS: Invalid where argument ' . $args['where'] . ': ' . $e->getMessage());
            }
        }
        if (!empty($args['order']) && preg_match('/^\w+(\s+(asc|desc)|)$/i', $args['order'])) {
            $current->set('sort', $args['order']);
        }
        return [$numberPerPage, $n, $current];
    }

    /**
     * Render GraphQL Playground
     * @return Response
     */
    public function renderPlayground()
    {
        $data = ['link' => Route::link(static::HANDLER)];
        $template = dirname(__DIR__, 2) . '/templates/graphql.html';

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, $template));
    }
}
