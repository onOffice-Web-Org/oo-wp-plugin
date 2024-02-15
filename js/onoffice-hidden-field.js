jQuery(document).ready(function ($) {
    $('.oo-form div, .oo-form p, .oo-form').contents().filter(function() {
        return this.nodeType === 3 && $(this).next('.hidden-field').length > 0;
    }).remove();

    $('.oo-form .hidden-field').each(function() {
        if (this.nextSibling && this.nextSibling.nodeType === 3) {
            $(this.nextSibling).remove();
        }
        $(this).next('br').remove();
    });
});