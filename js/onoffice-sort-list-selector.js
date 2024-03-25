jQuery(document).ready(function($) {
    $('.onofficeSortListSelector').change(function() {
        let listviewID = $(this).data('sort-listviewid');
        var selectedvalue = $(this).val();
        var sortUrlParameter = [];

        if (selectedvalue != "") {
            sortUrlParameter[`sortby_id_${listviewID}`] = selectedvalue.split("#")[0];
            sortUrlParameter[`sortorder_id_${listviewID}`] = selectedvalue.split("#")[1];
        }
        var searchparams = new URLSearchParams(window.location.search);

        for (key in sortUrlParameter) {
            if (searchparams.has(key)){
                if (sortUrlParameter[key] == ""){
                    searchparams.delete(key);
                } else{
                    searchparams.set(key, sortUrlParameter[key] );
                }
            } else {
                if (sortUrlParameter[key]  != "") {
                    searchparams.append(key, sortUrlParameter[key] );
                }
            }
        }

        var newLocationParameters = searchparams.toString();

        var loc = window.location.href;
        var locWithoutParams = loc.split("?");

        if (newLocationParameters != ""){
            locWithoutParams[1] = newLocationParameters;
        } else {
            if (locWithoutParams.length > 1) {
                locWithoutParams.pop();
            }
        }
        window.location.href = locWithoutParams.join("?");
    });
});