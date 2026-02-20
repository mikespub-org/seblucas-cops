<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;

/**
 * Handle datatables
 */
class TableHandler extends BaseHandler
{
    public const HANDLER = "tables";
    public const PREFIX = "/tables";
    public const PARAMLIST = ["db", "name", "id"];

    public static string $template = "templates/tables.html";

    public static function getRoutes()
    {
        return [
            "tables-db-name" => ["/tables/{db:\d+}/{name:\w+}"],
            "tables-db" => ["/tables/{db:\d+}"],
            "tables" => ["/tables"],
        ];
    }

    public function handle($request)
    {
        $db = $request->getId('db');
        $name = $request->get('name', null, '/^\w+$/');

        if (is_null($db) && !Database::isMultipleDatabaseEnabled()) {
            $db = 0;
        }

        if (!is_null($db) && !is_null($name)) {
            return $this->showTable($db, $name, $request);
        }

        if (!is_null($db)) {
            return $this->showDbTables($db, $request);
        }

        return $this->showDatabases($request);
    }

    /**
     * Summary of showTable
     * @param int $db
     * @param string $name
     * @param Request $request
     * @return Response
     */
    private function showTable(int $db, string $name, $request): Response
    {
        $data = [];
        $data['title'] = "Table $name";
        $homeLink = self::route('tables');
        $dbLink = self::route('tables-db', ['db' => $db]);
        $dbName = Database::getDbName($db) ?: '0';
        $data['breadcrumb'] = '<li class="breadcrumb-item"><a href="' . $homeLink . '">Databases</a></li>';
        $data['breadcrumb'] .= '<li class="breadcrumb-item"><a href="' . $dbLink . '">' . htmlspecialchars((string) $dbName) . '</a></li>';
        if ($request->get('id')) {
            $tableLink = self::route('tables-db-name', ['db' => $db, 'name' => $name]);
            $data['breadcrumb'] .= '<li class="breadcrumb-item active" aria-current="page"><a href="' . $tableLink . '">' . $name . '</a></li>';
        } else {
            $data['breadcrumb'] .= '<li class="breadcrumb-item active" aria-current="page">' . $name . '</li>';
        }
        $params = ['db' => $db, 'name' => $name];
        $data['ajax_url'] = RestApiHandler::resource(Database::class, $params);

        $columns = Database::getTableInfo($db, $name);
        $links = [
            'authors' => 'books_authors_link.author',
            //'languages' => 'books_languages_link.lang_code',
            'publishers' => 'books_publishers_link.publisher',
            'ratings' => 'books_ratings_link.rating',
            'series' => 'books_series_link.series',
            'tags' => 'books_tags_link.tag',
        ];
        if (array_key_exists($name, $links)) {
            $columns[] = [
                'name' => 'books',
            ];
        }
        $data['thead'] = '<tr>';
        $data['columns'] = [];
        $foreignKeys = [];
        foreach ($columns as $column) {
            $data['thead'] .= '<th>' . htmlspecialchars($column['name']) . '</th>';
            $colDef = ['data' => $column['name']];
            // Disable searching for BLOB columns
            if (isset($column['type']) && str_contains(strtolower($column['type']), 'blob')) {
                $colDef['searchable'] = false;
            }
            $data['columns'][] = $colDef;

            $refTable = $this->getReferencedTable($column['name']);
            if ($refTable && $refTable != $name) {
                $foreignKeys[$column['name']] = self::route('tables-db-name', ['db' => $db, 'name' => $refTable]);
            }
        }
        $data['thead'] .= '</tr>';
        $data['tfoot'] = $data['thead'];
        $data['tbody'] = ''; // Will be populated by datatables
        $data['json_columns'] = json_encode($data['columns']);
        // make sure we don't have an empty array, which causes problems in Javascript for 'sort'
        $data['foreign_keys'] = json_encode($foreignKeys, JSON_FORCE_OBJECT);
        $data['table'] = $name;
        $data['api_key'] = Config::get('api_key');

        $response = new Response(Response::MIME_TYPE_HTML);
        return $response->setContent(Format::template($data, self::$template));
    }

    private function getReferencedTable(string $colName): ?string
    {
        $map = [
            'book' => 'books',
            'author' => 'authors',
            'publisher' => 'publishers',
            'rating' => 'ratings',
            'series' => 'series',
            'tag' => 'tags',
            'language' => 'languages',
            'lang_code' => 'languages',
        ];
        return $map[$colName] ?? null;
    }

    /**
     * Summary of showDbTables
     * @param int $db
     * @param Request $request
     * @return Response
     */
    private function showDbTables(int $db, $request): Response
    {
        $data = ['link' => RestApiHandler::getBaseUrl()];
        $data['title'] = "Database " . Database::getDbName($db);
        $homeLink = self::route('tables');
        $dbName = Database::getDbName($db) ?: '0';
        $data['breadcrumb'] = '<li class="breadcrumb-item"><a href="' . $homeLink . '">Databases</a></li>';
        $data['breadcrumb'] .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars((string) $dbName) . '</li>';
        $data['ajax_url'] = '';
        $data['thead'] = '<tr><th>Table</th><th>Rows</th></tr>';
        $data['tbody'] = '';
        $tables = Database::getDbSchema($db, 'table');
        foreach ($tables as $table) {
            $tableName = $table['tbl_name'];
            if (str_contains($tableName, '_')) {
                continue;
            }
            if (in_array($tableName, ['preferences'])) {
                continue;
            }
            $count = Database::querySingle("SELECT COUNT(*) FROM {$tableName}", $db);
            $link = self::route('tables-db-name', ['db' => $db, 'name' => $tableName]);
            $data['tbody'] .= '<tr class="clickable-row" data-href="' . $link . '"><td><a href="' . $link . '">' . $tableName . '</a></td><td>' . $count . '</td></tr>';
        }
        $data['tfoot'] = $data['thead'];
        $data['json_columns'] = 'null';
        $data['foreign_keys'] = '{}';
        $data['table'] = '';
        $data['api_key'] = '';

        $response = new Response(Response::MIME_TYPE_HTML);
        return $response->setContent(Format::template($data, self::$template));
    }

    /**
     * Summary of showDatabases
     * @param Request $request
     * @return Response
     */
    private function showDatabases($request): Response
    {
        $data = ['link' => RestApiHandler::getBaseUrl()];
        $data['title'] = "Databases";
        $data['breadcrumb'] = '<li class="breadcrumb-item active" aria-current="page">Databases</li>';
        $data['ajax_url'] = '';
        $data['thead'] = '<tr><th class="text-start">Database</th></tr>';
        $data['tbody'] = '';
        $id = 0;
        foreach (Database::getDbNameList() as $key) {
            if (empty($key)) {
                $key = '0';
            }
            $link = self::route('tables-db', ['db' => $id]);
            $data['tbody'] .= '<tr class="clickable-row" data-href="' . $link . '"><td class="text-start"><a href="' . $link . '">' . $key . '</a></td></tr>';
            $id++;
        }
        $data['tfoot'] = $data['thead'];
        $data['json_columns'] = 'null';
        $data['foreign_keys'] = '{}';
        $data['table'] = '';
        $data['api_key'] = '';

        $response = new Response(Response::MIME_TYPE_HTML);
        return $response->setContent(Format::template($data, self::$template));
    }
}
