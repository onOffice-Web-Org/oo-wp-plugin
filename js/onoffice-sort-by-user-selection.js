var onOffice = onOffice || {};
jQuery(document).ready(function($) {
	onOffice.sortByUserSelection = function () {
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
				$("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault]")
					.find("option").remove();
			}
		});
	};

	onOffice.generateSortByUserDefinedDefault = function() {
		if ($("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]").prop('checked') == true) {
			var oldSelected = $("#viewrecordsfilter")
				.find("[name=oopluginlistviews-sortByUserDefinedDefault] :selected").val();
			var selectedDirection = $("#viewrecordsfilter")
				.find("[name=oopluginlistviews-sortByUserDefinedDirection] :selected").val();
			var standardSortInput = $("#viewrecordsfilter")
				.find("[name=oopluginlistviews-sortByUserDefinedDefault]");
			var sortByInput = $("#viewrecordsfilter")
				.find("[name=oopluginsortbyuservalues-sortbyuservalue] optgroup option:selected");
			var directions = ['ASC', 'DESC'];
			var translationsMapping = onoffice_mapping_translations[selectedDirection];

            standardSortInput.find("option").remove();
            if (sortByInput.length > 0) {
				sortByInput.each(function (i, option) {
                    for (var i = 0; i < directions.length; i++) {
                        if (option.value + '#' + directions[i] == oldSelected) {
                            standardSortInput.append("<option value='" + option.value + '#' + directions[i] + "' selected>"
								+ option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                        } else {
                            standardSortInput.append("<option value='" + option.value + '#' + directions[i] + "'>"
								+ option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                        }
                    }
                });
            }
            standardSortInput.trigger("chosen:updated");
		}
	};
	onOffice.sortByUserSelection();
});
