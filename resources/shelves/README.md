# Client-Side Virtual Shelves Implementation

## Overview

This implementation adds client-side virtual shelves using `localStorage`, available in the twigged, bootstrap2 and bootstrap5 templates. Users can create multiple custom shelves, add/remove books, and manage their collection without any server-side changes.

## Files Modified

### Shared (all templates)

1. **`resources/shelves/shelves.js`** (NEW) - Shared ShelfManager core JavaScript library (data layer only, no template-specific markup)
2. **`resources/shelves/README.md`** (NEW) - This documentation file
3. **`src/Pages/PageShelves.php`** (NEW) - Server-side shelves page route
4. **`src/Pages/PageId.php`** - Added `SHELVES` page id and `ROUTE_SHELVES`
5. **`src/Output/JsonRenderer.php`** - Outputs `shelvesurl` and `use_client_shelves` in JSON

### twigged template (Twig)

6. **`templates/twigged/scripts/shelves.js`** (NEW) - Template-specific UI extensions for ShelfManager (Bootstrap 3 styling)
7. **`templates/twigged/base.html`** - Shelf navbar link (modal container removed)
8. **`templates/twigged/booklist.html`** - Shelf toggle button on each book card
9. **`templates/twigged/bookdetail.html`** - Shelf toggle button on book detail page
10. **`templates/twigged/index.html`** - Includes shared shelves.js and template-specific shelves.js
11. **`templates/twigged/scripts/cops.js`** - Initialize ShelfManager and update UI on page loads
12. **`templates/twigged/shelves.html`** (NEW) - Dedicated shelf page template with `#shelf-page-content` container

### bootstrap2 template (doT)

13. **`templates/bootstrap2/scripts/shelves.js`** (NEW) - Template-specific UI extensions (Bootstrap 3 styling, glyphicons)
14. **`templates/bootstrap2/file.html`** - Conditional script includes for shelves.js
15. **`templates/bootstrap2/header.html`** - Shelf navbar link with badge
16. **`templates/bootstrap2/main.html`** - Shelf toggle button on book cards; `#shelf-page-content` for shelves page
17. **`templates/bootstrap2/bookdetail.html`** - Shelf toggle button on book detail page
18. **`templates/bootstrap2/scripts/cops.js`** - Initialize ShelfManager and update UI on page loads

### bootstrap5 template (doT)

19. **`templates/bootstrap5/scripts/shelves.js`** (NEW) - Template-specific UI extensions (Bootstrap 5 styling, bootstrap-icons)
20. **`templates/bootstrap5/file.html`** - Conditional script includes for shelves.js
21. **`templates/bootstrap5/footer.html`** - Shelf footer nav link with badge (alongside Customize/About)
22. **`templates/bootstrap5/main.html`** - Shelf toggle button on book cards; `#shelf-page-content` for shelves page
23. **`templates/bootstrap5/bookdetail.html`** - Shelf toggle button on book detail page
24. **`templates/bootstrap5/scripts/cops.js`** - Initialize ShelfManager and update UI on page loads

## Features

- **Multiple Shelves**: Create, rename, and delete custom shelf names
- **Default Shelf**: Starts with "Favorites" shelf
- **Add/Remove Books**: Toggle books on/off the active shelf from list or detail views
- **Persistent Storage**: Uses browser localStorage (survives browser restart)
- **Visual Feedback**: 
  - Badge count near "Shelves" link shows books in active shelf
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
2. **View shelves**: Click "Shelves" link (navbar for twigged/bootstrap2, footer for bootstrap5; navigates to the dedicated shelves page)
3. **Switch shelves**: Use the dropdown on the shelves page
4. **Create new shelf**: Click "New" button on the shelves page
5. **Rename shelf**: Click "Rename" button on the shelves page
6. **Delete shelf**: Click "Delete" button (only available when more than one shelf exists)
7. **Remove book**: Click "Remove" button next to a book on the shelves page

## Technical Details

### ES5 Compatibility

All JavaScript is written in ES5 syntax to match the existing COPS templates (twigged, bootstrap2, bootstrap5):
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

1. **Page load initialization**: `ShelfManager.init()` is called once when the page first loads:
   - twigged: inside `initiateTwig()` in `cops.js`
   - bootstrap2/bootstrap5: inside `$(document).ready()` in `cops.js`
2. **`postRefresh()`** in `cops.js`: Updates shelf UI (badge count, button states, shelf page) after every AJAX navigation
3. **Dedicated shelves page**: Renders into `#shelf-page-content` container (no modal)

### File Structure

