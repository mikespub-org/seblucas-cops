# Input Component

The `Input` namespace manages the application configuration and incoming HTTP request, abstracting them from the global state.

## Components

### Config
The `Config` class loads and provides access to configuration settings. It merges defaults from `config/default.php` with local overrides from `config/local.php` (and user-specific files). It serves as the single source of truth for configuration throughout the application.

### Request
The `Request` class encapsulates the HTTP request information (query parameters, post data, headers, cookies, server variables). It is instantiated by the Framework at the start of the lifecycle.

There is also a `ProxyRequest` subclass that handles trusted proxy headers (like `X-Forwarded-For`) to correctly determine the client IP and base URL when running behind a reverse proxy.

### RequestContext
The `RequestContext` class holds the context for the current request execution. It bundles the `Request` object with other essential services like the `Router` and the matched route information. This allows passing a consistent context to `Handlers` and `Middleware`, facilitating integration with different frameworks (via Adapters).

## Architecture Fit

1.  **Initialization**: The `Framework` (or Adapter) initializes `Config` and creates a `Request` object from PHP globals.
2.  **Context**: A `RequestContext` is created to wrap the `Request`.
3.  **Routing**: The `Router` uses the `Request` to find a matching route, which is updated in the `RequestContext`.
4.  **Execution**: The `Request` is passed through `Middleware` to the appropriate `Handler`.