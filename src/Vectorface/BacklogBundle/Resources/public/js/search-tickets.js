$(function() {
    $.ajax({
        url: "autocomplete",
        dataType: "json",
        success: function(data) {
            var counter = 0;
            var json = $.map(data, function(item, counter) {

                //Truncate item.label
                if(item.label.length > 30){
                    item.label = item.label.substr(0,30) + '...';
                }

                //[0]: Create custom search of ticket id + title
                autocomplete = {
                    label: '#' + item.value + ' - ' + item.label,
                    value: item.label,
                    id: item.value
                };
                return autocomplete;

            });

            $(".search-ticket").autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(json, request.term);
                    response(results.slice(0, 15));
                },
                minLength: 1,
                autoFocus: true,
                select: function( event, ui ) {
                    $.ajax({
                        url: 'add/' + ui.item.id,
                        type: 'post',
                        dataType: "json",
                        success: function (data) {
                            if(data.status) {
                                $('.search-ticket').val('');
                                addTicketRow(data.ticket, ui.item.id);
                            }
                        }
                    });
                }
            });

            function addTicketRow(data, ixBug) {
                data = missingData(data);
                html = '<tr data-ixBug="' + ixBug + '">';
                html += '<td><i class="icon-list draggable"></i></td><td>' + ixBug + '</td><td><a href="' + data.url + ixBug +'">' + data.sTitle + '</a></td><td>' + data.sFixFor + '</td><td>' + data.sProject + '</td><td>' + data.sPersonAssignedTo + '</td>';
                html += '<td><div class="btn-group pull-right"><a class="btn btn-danger trigger-modal-delete" href="#"><i class="icon-trash"></i></a></div></td>';
                html += '</tr>';
                row = $('.table-tickets tbody').prepend(html);
            }

            function missingData(data) {
                //Add muted none div to undefined or undecided content
                for (entry in data) {
                    if(data[entry] === undefined || data[entry].indexOf('Undecided') >= 0 || data[entry].indexOf('Unassigned') >= 0) {
                        data[entry] = '<span class="muted">' + data[entry] + '</span>';
                    }
                }
                return data;
            }
        }
    });
});