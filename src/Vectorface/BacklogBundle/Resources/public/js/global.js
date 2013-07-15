function updateAllPosition() {
    $('.table-tickets tbody > tr').each(function(i) {
        $(this).attr("data-position", i);
    });
}