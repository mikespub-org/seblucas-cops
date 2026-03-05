# Middleware Component

The `Middleware` namespace provides a layer for processing requests before they reach the final `Handler`. This is used for cross-cutting concerns like authentication.

The `Framework` contains the dispatch logic that processes the `Request` through a stack of middleware. Each middleware can:
1.  Perform an action on the request.
2.  Delegate control to the next middleware.
3.  Return a `Response` directly to short-circuit the process.

## Components

### AuthMiddleware
Handles user authentication (basic auth or session). It can return an error response on failure.

### AdminMiddleware
Protects administrative handlers by verifying that the authenticated user has admin privileges.

## Architecture Fit

1.  **Routing**: The `Router` matches a request and updates the `RequestContext`.
2.  **Dispatch**: The `Framework` passes the `Request` through the middleware stack.
3.  **Handling**: If all middleware passes, the `Request` is passed to the designated `Handler`.