```
resources/shelves/
├── shelves.js           # Shared ShelfManager core library (data + localStorage)
└── README.md            # This documentation file

templates/twigged/
├── scripts/
│   ├── shelves.js       # Template-specific UI extensions (Bootstrap 3 styling)
│   └── cops.js          # Initializes ShelfManager, updates UI on page loads
├── index.html           # Loads shared shelves.js, then template-specific shelves.js
├── base.html            # Contains shelf navbar link
├── shelves.html         # Dedicated shelf page template with #shelf-page-content container
├── booklist.html        # Shelf toggle button on each book card
└── bookdetail.html      # Shelf toggle button on book detail page

templates/bootstrap2/
├── scripts/
│   ├── shelves.js       # Template-specific UI extensions (Bootstrap 3 styling, glyphicons)
│   └── cops.js          # Initializes ShelfManager, updates UI on page loads
├── file.html            # Conditional script includes for shelves.js
├── header.html          # Shelf navbar link with badge
├── main.html            # Shelf toggle button on book cards; #shelf-page-content for shelves page
└── bookdetail.html      # Shelf toggle button on book detail page

templates/bootstrap5/
├── scripts/
│   ├── shelves.js       # Template-specific UI extensions (Bootstrap 5 styling, bi-icons)
│   └── cops.js          # Initializes ShelfManager, updates UI on page loads
├── file.html            # Conditional script includes for shelves.js
├── footer.html          # Shelf footer nav link with badge
├── main.html            # Shelf toggle button on book cards; #shelf-page-content for shelves page
└── bookdetail.html      # Shelf toggle button on book detail page
```

The shared `resources/shelves/shelves.js` provides the core library with:
- Data management (`load`, `save`, `toggleBook`, `createShelf`, `deleteShelf`, `renameShelf`, etc.)
- Book identity via `getBookKey(book)` (`"db:id"` with falsy db normalized to `''`)
- Shared UI logic: `handleToggle`, `renderShelfPage`, `refreshShelfPage`, `bindPageEvents`, `openShelvesPage`
- Event delegation that calls the template-specific `updateBookButtons()`

Each template's `templates/<template>/scripts/shelves.js` defines only the styling-dependent parts:
- `updateBookButtons()` - active-state CSS classes for bookmark buttons
- `renderShelfContent()` - shelf page markup with that template's CSS classes, icons, and grid structure

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
- Bootstrap required (already loaded by each COPS template)

## Server-Side Rendering Compatibility

**Important**: The shelves feature is **only available when `server_side_rendering == 0`** (client-side rendering mode).

When `server_side_rendering == 1` (server-side rendering):
- The shelves.js script is NOT loaded
- The "Shelves" nav link does NOT appear
- The shelf page is NOT rendered
- Bookmark buttons do NOT appear on book cards or detail pages

This ensures:
- Clean interface for legacy/server-side rendering mode
- No JavaScript dependencies for users with older browsers
- Smaller page load for server-side rendering users

The feature is conditionally rendered in templates using `use_client_shelves`:

For Twig templates (twigged):
```twig
{% if it.c.config.use_client_shelves %}
    <!-- shelf UI elements -->
{% endif %}
```

For doT templates (bootstrap2, bootstrap5):
```html
{{? it.c.config.use_client_shelves }}
    <!-- shelf UI elements -->
{{?}}
```

## Limitations

- **Per-browser storage**: Shelves are stored per browser, not synced across devices
- **No server persistence**: Clearing browser data will delete shelves
- **No sharing**: Cannot share shelf lists with other users
- **Stale metadata**: Book titles/authors may become outdated if library changes (links still work)

## Adding shelves to another template

The shelf feature is split into two layers:

1. **Shared core** — `resources/shelves/shelves.js`
   - Data management (localStorage, book identity, shelf CRUD, `escapeHtml`)
   - Shared UI logic (`handleToggle`, `renderShelfPage`, `refreshShelfPage`, `bindPageEvents`, `openShelvesPage`)
   - No CSS classes, no Bootstrap assumptions
   - Exposed as the `ShelfManager` global
   - One template extension point: `updateBookButtons()` (active-state styling, called by `updateUI()`)

2. **Template-specific UI** — `templates/<template>/scripts/shelves.js`
   - Owns all HTML rendering with that template's classes/icons/styling
   - `renderShelfContent()` — full shelf page HTML (toolbar + book cards)
   - `updateBookButtons()` — toggle active-state CSS classes on `.shelf-toggle-btn` elements

To add shelves to a new template, follow the pattern used by bootstrap2 and bootstrap5:

1. Create `templates/<template>/scripts/shelves.js` defining `renderShelfContent()` and `updateBookButtons()` with that template's styling (e.g. Bootstrap 5 uses `card`, `bi-*` icons, `data-bs-toggle`, no `caret`, no `col-xs`).
2. Add conditional script includes in `templates/<template>/file.html` for `resources/shelves/shelves.js` + the template's `scripts/shelves.js` (when client-side rendering + `use_client_shelves`).
3. Add a "Shelves" nav link to the template's header or footer pointing to `shelvesurl`, with `#shelf-count` badge.
4. Add shelf toggle buttons to book cards and book detail pages, conditional on `use_client_shelves`. Handle `it.page == "shelves"` to render `#shelf-page-content`.
5. Add shelf UI calls (`ShelfManager.init()` once on load, `updateUI()` + `renderShelfPage()` in `postRefresh()`) to `templates/<template>/scripts/cops.js`.

## Future Enhancements

Possible improvements for future versions:
- Download all books in shelf as a .zip file (using existing ZipperHandler / ZipStream infrastructure)
- Export/import shelf data as JSON
- Sort books within shelves
- Search/filter within shelf view
- Shelf sharing via URL encoding
- Sync with Calibre reading lists
- Add/remove books to/from multiple shelves at once
