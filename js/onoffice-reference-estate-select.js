jQuery(document).ready(function() {
    const referenceOptionValue = '2';
    const referenceOption = '0';
    const defaultOptionValue = '1';
    const mainElement = document.querySelector('select[name=oopluginlistviews-listtype');
    const mainElementShowReferenceEstate = document.querySelector('select[name=oopluginlistviews-showreferenceestate');

    $("#viewrecordsfilter .inside").append(" <p class=wp-clearfix><label class=memssageReference>Reference estates will not link to their detail page, because the access is <a>restricted</a>.</label></p>");
    $("#viewrecordsfilter .inside").append(" <p class=wp-clearfix><label class=memssageOnlyReference>Reference estates will link to their detail page, because the access is <a>not restricted</a>.</label></p>");

    $('.memssageReference').hide();
    $('.memssageOnlyReference').hide();

    mainElementShowReferenceEstate.addEventListener('change',function(event){
        if (mainElementShowReferenceEstate.value === referenceOption) {
            $('.memssageReference').hide();
            $('.memssageOnlyReference').hide();
        }
        if (mainElementShowReferenceEstate.value === defaultOptionValue) {
            $('.memssageReference').show();
            $('.memssageOnlyReference').hide();
        }
        if (mainElementShowReferenceEstate.value === referenceOptionValue) {
            $('.memssageOnlyReference').show();
            $('.memssageReference').hide();
        }
           let val = event.target.value;
           if (val === referenceOptionValue) {
                Element.setAttribute('disabled', 'disabled');
                Element.checked = true;
           } else {
                Element.removeAttribute('disabled');
                Element.checked = false;
           }
        });
});
