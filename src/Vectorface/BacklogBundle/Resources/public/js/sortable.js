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
        stop: function(event, ui) {
            var newPosition = $(ui.item).prevAll().length;
            var ixbug = $(ui.item).attr('data-ixbug');
            $.ajax({
                url: 'move/' + ixbug + '/' + newPosition,
                type: 'post',
                success: function (data) {
                    
                }
            });
        }
    }).disableSelection();
});