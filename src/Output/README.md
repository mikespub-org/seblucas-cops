# Output Component

The `Output` namespace is responsible for formatting data and generating the final **Response** that is sent to the client.

## Components

### Response
The **Response** class is the base object representing an HTTP response, built on `Symfony\Component\HttpFoundation\Response`. It encapsulates headers and content to be sent. Subclasses like `FileResponse` or `ImageResponse` handle specific response types.

### Renderers
Renderers are responsible for transforming data from the `Pages` and `Calibre` components into a specific format.
-   **`HtmlRenderer`**: Renders HTML pages for browsers using server-side templating (doT or Twig).
-   **`JsonRenderer`**: Serializes data into JSON, primarily for client-side rendering in modern browsers.
-   **`OpdsRenderer`**: Generates OPDS 1.2 XML feeds for e-reader devices.

### API Output Classes
-   **`GraphQLExecutor`**: Executes GraphQL queries against COPS data using webonyx/graphql-php. Loads schema from `resources/schema.graphql` and resolves queries for books, authors, series and other Calibre entities.
-   **`RestApiProvider`**: Routes /restapi-prefixed requests to JSON output. Maps URL paths to data methods (databases, books, authors, openapi, routes, handlers, etc.) using an OpenAPI spec from `resources/openapi.json`.

### Other Output Classes
-   **`EPubReader`**: Manages the user interface for the online e-book readers (e.g., Monocle).
-   **`ComicReader`**: Comic book reader for CBZ archives. Extracts cover images and metadata from ComicInfo.xml, lists page images, and renders an online comic reader interface.
-   **`Mail`**: Handles the "Send to Kindle" feature by formatting and sending books via email.

## Architecture Fit

1.  **Handling**: A `Handler` processes the `Request` and gathers the necessary data.
2.  **Rendering**: The `Handler` instantiates and uses an appropriate `Renderer` or other `Output` class to create a **Response** object.
3.  **Sending**: The `Handler` returns the **Response** to the `Framework`, which then calls the `send()` method to deliver the final output to the client.