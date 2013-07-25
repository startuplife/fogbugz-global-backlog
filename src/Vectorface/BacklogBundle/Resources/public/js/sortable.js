// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
    ui.children().each(function() {
        $(this).width($(this).width());
    });
    return ui;
}

$(function() {
    $( "#sortable tbody" ).sortable({
        helper: fixHelper,
        placeholder: "warning",
        handle: '.icon-list'
    }).disableSelection();
});