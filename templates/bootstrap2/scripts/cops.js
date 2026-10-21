function postRefresh()
{
    $('[data-toggle="tooltip"]').tooltip();
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();

    // Update shelf UI
    if (typeof ShelfManager !== 'undefined') {
        if (typeof ShelfManager.updateUI === 'function') {
            ShelfManager.updateUI();
        }
        if (typeof ShelfManager.renderShelfPage === 'function') {
            ShelfManager.renderShelfPage();
        }
        if (typeof ShelfManager.handleSharedShelf === 'function') {
            ShelfManager.handleSharedShelf();
        }
    }
}

$(document).ready(function() {
    if (typeof ShelfManager !== 'undefined' && typeof ShelfManager.init === 'function') {
        ShelfManager.init();
    }
});