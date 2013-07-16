$(document).ready(function() {
    $(".table-tickets").on("click", ".arrow-up,.arrow-down", function() {
        updateAllPosition();
        var row = $(this).parents("tr:first");
        var ixbug = row.attr('data-ixbug');
        var position = row.attr('data-position');
        row.stop().removeAttr('style');

        if ($(this).is(".arrow-up") && row.prev().length > 0) {
            $.ajax({
                url: 'up/' + position + '/' + ixbug,
                type: 'post',
                success: function (data) {

                    row.insertBefore(row.prev());
                    row.effect("highlight", {}, 800);
                    updateAllPosition()
                }
            });

        } else if($(this).is(".arrow-down") && row.next().length > 0) {
            $.ajax({
                url: 'down/' + position + '/' + ixbug,
                type: 'post',
                success: function (data) {

                    row.insertAfter(row.next());
                    row.effect("highlight", {}, 800);
                    updateAllPosition()
                }
            });

        }
    });

});