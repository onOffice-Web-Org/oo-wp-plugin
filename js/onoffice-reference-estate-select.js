jQuery(document).ready(function() {
    const referenceOptionValue = 'reference';
    const mainElement = document.querySelector('select[name=oopluginlistviews-listtype');
    const Element = document.querySelector('input[name=oopluginlistviews-showreferenceestate');
    window.addEventListener('load', function(){
        if (mainElement.value === referenceOptionValue) {
            Element.setAttribute('disabled', 'disabled');
            Element.checked = true;
        }
    });
    mainElement.addEventListener('change',function(event){
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
