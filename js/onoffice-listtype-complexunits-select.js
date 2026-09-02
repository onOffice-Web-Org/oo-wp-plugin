jQuery(document).ready(function ($) {
    const LISTVIEW_TYPE_COMPLEXUNITS = 'complexunits';
    const mainElementListType = document.querySelector('select[name=oopluginlistviews-listtype]');
    const parentEstateIdField = document.querySelector('[name=oopluginlistviews-parentestateid]');

    if (!mainElementListType || !parentEstateIdField) {
        return;
    }

    const parentEstateIdRow = parentEstateIdField.closest('p') || parentEstateIdField;

    const toggleParentEstateIdVisibility = function () {
        if (mainElementListType.value === LISTVIEW_TYPE_COMPLEXUNITS) {
            $(parentEstateIdRow).show();
        } else {
            $(parentEstateIdRow).hide();
        }
    };

    mainElementListType.addEventListener('change', toggleParentEstateIdVisibility);
    toggleParentEstateIdVisibility();
});
