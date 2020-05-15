jQuery(document).ready(function($) {
    $('[name="' + shortcode.name + '"]').keyup(function(){
        oldVal = $(this).val();
        var sanitizedVal = oldVal.replace(/[^a-zA-Z0-9äÄöÖüÜß_ ]/g, '');
        $(this).val(sanitizedVal);
    });
});