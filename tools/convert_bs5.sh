#!/bin/bash

# ---
# Bootstrap 3 to Bootstrap 5 Conversion Script for COPS 'twigged5' templates
#
# This script performs a series of search-and-replace operations to update
# Bootstrap 3 classes and attributes to their Bootstrap 5 equivalents.
#
# It's designed to handle the most common, repetitive changes.
# Manual adjustments will be required for more complex components like
# pagers, complex forms, and icon replacements.
#
# Usage:
# 1. Make a backup of your 'templates/twigged5' directory.
# 2. Run this script from the root of your project.
# 3. Manually review and fix the remaining issues.
# ---

TEMPLATE_DIR="templates/twigged5"

if [ ! -d "$TEMPLATE_DIR" ]; then
    echo "Error: Directory '$TEMPLATE_DIR' not found."
    echo "Please run this script from the root of your COPS project."
    exit 1
fi

echo "Starting Bootstrap 3 to 5 conversion for templates in '$TEMPLATE_DIR'..."

find "$TEMPLATE_DIR" -type f -name "*.html" | while read -r file; do
    echo "Processing $file..."

    # --- Grid System ---
    # col-xs-* -> col-*
    sed -i -E 's/col-xs-([0-9]+)/col-\1/g' "$file"
    # col-*-offset-* -> offset-*-*
    sed -i -E 's/col-([a-z]+)-offset-([0-9]+)/offset-\1-\2/g' "$file"

    # --- Navbar ---
    # navbar-inverse -> navbar-dark bg-dark
    sed -i 's/navbar-inverse/navbar-dark bg-dark/g' "$file"

    # --- Components: Panel -> Card ---
    sed -i 's/panel-default/card/g' "$file"
    sed -i 's/panel-heading/card-header/g' "$file"
    sed -i 's/panel-body/card-body/g' "$file"
    sed -i 's/panel-footer/card-footer/g' "$file"
    sed -i 's/panel/card/g' "$file" # General fallback for any remaining .panel

    # --- Components: Label -> Badge ---
    # .label.label-default -> .badge.bg-secondary
    sed -i 's/label label-default/badge bg-secondary/g' "$file"

    # --- Components: Badge ---
    # Add styling to simple .badge, but be careful not to double-up
    # This specifically targets badges with a pull-right
    sed -i 's/badge pull-right/badge bg-primary rounded-pill float-end/g' "$file"

    # --- Components: Dropdown ---
    # Remove .caret span, as BS5 handles this with CSS
    sed -i 's/<span class="caret"><\/span>//g' "$file"
    # Add .dropdown-toggle-split for split buttons
    sed -i -E 's/(class="btn[a-z -]+dropdown-toggle")/\1 dropdown-toggle-split/g' "$file"

    # --- Buttons ---
    # .btn-default -> .btn-secondary
    sed -i 's/btn-default/btn-secondary/g' "$file"

    # --- Helper Classes ---
    sed -i 's/pull-right/float-end/g' "$file"
    sed -i 's/img-responsive/img-fluid/g' "$file"
    sed -i 's/center-block/mx-auto d-block/g' "$file"

    # --- Responsive Utilities ---
    # .hidden-sm .hidden-md -> d-none d-sm-inline (common pattern in these files)
    # This is a contextual replacement. In base.html and mainlist.html, this combination
    # was used to hide text on small/medium screens but show it on others.
    # The BS5 equivalent `d-none d-sm-inline` is a good approximation for "show on larger screens".
    sed -i 's/hidden-sm hidden-md/d-none d-sm-inline/g' "$file"
    # .visible-xs -> .d-block .d-sm-none (Show only on extra-small)
    sed -i 's/visible-xs/d-block d-sm-none/g' "$file"

    # --- Responsive Text Alignment (from custom CSS in index.html) ---
    sed -i 's/text-xs-center/text-center/g' "$file"
    sed -i 's/text-sm-left/text-sm-start/g' "$file"
    sed -i 's/text-xs-left/text-start/g' "$file"

    # --- JavaScript data-* Attributes ---
    sed -i 's/data-toggle="/data-bs-toggle="/g' "$file"
    sed -i 's/data-target="/data-bs-target="/g' "$file"
    sed -i 's/data-dismiss="/data-bs-dismiss="/g' "$file"

    # --- Icons (Placeholder Replacement) ---
    # This replaces the span with an <i> tag and adds a TODO comment
    # to make manual replacement easier.
    sed -i -E 's/<span class="glyphicon glyphicon-([^"]+)"><\/span>/<i class="bi bi-\1"><\/i> <!-- TODO: Verify BS5 icon name -->/g' "$file"    

    # --- Forms ---
    # Prepare navbar-form for removal
    sed -i 's/navbar-form/d-flex/g' "$file"

done

echo "Conversion script finished."
echo "IMPORTANT: Manual review is required."
echo "Things to check:"
echo "1. Pager in 'mainlist.html' needs to be refactored to a <nav> and .pagination component."
echo "2. Forms in 'filters.html' and 'customize.html' need structural changes for .form-check."
echo "3. All icon replacements (now marked with 'TODO') must be verified against the Bootstrap Icons library."
echo "4. The search form in 'base.html' needs to be updated to use flex utilities."
