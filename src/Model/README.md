# Model Component

The `Model` namespace provides a standardized data structure for representing items in the catalog. This structure acts as a Data Transfer Object (DTO) layer, inspired by the OPDS 1.2 specification, to decouple the data source (`Calibre`) from the final representation (`Output`).

## Components

### Entry
`Entry` is the base class for any item in a feed or list. It can represent a navigation link (like a link to an author's page) or an acquisition link (a book). It contains a collection of `Link` objects.

### EntryBook
`EntryBook` extends `Entry` and is specifically for book items. It contains additional book-specific metadata like summary, publication date, and ISBN.

### Link
The `Link` class represents a hyperlink associated with an `Entry`. Each link has a relation (`rel`) and a MIME type (`type`) that define its purpose according to OPDS standards (e.g., an acquisition link for downloading a book, an image link for a cover thumbnail).

## Architecture Fit

1.  **Data Retrieval**: The `Pages` component queries the `Calibre` database to get raw data for a specific view.
2.  **Modeling**: The `Pages` component transforms this raw data into an array of `Entry` or `EntryBook` objects. Each entry is populated with the appropriate `Link` objects.
3.  **Rendering**: This standardized array of model objects is passed to an `Output` renderer (`HtmlRenderer`, `JsonRenderer`, `OpdsRenderer`).
4.  **Output**: The renderer iterates over the entries and links to generate the final HTML, JSON, or OPDS XML response.