$(document).ready(function(){

    $('#deleteModal').modal({
        show: false
    });

    $("#deleteModal .trigger-delete").click(function() {
        console.log('hello');
    });

    $(".trigger-modal-delete").click(function() {
        $('#deleteModal').modal('show');
    });

});