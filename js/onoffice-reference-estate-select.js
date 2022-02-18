jQuery(document).ready(function() {
    const valueSelect = 'reference';
    const mainElement = document.querySelector('select[name=oopluginlistviews-listtype');
    const Element = document.querySelector('input[name=oopluginlistviews-showreferenceestate');
    mainElement.addEventListener('change',function(event){
       let val = event.target.value;
       if (val === valueSelect) {
            Element.setAttribute('disabled', 'disabled');
            Element.checked = true;
       } else {
            Element.removeAttribute('disabled');
            Element.checked = false;
       }
    });
});
