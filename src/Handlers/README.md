# Handlers Component

The `Handlers` namespace contains the core controllers of the application. Each handler is responsible for processing a specific type of request and generating a `Response`.

## Components

Handlers are the final step in the request processing chain after `Middleware`. They receive the `Request` and perform the main business logic.

-   **Page Handlers** (`HtmlHandler`, `JsonHandler`, `FeedHandler`): These handlers are responsible for the main browsing interface. They work with the `Pages` and `Calibre` components to retrieve data and use an `Output` renderer (`HtmlRenderer`, `JsonRenderer`, `OpdsRenderer`) to format the response for web browsers or OPDS clients.
-   **File Handlers** (`FetchHandler`, `ReadHandler`, `ZipFsHandler`): These handlers manage the delivery of book files, either for direct download, for use in the online e-readers, or for filesystem-like access to zipped book contents.
-   **API Handlers** (`RestApiHandler`, `GraphqlHandler`): These provide machine-readable interfaces for external tools.
-   **Admin Handlers** (`AdminHandler`, `TableHandler`): These provide access to administrative functions and direct database table viewing/editing.

## Architecture Fit

1.  **Routing**: The `Router` matches an incoming `Request` to a specific Handler class and registers the route information in the `RequestContext`.
2.  **Middleware**: The `RequestContext` is passed through the `Middleware` stack for tasks like authentication.
3.  **Execution**: If all middleware passes, the `Framework` instantiates the matched `Handler` and calls its `handle()` method, passing the `Request`.
4.  **Output**: The `Handler` performs its logic and typically uses a component from the `Output` namespace to create a `Response` object.
5.  **Response**: The `Response` is returned up the chain to the `Framework`, which sends it to the client.