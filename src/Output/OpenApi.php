<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

/**
 * Basic OpenApi definition for REST API
 */
class OpenApi
{
    /**
     * Summary of getDefinition
     * @return array<string, mixed>
     */
    public function getDefinition()
    {
        $openapi = $this->initOpenApi();
        $openapi["servers"] = $this->getServers();
        $openapi["components"] = $this->getComponents();
        $openapi["paths"] = $this->getPaths();
        return $openapi;
    }

    /**
     * Summary of initOpenApi
     * @return array<mixed>
     */
    public function initOpenApi()
    {
        return [
            "openapi" => "3.0.3",
            "info" => [
                "title" => "COPS REST API",
                "version" => Config::VERSION,
            ],
        ];
    }

    /**
     * Summary of getServers
     * @return array<mixed>
     */
    public function getServers()
    {
        $server = [
            "url" => RestApiHandler::getBaseUrl(),
            "description" => "COPS REST API Endpoint",
        ];
        return [
            $server,
        ];
    }

    /**
     * Summary of getComponents
     * @return array<mixed>
     */
    public function getComponents()
    {
        return [
            "securitySchemes" => $this->getSecuritySchemes(),
            "parameters" => $this->getParameters(),
        ];
    }

    /**
     * Summary of getSecuritySchemes
     * @return array<mixed>
     */
    public function getSecuritySchemes()
    {
        return [
            "ApiKeyAuth" => [
                "type" => "apiKey",
                "in" => "header",
                "name" => "X-API-KEY",
            ],
            "BasicAuth" => [
                "type" => "http",
                "scheme" => "basic",
            ],
        ];
    }

    /**
     * Summary of getParameters
     * @return array<mixed>
     */
    public function getParameters()
    {
        return [
            "dbParam" => [
                "name" => "db",
                "in" => "query",
                "required" => false,
                "schema" => [
                    "type" => "integer",
                    "minimum" => 0,
                ],
                //"example" => 0,
            ],
        ];
    }

    /**
     * Summary of getPaths
     * @return array<mixed>
     */
    public function getPaths()
    {
        $paths = [];
        foreach (Route::getRoutes() as $name => $route) {
            [$path, $pathItem] = $this->getPathItem($name, $route);
            if (empty($path)) {
                continue;
            }
            $paths[$path] = $pathItem;
        }
        return $paths;
    }

    /**
     * Summary of getPathItem
     * @param string $name
     * @param array<mixed> $route
     * @return array{0: string, 1: array<mixed>}
     */
    public function getPathItem($name, $route)
    {
        [$path, $queryParams, $methods, $options] = $route;
        $pathItem = [];
        if (str_starts_with($path, RestApi::PREFIX . '/')) {
            $path = substr($path, strlen(RestApi::PREFIX));
            if (empty($path)) {
                return [$path, $pathItem];
            }
        }
        [$path, $params, $queryString] = $this->getPathParams($path, $queryParams);

        // @todo clean up queryString with handler_param here
        $handler = 'default';
        if (!empty($queryParams[Route::HANDLER_PARAM])) {
            $handler = $queryParams[Route::HANDLER_PARAM]::HANDLER;
        }
        if ($handler == "restapi") {
            $queryString = substr($path, 1) . ' api';
        } elseif (!empty($queryParams[Route::HANDLER_PARAM])) {
            if (!empty($queryString)) {
                $queryString = $handler . ' handler with ' . trim($queryString, '&');
            } else {
                $queryString = $handler . ' handler';
            }
        } else {
            $queryString = 'page handler with ' . trim($queryString, '&');
        }
        if (empty($methods)) {
            $methods = ['GET'];
        }
        foreach ($methods as $method) {
            $method = strtolower($method);
            $operationId = $method . '_' . $name;
            $operation = $this->getOperation($queryString, $operationId, $handler);
            if (!empty($params)) {
                $operation["parameters"] = $params;
            }
            $this->addOperationSecurity($operation, $path, $queryParams);
            $pathItem[$method] = $operation;
        }
        return [$path, $pathItem];
    }

    /**
     * Summary of getPathParams
     * @param string $path
     * @param array<mixed> $queryParams
     * @return array<mixed>
     */
    public function getPathParams($path, $queryParams)
    {
        $params = [];
        $queryString = Route::getQueryString($queryParams);
        $found = [];
        // support custom pattern for route placeholders - see nikic/fast-route
        if (preg_match_all("~\{(\w+(|:[^}]+))\}~", $path, $found)) {
            foreach ($found[1] as $param) {
                $schema = [
                    "type" => "string",
                ];
                if (str_contains($param, ':')) {
                    [$param, $pattern] = explode(':', $param);
                    $schema["pattern"] = '^' . $pattern . '$';
                    $path = str_replace(':' . $pattern, '', $path);
                }
                if ($param !== 'ignore') {
                    $queryString .= "&{$param}=" . '{' . $param . '}';
                }
                array_push($params, [
                    "name" => $param,
                    "in" => "path",
                    "required" => true,
                    "schema" => $schema,
                    //"example" => $example,
                ]);
            }
        }
        $extra = $this->getExtraParams($path, $queryParams);
        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }
        return [$path, $params, $queryString];
    }

    /**
     * Summary of getExtraParams
     * @param string $path
     * @param array<mixed> $queryParams
     * @return array<mixed>
     */
    public function getExtraParams($path, $queryParams)
    {
        $params = [];
        if ($path == "/databases/{db}") {
            array_push($params, [
                "name" => "type",
                "in" => "query",
                "schema" => [
                    "type" => "string",
                    "enum" => ["table", "view"],
                ],
                "example" => "table",
            ]);
        }
        if (
            !str_starts_with($path, "/databases") &&
            !in_array($path, ["/openapi", "/routes", "/handlers", "/about"]) &&
            (empty($queryParams[Route::HANDLER_PARAM]) ||
            in_array($queryParams[Route::HANDLER_PARAM]::HANDLER, ['restapi', 'zipper']))
        ) {
            array_push($params, [
                '$ref' => "#/components/parameters/dbParam",
            ]);
        }
        return $params;
    }

    /**
     * Summary of getOperation
     * @param string $queryString
     * @param string $operationId
     * @param string $handler
     * @return array<mixed>
     */
    public function getOperation($queryString, $operationId, $handler)
    {
        return [
            "summary" => "Route to " . $queryString,
            "operationId" => $operationId,
            "tags" => [
                $handler,
            ],
            "responses" => [
                "200" => [
                    "description" => "Result of " . $queryString,
                ],
            ],
        ];
    }

    /**
     * Summary of addOperationSecurity
     * @param array<mixed> $operation
     * @param string $path
     * @param array<mixed> $queryParams
     * @return void
     */
    public function addOperationSecurity(&$operation, $path, $queryParams)
    {
        if ($path == "/databases/{db}/{name}") {
            $operation["summary"] .= " - with api key";
            $operation["security"] = [
                ["ApiKeyAuth" => []],
            ];
        }
        if ($path == "/user" || $path == "/user/details") {
            $operation["summary"] .= " - with basic authentication";
            $operation["security"] = [
                ["BasicAuth" => []],
            ];
        }
        if (!empty($queryParams[Route::HANDLER_PARAM])) {
            $handler = $queryParams[Route::HANDLER_PARAM]::HANDLER;
            if (!in_array($handler, ["restapi", "check", "phpunit"])) {
                $operation["summary"] .= " - with api key";
                $operation["security"] = [
                    ["ApiKeyAuth" => []],
                ];
            }
        }
    }
}
