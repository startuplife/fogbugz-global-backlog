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

    $('#editModal').modal({
        show: false
    });

    $("#editModal .trigger-edit").click(function() {
        var ixbug = $('#editModal').data('ixbug');
        $.ajax({
            url: 'edit/' + ixbug,
            type: 'post',
            data: $("#editModal form").serialize(),
            success: function (data) {
                $('#editModal').modal('hide');
                //Being super lazy until handlebars support is added
                window.location.reload(true);
            }
        });
    });

    $(".table-tickets").on("click", ".trigger-modal-edit", function(e) {
        e.preventDefault();
        var ixbug = $(this).closest('tr').data('ixbug');
        $('#editModal').data('ixbug', ixbug);
        $.ajax({
                url: 'edit/' + ixbug,
                type: 'post',
                success: function (data) {
                    $('#timeEstimate').val(data.hrsCurrEst);
                    $('#personAssignedTo').val(data.sPersonAssignedTo);
                    $('#editModal').modal('show');
                }
            });
    });

});