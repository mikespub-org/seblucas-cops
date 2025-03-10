<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Calibre\Format;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use GraphQL\GraphQL;
use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\AST;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Executor;
use GraphQL\Error\DebugFlag;
use Exception;
use JsonException;

/**
 * Summary of GraphQLExecutor
 */
class GraphQLExecutor
{
    public const DEFINITION_FILE = 'resources/schema.graphql';
    public const SCHEMA_CACHE_FILE = 'resources/cache.graphql.php';
    public const DEBUG = DebugFlag::INCLUDE_DEBUG_MESSAGE;

    /**
     * Summary of runQuery
     * @param Request $request
     * @param ?class-string $handler
     * @return array<string, mixed>
     */
    public function runQuery($request, $handler = null)
    {
        $input = json_decode((string) $request->content(), true);
        // set request handler for navlink
        $handler ??= RestApiHandler::class;
        $request->set(Route::HANDLER_PARAM, $handler);

        $schema = $this->getSchema();

        $queryString = $input['query'];
        $rootValue = 'query';
        // @see https://github.com/webonyx/graphql-php/blob/master/examples/02-schema-definition-language/graphql.php
        // use $rootValue to resolve query fields
        //$rootValue = $this->getFieldResolvers($request);
        $context = new RequestContext($request);
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

        return $result->toArray(self::DEBUG);
    }

