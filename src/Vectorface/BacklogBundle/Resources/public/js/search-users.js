$(function() {
    $.ajax({
        url: "autocomplete/users",
        dataType: "json",
        success: function(data) {
            var counter = 0;
            var json = $.map(data, function(item, counter) {

                autocomplete = {
                    label: item.label,
                    value: item.label,
                    id: item.value
                };
                return autocomplete;

            });

            $(".search-users").autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(json, request.term);
                    response(results.slice(0, 1));
                },
                minLength: 1,
                autoFocus: true,
            });

        }
    });
});