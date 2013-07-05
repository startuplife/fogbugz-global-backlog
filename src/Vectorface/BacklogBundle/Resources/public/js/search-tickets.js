$(function() {
    $.ajax({
        url: "autocomplete",
        dataType: "json",
        success: function(data) {
            var json = $.map(data, function(item) {
                return {
                    label: item.label,
                    value: item.label,
                    id: item.value
                };
            });
            $(".search-ticket").autocomplete({
                delay: 50,
                source: json,
                minLength: 2,
                autoFocus: true,
                select: function( event, ui ) {
                    console.log(ui.item.value + " aka " + ui.item.id);
                    $.ajax({
                        url: 'add/' + ui.item.id,
                        type: 'post',
                        dataType: "json",
                        success: function (data) {
                            $('.search-ticket').val('');
                        }
                    });
                }
            });
        }
    });
});