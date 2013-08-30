$('.checkall:checkbox').click(function (e) {
    var column = $(this).closest('th').index()+1;
    var checked = $(this).prop('checked');
    $(this).closest('table').find('td:nth-child(' + column + ')').each(function(index, el) {
        if(checked) {
            $(this).find(':checkbox').prop('checked', true);
        } else {
            $(this).find(':checkbox').prop('checked', false);
        }
    });
});