# Calibre Component

The `Calibre` namespace is the data access layer for the application. It is responsible for all interactions with the Calibre `metadata.db` SQLite database and for locating book files on the filesystem.

## Components

### Database
This class manages the connection to one or more `metadata.db` files. It provides methods for executing SQL queries and handles the logic for multi-database setups.

### Entity Classes
A set of classes (`Book`, `Author`, `Series`, `Tag`, `CustomColumn`, etc.) that represent individual records from the database. They provide an object-oriented interface to the raw data and contain logic for finding associated data (e.g., finding all books for an author).

### List Classes
Classes like `BookList` and `BaseList` are responsible for querying and returning lists of entities, handling filtering, sorting, and pagination logic at the database level.

## Architecture Fit

1.  **Data Request**: The `Pages` component needs data for a view (e.g., all books by a specific author).
2.  **Querying**: It uses a `Calibre` entity or list class (e.g., `Author` or `BookList`) to query the `Database`.
3.  **Data Return**: The `Calibre` component returns raw data, often as an array of entity objects (e.g., an array of `Book` objects).
4.  **Modeling**: This raw data is then passed to the `Model` component to be transformed into a standardized format (`Entry` or `EntryBook`) before being rendered by the `Output` component.