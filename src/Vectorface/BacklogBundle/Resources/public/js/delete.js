$(document).ready(function(){

    $('#deleteModal').modal({
        show: false
    });

    $("#deleteModal .trigger-delete").click(function() {
        var ixBug = $('#deleteModal').data('ixBug');
        $.ajax({
            url: 'delete/' + ixBug,
            type: 'post',
            success: function (data) {
                $('#deleteModal').modal('hide');
                $(".table-tickets").find("[data-ixBug='" + ixBug + "']").fadeOut(1000, function() { $(this).remove(); });

            }
        });
    });

    $(".table-tickets").on("click", ".trigger-modal-delete", function(e) {
        e.preventDefault();
        var ixBug = $(this).closest('tr').data('ixbug');
        $('#deleteModal').data('ixBug', ixBug);
        $('#deleteModal').modal('show');
    });

});