# Client-Side Virtual Shelves Implementation

## Overview

This implementation adds client-side virtual shelves to the twigged template using `localStorage`. Users can create multiple custom shelves, add/remove books, and manage their collection without any server-side changes.

## Files Modified

1. **`resources/shelves/shelves.js`** (NEW) - Shared ShelfManager core JavaScript library
2. **`resources/shelves/README.md`** (NEW) - This documentation file
3. **`templates/twigged/scripts/shelves.js`** (MODIFIED) - Template-specific UI extensions for ShelfManager
4. **`templates/twigged/base.html`** - Added shelf navbar link (modal container removed)
5. **`templates/twigged/booklist.html`** - Added shelf toggle button to each book card
6. **`templates/twigged/bookdetail.html`** - Added shelf toggle button on book detail page
7. **`templates/twigged/index.html`** - Included shared shelves.js and template-specific shelves.js
8. **`templates/twigged/scripts/cops.js`** - Initialize ShelfManager and update UI on page loads
9. **`templates/twigged/shelves.html`** (NEW) - Dedicated shelf page template with `#shelf-page-content` container

## Features

- **Multiple Shelves**: Create, rename, and delete custom shelf names
- **Default Shelf**: Starts with "Favorites" shelf
- **Add/Remove Books**: Toggle books on/off the active shelf from list or detail views
- **Persistent Storage**: Uses browser localStorage (survives browser restart)
- **Visual Feedback**: 
  - Badge count in navbar shows books in active shelf
  - Button changes appearance when book is in shelf
- **Shelf Management Page**: 
  - Switch between shelves
  - View all books in current shelf with thumbnails
  - Remove books from shelf
  - Create/rename/delete shelves

## Storage Format

```javascript
{
  "activeShelf": "Favorites",
  "shelves": {
    "Favorites": [
      {
        "id": "123",
        "db": "",
        "title": "The Hobbit",
        "author": "J.R.R. Tolkien",
        "thumbnailurl": "...",
        "detailurl": "...",
        "seriesName": "Lord of the Rings",
        "seriesIndex": 1
      }
    ],
    "To Read": []
  }
}
```

**localStorage key**: `cops_shelves`

**Book identity**: A book is uniquely identified by its `(databaseId, book id)` pair, normalized to a `"db:id"` key. The `db` values `undefined`, `null`, `''` and `0` are all considered equivalent (empty key), so the default/single database always matches regardless of how the id was rendered. Books from other databases (`db: "1"`, `"2"`, ...) keep distinct identities even if they share the same book id.

## Usage

1. **Add a book to shelf**: Click the bookmark icon (☆) on any book card or detail page
2. **View shelves**: Click "Shelves" in the top navigation bar (navigates to the dedicated shelves page)
3. **Switch shelves**: Use the dropdown on the shelves page
4. **Create new shelf**: Click "New" button on the shelves page
5. **Rename shelf**: Click "Rename" button on the shelves page
6. **Delete shelf**: Click "Delete" button (only available when more than one shelf exists)
7. **Remove book**: Click "Remove" button next to a book on the shelves page

## Technical Details

### ES5 Compatibility

All JavaScript is written in ES5 syntax to match the existing twigged template:
- Uses `var` instead of `let`/`const`
- Traditional `for` loops instead of `forEach`/`map`
- No arrow functions
- No template literals

### Event Delegation

Shelf toggle buttons use jQuery event delegation (`$(document).on('click', ...)`) because COPS uses AJAX navigation that re-renders the DOM. This ensures buttons work after navigation without re-binding.

### localStorage Error Handling

The implementation includes try/catch blocks to handle:
- Private browsing mode where localStorage may be unavailable
- QuotaExceededError when localStorage is full
- Corrupted data in localStorage

If localStorage is unavailable, the feature gracefully degrades (buttons do nothing, no errors shown).

### Integration Points

1. **`initiateTwig()`** in `cops.js`: Initializes ShelfManager after first page load
2. **`postRefresh()`** in `cops.js`: Updates shelf UI (badge count, button states) after AJAX navigation
3. **Bootstrap Modal**: Uses existing Bootstrap modal for shelf management UI

### File Structure

```
resources/shelves/
├── shelves.js           # Shared ShelfManager core library (data + localStorage)
└── README.md            # This documentation file

templates/twigged/
├── scripts/
│   ├── shelves.js       # Template-specific UI extensions (extends ShelfManager)
│   └── cops.js          # Initializes ShelfManager, updates UI on page loads
├── index.html           # Loads shared shelves.js, then template-specific shelves.js
├── base.html            # Contains shelf navbar link (modal container removed)
├── shelves.html         # Dedicated shelf page template with #shelf-page-content container
├── booklist.html        # Shelf toggle button on each book card
└── bookdetail.html      # Shelf toggle button on book detail page
```

