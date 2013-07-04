$(document).ready(function(){
    $(".arrow-up,.arrow-down").click(function(){
        var row = $(this).parents("tr:first");
        if ($(this).is(".arrow-up")) {
            row.insertBefore(row.prev());
        } else {
            row.insertAfter(row.next());
        }
    });
});