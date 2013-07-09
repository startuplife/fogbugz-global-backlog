$(document).ready(function() {
    $(".arrow-up,.arrow-down").click(function() {
        var row = $(this).parents("tr:first");

        if ($(this).is(".arrow-up") && row.prev().length > 0) {
            row.stop().removeAttr('style');
            row.insertBefore(row.prev());
            row.effect("highlight", {}, 800);

        } else if($(this).is(".arrow-down") && row.next().length > 0) {
            row.stop().removeAttr('style');
            row.insertAfter(row.next());
            row.effect("highlight", {}, 800);

        }
    });
});