The shared `resources/shelves/shelves.js` provides the core library with data management (`load`, `save`, `toggleBook`, etc.), book identity via `getBookKey(book)` (`"db:id"` with falsy db normalized to `''`), and event delegation that calls template-specific methods (`openShelvesPage`, `handleToggle`, `updateBookButtons`).

The template-specific `templates/twigged/scripts/shelves.js` extends ShelfManager with UI rendering functions (bookmark buttons, dedicated page content styled to match the book cards in `booklist.html`).

## Functional Test

A standalone Node.js functional test is available at **`tests/shelves_test.js`**. It covers book identity (db normalization), localStorage persistence, multi-database shelves, shelf management, dedicated page rendering (including XSS escaping), and error handling - without requiring a browser, webserver or PHP.

```bash
node tests/shelves_test.js
```

The test exits with code 0 on success and 1 on failure, so it can be used in automated pipelines.

## Browser Compatibility

- Modern browsers with localStorage support (Chrome, Firefox, Safari, Edge)
- ES5 JavaScript support required
- jQuery required (already loaded by COPS)
- Bootstrap modal required (already loaded by COPS)

## Server-Side Rendering Compatibility

**Important**: The shelves feature is **only available when `server_side_rendering == 0`** (client-side rendering mode).

When `server_side_rendering == 1` (server-side rendering):
- The shelves.js script is NOT loaded
- The "Shelves" navbar link does NOT appear
- The shelf page is NOT rendered
- Bookmark buttons do NOT appear on book cards or detail pages

This ensures:
- Clean interface for legacy/server-side rendering mode
- No JavaScript dependencies for users with older browsers
- Smaller page load for server-side rendering users

The feature is conditionally rendered in templates using:
```twig
{% if it.c.config.use_client_shelves %}
    <!-- shelf UI elements -->
{% endif %}
```

## Limitations

- **Per-browser storage**: Shelves are stored per browser, not synced across devices
- **No server persistence**: Clearing browser data will delete shelves
- **No sharing**: Cannot share shelf lists with other users
- **Stale metadata**: Book titles/authors may become outdated if library changes (links still work)

## Adding shelves to another template

The shelf feature is split into two layers:

1. **Shared data core** — `resources/shelves/shelves.js`
   - Data only (localStorage, book identity, shelf CRUD, `escapeHtml`)
   - No HTML rendering, no CSS classes, no Bootstrap assumptions
   - Exposed as the `ShelfManager` global
   - Two template extension points called by `init()` / `updateUI()`:
     - `ShelfManager.handleToggle(btn)` — per-book toggle click handler
     - `ShelfManager.updateBookButtons()` — update active-state styling on `.shelf-toggle-btn` elements

2. **Template-specific UI** — `templates/<template>/scripts/shelves.js`
   - Owns all HTML rendering with that template's classes/icons/styling
   - `renderShelfContent()` — full shelf page HTML (toolbar + book cards)
   - `bindPageEvents()` — wire up shelf switcher, create/delete/rename/remove buttons
   - `openShelvesPage()` — navigate to `shelvesurl` when "Shelves" is clicked
   - `renderShelfPage()` / `refreshShelfPage()` — write content to `#shelf-page-content`

To add shelves to bootstrap2 or bootstrap5:

1. Create `templates/<template>/scripts/shelves.js` implementing the five methods above with that template's styling (e.g. Bootstrap 5 uses `card`, `bi-*` icons, `data-bs-toggle`, no `caret`, no `col-xs`).
2. Add conditional script includes in `templates/<template>/file.html` for `resources/shelves/shelves.js` + the template's `scripts/shelves.js` (when client-side rendering + `use_client_shelves`).
3. Add a "Shelves" nav link to `templates/<template>/header.html` pointing to `shelvesurl`.
4. Add shelf toggle buttons to book cards in `templates/<template>/main.html` and handle `it.page == "shelves"`.
5. Add shelf UI calls (`ShelfManager.init()`, `updateUI()`, `renderShelfPage()`) to `templates/<template>/scripts/cops.js`.

## Future Enhancements

Possible improvements for future versions:
- Export/import shelf data as JSON
- Sort books within shelves
- Search/filter within shelf view
- Shelf sharing via URL encoding
- Sync with Calibre reading lists
