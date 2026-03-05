# Routing Component

The `Routing` namespace handles the mapping of HTTP requests to specific `Handlers` and the generation of URLs.

## Components

### Router
The `Router` class matches the incoming `Request` to a registered route. It collects route definitions from `Handlers` (via `getRoutes()`) and delegates the matching logic to an adapter.

### UriGenerator
The `UriGenerator` handles the creation of URLs. It uses route names and parameters to generate absolute or relative URLs, abstracting the underlying URL structure.

### Adapters
The system uses adapters to integrate different routing libraries. COPS currently uses `symfony/routing` by default, with support for `nikic/fast-route` as a legacy option.

## Architecture Fit

1.  **Definition**: `Handlers` define their routes, which are registered with the `Router`.
2.  **Matching**: The `Framework` calls the `Router` to match the current `Request`.
3.  **Context**: Matched parameters are stored in the `RequestContext`.
4.  **Generation**: The `UriGenerator` is used throughout the application to build links.