    /**
     * Summary of getSchema
     * @phpstan-type TypeConfigDecorator callable(): array<string, mixed>
     * @param bool $refresh
     * @return \GraphQL\Type\Schema
     */
    public function getSchema($refresh = false)
    {
        $fieldResolvers = $this->mapTypeFieldResolvers();
        $typeResolvers = $this->mapTypeResolvers();

        $typeConfigDecorator = function (array $typeConfig, TypeDefinitionNode $typeDefinitionNode) use ($fieldResolvers, $typeResolvers) {
            $name = $typeConfig['name'];
            // ... add missing options to $typeConfig based on type $name
            if (empty($typeConfig['resolveField']) && !empty($fieldResolvers[$name])) {
                $typeConfig['resolveField'] = $fieldResolvers[$name]();
            }
            if (empty($typeConfig['resolveType']) && !empty($typeResolvers[$name])) {
                $typeConfig['resolveType'] = $typeResolvers[$name]();
            }
            return $typeConfig;
        };

        $cacheFile = dirname(__DIR__, 2) . '/' . self::SCHEMA_CACHE_FILE;
        if ($refresh || !file_exists($cacheFile)) {
            $schemaFile = dirname(__DIR__, 2) . '/' . self::DEFINITION_FILE;
            $contents = file_get_contents($schemaFile);
            $document = Parser::parse($contents);
            file_put_contents($cacheFile, "<?php\nreturn " . var_export(AST::toArray($document), true) . ";\n");
        } else {
            $document = AST::fromArray(require $cacheFile); // fromArray() is a lazy operation as well
        }
        //$schema = BuildSchema::build($contents);
        $schema = BuildSchema::build($document, $typeConfigDecorator);

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
            'Data' => $this->getDataFieldResolver(...),
            'Note' => $this->getNoteFieldResolver(...),
            //'Resource' => $this->getResourceFieldResolver(...),
        ];
    }

    /**
     * Summary of getQueryFieldResolver
     * @return callable
     */
    public function getQueryFieldResolver()
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) {
            /** @var RequestContext $context */
            $request = $context->getRequest();
            $fieldName = $info->fieldName;
            $result = self::getQueryField($fieldName, $args, $request);
            if ($result !== false) {
                return $result;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getEntryFieldResolver
     * @return callable
     */
    public function getEntryFieldResolver()
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) {
            /** @var RequestContext $context */
            $request = $context->getRequest();
            $fieldName = $info->fieldName;
            $result = self::getEntryField($fieldName, $objectValue, $args, $request);
            if ($result !== false) {
                return $result;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getEntryBookFieldResolver
     * @return callable
     */
    public function getEntryBookFieldResolver()
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) {
            //$request = $context->getRequest();
            $fieldName = $info->fieldName;
            // coming from Data
            if ($objectValue instanceof Book) {
                $objectValue = $objectValue->getEntry();
            }
            $result = self::getEntryBookField($fieldName, $objectValue);
            if ($result !== false) {
                return $result;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getDataFieldResolver
     * @return callable
     */
    public function getDataFieldResolver(): callable
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) {
            //$request = $context->getRequest();
            $fieldName = $info->fieldName;
            $result = self::getDataField($fieldName, $objectValue);
            if ($result !== false) {
                return $result;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of getNoteFieldResolver
     * @return callable
     */
    public function getNoteFieldResolver()
    {
        $resolver = static function ($objectValue, array $args, $context, ResolveInfo $info) {
            //$request = $context->getRequest();
            $fieldName = $info->fieldName;
            $result = self::getNoteField($fieldName, $objectValue);
            if ($result !== false) {
                return $result;
            }
            return Executor::defaultFieldResolver($objectValue, $args, $context, $info);
        };
        return $resolver;
    }

    /**
     * Summary of mapTypeResolvers
     * @return array<string, callable>
     */
    public function mapTypeResolvers()
    {
        return [
            'Node' => $this->getNodeTypeResolver(...),
            'SearchResult' => $this->getNodeTypeResolver(...),
        ];
    }

    /**
     * Summary of getNodeTypeResolver
     * @return callable
     */
    public function getNodeTypeResolver()
    {
        $resolver = static function ($objectValue, $context, ResolveInfo $info) {
            if ($objectValue instanceof EntryBook) {
                return 'EntryBook';
            }
            if ($objectValue instanceof Data) {
                return 'Data';
            }
            if ($objectValue instanceof Note) {
                return 'Note';
            }
            return 'Entry';
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
     * Summary of getQueryField
     * @param string $fieldName
     * @param array<mixed> $args
     * @param Request $request
     * @return mixed
     */
    public static function getQueryField($fieldName, $args, $request)
    {
        $handler = $request->getHandler();
        switch ($fieldName) {
            case 'books':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $booklist = new BookList($current, null, $numberPerPage);
                [$entryArray, $totalNumber] = $booklist->getAllBooks($n);
                return $entryArray;
            case 'book':
                $book = Book::getBookById($args['id'], $request->database());
                if (is_null($book)) {
                    return $book;
                }
                $book->setHandler($handler);
                return $book->getEntry();
            case 'datas':
                $book = Book::getBookById($args['bookId'], $request->database());
                if (is_null($book)) {
                    return $book;
                }
                $book->setHandler($handler);
                return $book->getDatas();
            case 'data':
                $book = Book::getBookByDataId($args['id'], $request->database());
                if (is_null($book)) {
                    return $book;
                }
                $book->setHandler($handler);
                $data = $book->datas[0];
                return $data;
            case 'authors':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Author::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'author':
                $instance = Author::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'formats':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Format::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'format':
                $instance = Format::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'identifiers':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Identifier::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'identifier':
                $instance = Identifier::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'languages':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Language::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'language':
                $instance = Language::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'publishers':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Publisher::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'publisher':
                $instance = Publisher::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'ratings':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Rating::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'rating':
                $instance = Rating::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'series':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Serie::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'serie':
                $instance = Serie::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'tags':
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $baselist = new BaseList(Tag::class, $current, null, $numberPerPage);
                $entryArray = $baselist->getRequestEntries($n);
                return $entryArray;
            case 'tag':
                $instance = Tag::getInstanceById($args['id'], $request->database());
                $instance->setHandler($handler);
                return $instance->getEntry();
            case 'nodelist':
                // @todo add other requested fields on demand
                $result = [];
                foreach ($args['idlist'] as $id) {
                    try {
                        $result[] = self::getNode((string) $id, $request);
                    } catch (Exception $e) {
                        // see https://github.com/webonyx/graphql-php/issues/374 or
                        // see https://github.com/webonyx/graphql-php/issues/432
                        $result[] = $e;
                    }
                }
                return $result;
            case 'node':
                // @todo add other requested fields on demand
                return self::getNode((string) ($args['id'] ?? ''), $request);
            case 'search':
                return self::getSearch($args, $request);
            default:
                return false;
        }
    }

    /**
     * Summary of getEntryField
     * @param string $fieldName
     * @param Entry $entry
     * @param array<mixed> $args
     * @param Request $request
     * @return mixed
     */
    public static function getEntryField($fieldName, $entry, $args, $request)
    {
        switch ($fieldName) {
            case 'books':
                // get books for parent instance(s)
                $instance = $entry->instance;
                [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
                $booklist = new BookList($current, null, $numberPerPage);
                [$entryArray, $totalNumber] = $booklist->getBooksByInstance($instance, $n);
                return $entryArray;
            case 'navlink':
                return $entry->getNavLink();
            case 'thumbnail':
                return $entry->getThumbnail();
            case 'note':
                // get note for parent instance(s)
                $instance = $entry->instance;
                $note = $instance?->getNote();
                if (!empty($note)) {
                    // @todo parse doc here?
                    return $note;
                }
                return null;
            default:
                return false;
        }
    }

    /**
     * Summary of getEntryBookField
     * @param string $fieldName
     * @param EntryBook $entry
     * @return mixed
     */
    public static function getEntryBookField($fieldName, $entry)
    {
        /** @var Book $book */
        $book = $entry->book;
        switch ($fieldName) {
            //case 'path':
            //    return $book->path;
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
            case 'formats':
                $formats = $book->getFormats();
                $entryArray = [];
                foreach ($formats as $format) {
                    array_push($entryArray, $format->getEntry());
                }
                return $entryArray;
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
            case 'navlink':
                return $book->getUri();
            case 'thumbnail':
                return $entry->getThumbnail();
            case 'cover':
                return $entry->getImage();
            default:
                return false;
        }
    }

    /**
     * Summary of getDataField
     * @param string $fieldName
     * @param Data $data
     * @return mixed
     */
    public static function getDataField($fieldName, $data)
    {
        switch ($fieldName) {
            case 'size':
                $path = $data->getLocalPath();
                return file_exists($path) ? filesize($path) : null;
            case 'mtime':
                $path = $data->getLocalPath();
                return file_exists($path) ? date(DATE_ATOM, filemtime($path)) : null;
            case 'navlink':
                return $data->getHtmlLink();
            default:
                return false;
        }
    }

    /**
     * Summary of getNoteField
     * @param string $fieldName
     * @param Note $note
     * @return mixed
     */
    public static function getNoteField($fieldName, $note)
    {
        switch ($fieldName) {
            case 'type':
                return $note->colname;
            case 'content':
                return $note->doc;
            case 'size':
                return strlen($note->doc);
            case 'mtime':
                return date(DATE_ATOM, (int) $note->mtime);
            case 'navlink':
                return $note->getUri();
            case 'resources':
                return $note->getResources();
            default:
                return false;
        }
    }

    /**
     * Summary of getNode
     * @param string $globalId
     * @param Request $request
     * @return mixed
     */
    public static function getNode($globalId, $request)
    {
        [$db, $type, $id] = self::fromGlobalIdentier($globalId);
        if (empty($type) || empty($id)) {
            return null;
        }
        // handle $db if different from $request->database()
        $current = clone $request;
        if ($db !== $current->database()) {
            $current->set('db', $db);
        }
        // books => book, authors => author etc.
        $fieldName = substr($type, 0, -1);
        // @todo add other requested fields on demand
        $entry = self::getQueryField($fieldName, ['id' => $id], $current);
        if (!empty($entry)) {
            return $entry;
        }
        $result = null;
        //$result = array_merge($result, (array) $entry);
        if (in_array($type, ['books'])) {
            $result = ['__typename' => 'EntryBook', 'id' => $globalId];
        } else {
            $result = ['__typename' => 'Entry', 'id' => $globalId];
        }
        return $result;
    }

    /**
     * Summary of fromGlobalIdentier
     * @param string $globalId
     * @return array<mixed>
     */
    public static function fromGlobalIdentier($globalId)
    {
        // format: /{type}/{id} or /{db}/{type}/{id} e.g. /books/17 or /1/books/17
        $globalId = trim($globalId, '/');
        if (empty($globalId) || !str_contains($globalId, '/')) {
            return [null, null, null];
        }
        [$db, $type, $id] = explode('/', $globalId . '//');
        if (!is_numeric($db) && $id === '') {
            $id = $type;
            $type = $db;
            $db = null;
        } else {
            $db = intval($db);
        }
        // basic validation of global identifier parts
        try {
            if (empty(Database::getDbFileName($db))) {
                return [null, null, null];
            }
        } catch (Exception) {
            throw new Exception('Invalid global identifier db');
        }
        if (!in_array($type, ['authors', 'books', 'datas', 'formats', 'identifiers', 'languagues', 'publishers', 'ratings', 'series', 'tags'])) {
            throw new Exception('Invalid global identifier type');
        }
        if ($id === '') {
            throw new Exception('Invalid global identifier id');
        }
        return [$db, $type, $id];
    }

    /**
     * Summary of getSearch
     * @param array<mixed> $args
     * @param Request $request
     * @return mixed
     */
    public static function getSearch($args, $request)
    {
        [$numberPerPage, $n, $current] = self::parseListArgs($args, $request);
        $query = $args['query'] ?? '';
        if (empty($query)) {
            return null;
        }
        $current->set('query', $query);
        $scope = $args['scope'] ?? '';
        if (!empty($scope)) {
            $current->set('scope', $scope);
        }
        // use typeahead format here
        $current->set('search', 1);
        $pageId = PageId::OPENSEARCH_QUERY;
        $page = PageId::getPage($pageId, $current);
        // ...
        return $page->entryArray;
    }
}
