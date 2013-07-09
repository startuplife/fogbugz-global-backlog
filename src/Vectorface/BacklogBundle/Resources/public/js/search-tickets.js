$(function() {
    $.ajax({
        url: "autocomplete",
        dataType: "json",
        success: function(data) {
            var counter = 0;
            var json = $.map(data, function(item, counter) {

                //Truncate items
                if(counter > 14){
                    return null;
                }

                //Truncate item.label
                if(item.label.length > 30){
                    item.label = item.label.substr(0,30) + '...';
                }

                return {
                    label: item.label,
                    value: item.label,
                    id: item.value
                };
            });

            $(".search-ticket").autocomplete({
                delay: 25,
                source: json,
                minLength: 1,
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