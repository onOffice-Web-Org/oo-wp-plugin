jQuery(document).ready(function ($) {
    $('.oo-form .hidden-field').each(function() {
        if (this.nextSibling && this.nextSibling.nodeType === 3) {
            $(this.nextSibling).remove();
        }
        $(this).next('br').remove();
    });
});