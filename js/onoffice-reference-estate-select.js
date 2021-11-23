jQuery(document).ready(function() {
    const valueSelect = 'reference';
    var mainElement = $("select[name=oopluginlistviews-listtype]");
    var Element = $("input[name=oopluginlistviews-showreferenceestate]");
    mainElement.change(function () {
       let val = $(this).val();
       if (val === valueSelect) {
            Element.prop('disabled', 'disabled');
            Element.prop('checked', true);
       } else {
            Element.removeAttr('disabled');
           Element.prop('checked', false);
       }
    });
});
