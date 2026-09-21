# Client-Side Virtual Shelves Implementation

## Overview

This implementation adds client-side virtual shelves to the twigged template using `localStorage`. Users can create multiple custom shelves, add/remove books, and manage their collection without any server-side changes.

## Files Modified

1. **`templates/twigged/scripts/shelves.js`** (NEW) - Core ShelfManager JavaScript library
2. **`templates/twigged/base.html`** - Added shelf navbar link and modal container
3. **`templates/twigged/booklist.html`** - Added shelf toggle button to each book card
4. **`templates/twigged/bookdetail.html`** - Added shelf toggle button on book detail page
5. **`templates/twigged/index.html`** - Included shelves.js script
6. **`templates/twigged/scripts/cops.js`** - Initialize ShelfManager and update UI on page loads

## Features

- **Multiple Shelves**: Create, rename, and delete custom shelf names
- **Default Shelf**: Starts with "Favorites" shelf
- **Add/Remove Books**: Toggle books on/off the active shelf from list or detail views
- **Persistent Storage**: Uses browser localStorage (survives browser restart)
- **Visual Feedback**: 
  - Badge count in navbar shows books in active shelf
  - Button changes appearance when book is in shelf
- **Shelf Management Modal**: 
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

## Usage

1. **Add a book to shelf**: Click the bookmark icon (☆) on any book card or detail page
2. **View shelves**: Click "Shelves" in the top navigation bar
3. **Switch shelves**: Use the dropdown in the shelf modal
4. **Create new shelf**: Click "New" button in the modal
5. **Rename shelf**: Click "Rename" button in the modal
6. **Delete shelf**: Click "Delete" button (only available when more than one shelf exists)
7. **Remove book**: Click "Remove" button next to a book in the shelf modal

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
- The shelf modal is NOT rendered
- Bookmark buttons do NOT appear on book cards or detail pages

This ensures:
- Clean interface for legacy/server-side rendering mode
- No JavaScript dependencies for users with older browsers
- Smaller page load for server-side rendering users

The feature is conditionally rendered in templates using:
```twig
{% if it.server_side_rendering == 0 %}
    <!-- shelf UI elements -->
{% endif %}
```

## Limitations

- **Per-browser storage**: Shelves are stored per browser, not synced across devices
- **No server persistence**: Clearing browser data will delete shelves
- **No sharing**: Cannot share shelf lists with other users
- **Stale metadata**: Book titles/authors may become outdated if library changes (links still work)

## Future Enhancements

Possible improvements for future versions:
- Export/import shelf data as JSON
- Sort books within shelves
- Search/filter within shelf view
- Dedicated shelf page template (instead of modal-only)
- Shelf sharing via URL encoding
- Sync with Calibre reading lists
