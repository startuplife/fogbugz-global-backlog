$(document).ready(function(){

    $('#deleteModal').modal({
        show: false
    });

    $("#deleteModal .trigger-delete").click(function() {
        var ixbug = $('#deleteModal').data('ixbug');
        $.ajax({
            url: 'delete/' + ixbug,
            type: 'post',
            success: function (data) {
                $('#deleteModal').modal('hide');
                $(".table-tickets").find("[data-ixbug='" + ixbug + "']").fadeOut(1000, function() { $(this).remove(); });

            }
        });
    });

    $(".table-tickets").on("click", ".trigger-modal-delete", function(e) {
        e.preventDefault();
        var ixbug = $(this).closest('tr').data('ixbug');
        $('#deleteModal').data('ixbug', ixbug);
        $('#deleteModal').modal('show');
    });

});