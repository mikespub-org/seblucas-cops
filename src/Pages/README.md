# Pages Component

The `Pages` namespace contains the logic for retrieving and organizing data for specific views in the application (e.g., a list of authors, book details, recent additions).

## Components

### PageId
The `PageId` class acts as a registry and factory for all available pages. It defines constants for page identifiers (e.g., `ALL_AUTHORS`, `BOOK_DETAIL`) and provides the `getPage()` method to instantiate the correct `Page` class based on the request.

### BasePage
All specific pages extend `BasePage`. This abstract class provides common functionality for:
-   Accessing the `Calibre` database.
-   Handling pagination.
-   Filtering results (e.g., by tag or letter).

### Specific Pages
Each view has a corresponding class (e.g., `PageAllAuthors`, `PageBookDetail`) that implements the `getEntries()` method. This method queries the `Calibre` database and returns a list of `Entry` or `EntryBook` objects from the `Model` namespace.

## Architecture Fit

1.  **Routing**: A `PageHandler` (like `HtmlHandler` or `JsonHandler`) determines which page is requested.
2.  **Instantiation**: The handler calls `PageId::getPage()` to get the specific `Page` object.
3.  **Data Retrieval**: The handler calls `getEntries()` on the `Page` object.
4.  **Rendering**: The resulting entries are passed to a `Renderer` (in `Output`) to generate the final HTML, JSON, or OPDS output.