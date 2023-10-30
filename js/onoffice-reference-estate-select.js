jQuery(document).ready(function ($) {
    const HIDE_REFERENCE_ESTATE = '0';
    const SHOW_REFERENCE_ESTATE = '1';
    const SHOW_ONLY_REFERENCE_ESTATE = '2';
    const mainElementShowReferenceEstate = document.querySelector('select[name=oopluginlistviews-showreferenceestate');

    mainElementShowReferenceEstate.addEventListener('change', function (event) {
        if (mainElementShowReferenceEstate.value === HIDE_REFERENCE_ESTATE) {
            $('.memssageReference').hide();
        }
        if (mainElementShowReferenceEstate.value === SHOW_REFERENCE_ESTATE) {
            $('.memssageReference').show();
        }
        if (mainElementShowReferenceEstate.value === SHOW_ONLY_REFERENCE_ESTATE) {
            $('.memssageReference').show();
        }
        let val = event.target.value;
        if (val === SHOW_ONLY_REFERENCE_ESTATE) {
            Element.setAttribute('disabled', 'disabled');
            Element.checked = true;
        } else {
            Element.removeAttribute('disabled');
            Element.checked = false;
        }
    });
});
