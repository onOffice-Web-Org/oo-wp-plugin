jQuery(document).ready(function ($) {
    $('#onoffice-form div, #onoffice-form p, #onoffice-form').contents().filter(function() {
        return this.nodeType === 3 && $(this).next('.hidden-field').length > 0;
    }).remove();


    $('#onoffice-form .hidden-field').each(function() {
        if (this.nextSibling && this.nextSibling.nodeType === 3) {
            $(this.nextSibling).remove();
        }
        $(this).next('br').remove();
    });
});