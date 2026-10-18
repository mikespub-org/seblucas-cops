// Refactored to replace ES6+ features with ES5-compatible syntax
function postRefresh()
{
    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    var tooltipList = Array.prototype.map.call(tooltipTriggerList, function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) {
        elmnt.scrollIntoView();
    }
    $(".tt-hint").attr("name","searchTypeahead");

    /* Add submenu class to body to give extra spacing */
    if ($("#controls-menu").length > 0) {
        document.body.classList.add("submenu");
    } else {
        if (document.body.classList.contains("submenu")) {
            document.body.classList.remove("submenu");
        }
    }

    // Update shelf UI
    if (typeof ShelfManager !== 'undefined') {
        if (typeof ShelfManager.updateUI === 'function') {
            ShelfManager.updateUI();
        }
        if (typeof ShelfManager.renderShelfPage === 'function') {
            ShelfManager.renderShelfPage();
        }
    }
}

$(document).ready(function() {
    if (typeof ShelfManager !== 'undefined' && typeof ShelfManager.init === 'function') {
        ShelfManager.init();
    }
});