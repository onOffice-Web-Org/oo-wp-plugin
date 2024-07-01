jQuery(document).ready(function ($) {
    $('.oo-form .hidden-field').each(function() {
        if (this.nextSibling && this.nextSibling.nodeType === 3) {
            $(this.nextSibling).remove();
        }
        const nextBr = $(this).next('br');
        if (nextBr.length) {
            nextBr.remove();
        }
    });
});