function postRefresh()
{
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();
    $(".tt-hint").attr("name","searchTypeahead");

    /* Add submenu class to body to give extra spacing */
   if ($("#controls-menu").length > 0) {
    document.body.classList.add("submenu");
   } else {
    if (document.body.classList.contains("submenu")) document.body.classList.remove("submenu");
   }
}