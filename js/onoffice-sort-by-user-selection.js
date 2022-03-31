var onOffice = onOffice || {};
jQuery(document).ready(function($) {
    onOffice.sortByUserSelection = function() {
        var sortbynames = [
            'oopluginlistviews-sortByUserDefinedDefault',
            'oopluginsortbyuservalues-sortbyuservalue',
            'oopluginlistviews-sortby'
        ];

        $("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]").change(function (){
            if(!this.checked) {
                sortbynames.forEach(function(item){
                    $("#viewrecordsfilter").find("[name="+item+"]").val(null).trigger("chosen:updated");
                });
				$("#viewrecordsfilter").find("[name='oopluginlistviews-sortByUserDefinedDirection']")
					.val(0).trigger("chosen:updated");
            }
        });
    };

    onOffice.generateSortByUserDefinedDefault = function() {
        if ($("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]").prop('checked') == true) {

            var oldSelected;
            var selectedDirection;
            var directions = ['ASC', 'DESC'];

            oldSelected = $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault] :selected").val();
            selectedDirection = $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDirection] :selected").val();
            var translationsMapping = onoffice_mapping_translations[selectedDirection];

            $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault] option").remove();

            $("#viewrecordsfilter")
                .find("[name=oopluginsortbyuservalues-sortbyuservalue] :selected")
                .each(function(i, option) {
                    for (var i = 0; i < directions.length; i++) {
                        if (option.value+'#'+directions[i] == oldSelected) {
                            $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault]")
                                .append("<option value='" + option.value + '#'+directions[i] + "' selected>" + option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                        } else {
                            $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault]")
                                .append("<option value='" + option.value + '#'+directions[i]  + "'>" + option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                        }
                    }
                });
        }
    };
	onOffice.sortByUserSelection();
});
