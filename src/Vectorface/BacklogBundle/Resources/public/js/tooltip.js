$(document).on('mouseenter','[rel=tooltip]', function(){
    $(this).tooltip({ placement: 'left'});
    $(this).tooltip('show');
});