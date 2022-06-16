jQuery(document).ready(function () {
    const referenceOptionValue = '2';
    const referenceOption = '0';
    const defaultOptionValue = '1';
    const mainElement = document.querySelector('select[name=oopluginlistviews-listtype');
    const mainElementShowReferenceEstate = document.querySelector('select[name=oopluginlistviews-showreferenceestate');


    mainElementShowReferenceEstate.addEventListener('change', function (event) {
        if (mainElementShowReferenceEstate.value === referenceOption) {
            $('.memssageReference').hide();
        }
        if (mainElementShowReferenceEstate.value === defaultOptionValue) {
            $('.memssageReference').show();
        }
        if (mainElementShowReferenceEstate.value === referenceOptionValue) {
            $('.memssageReference').show();
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
