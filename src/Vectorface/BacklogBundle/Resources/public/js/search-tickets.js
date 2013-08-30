$(function() {
    $.ajax({
        url: "/backlog/autocomplete/tickets/open",
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

            $(".search-tickets").autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(json, request.term);
                    response(results.slice(0, 15));
                },
                minLength: 1,
                autoFocus: true,
                select: function( event, ui ) {
                    var listId = $('.search-tickets').attr('data-list-id');
                    $.ajax({
                        url: 'add/' + ui.item.id + '/' + listId,
                        type: 'post',
                        dataType: "json",
                        success: function (data) {
                            if(data.status) {
                                $('.search-tickets').val('');
                                addTicketRow(data.ticket, ui.item.id);
                            }
                        }
                    });
                }
            });

            function addTicketRow(data, ixBug) {
                data = missingData(data);
                html = '<tr data-ixBug="' + ixBug + '">';
                html += '<td>&nbsp;</td><td><input type="checkbox" name="" value=""></td><td><a href="' + data.url + ixBug +'">' + data.sTitle + '</a></td><td>' + data.sStatus + '</td><td>' + data.hrsCurrEst + '</td><td>' + data.sPersonAssignedTo + '</td>';
                html += '<td><div class="btn-group pull-right"><a class="btn trigger-modal-edit" href="#"><i class="icon-pencil"></i></a><a class="btn btn-danger trigger-modal-delete" href="#"><i class="icon-trash"></i></a></div></td>';
                html += '</tr>';
                row = $('.table-tickets tbody').prepend(html);
            }

            function missingData(data) {
                //Add muted none div to undefined or undecided content
                for (entry in data) {
                    if(data[entry] === undefined || data[entry].indexOf('Undecided') >= 0 || data[entry].indexOf('Unassigned') >= 0 || data[entry] == 0) {
                        data[entry] = '<span class="muted">None</span>';
                    }
                }
                return data;
            }
        }
    });
});

$(".dropdown-menu li a").click(function(){
  var selectedText = $(this).text();
  $(this).parents('.btn-group').find('.dropdown-text').html(selectedText);
});