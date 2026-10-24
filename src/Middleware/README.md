# Middleware Component

The `Middleware` namespace provides a layer for processing requests before they reach the final `Handler`. This is used for cross-cutting concerns like authentication.

The `Framework` contains the dispatch logic that processes the `Request` through a stack of middleware. Each middleware can:
1.  Perform an action on the request.
2.  Delegate control to the next middleware.
3.  Return a `Response` directly to short-circuit the process.

## Components

### BaseMiddleware
Abstract base class that all concrete middleware extends. Defines the `process($request, $handler)` contract.

### AuthMiddleware
Handles user authentication via HTTP Basic Auth or session-based form auth. It can return an error response on failure.

### AdminMiddleware
Protects administrative handlers by verifying that the authenticated user has admin privileges.

### ConnectMiddleware
Initializes session-based connection tracking when fetch protection is enabled.

### ProtectMiddleware
Blocks fetch operations when fetch protection is enabled unless the user has connected via web or feed first.

### TestMiddleware
Test middleware used for development. Sets request data and appends content to the response after handling.

## Architecture Fit

1.  **Routing**: The `Router` matches a request and updates the `RequestContext`.
2.  **Dispatch**: The `Framework` passes the `Request` through the middleware stack.
3.  **Handling**: If all middleware passes, the `Request` is passed to the designated `Handler`.