var onOffice = onOffice || {};
jQuery(document).ready(function($) {
	var sortByUserCheckbox = $("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]");
	var sortByUserValue = $("#viewrecordsfilter").find("[name=oopluginsortbyuservalues-sortbyuservalue]");
	var sortRandom = $("#viewrecordsfilter").find("[name=oopluginlistviews-random]");
	var sortbynames = [
		'oopluginlistviews-sortByUserDefinedDefault',
		'oopluginsortbyuservalues-sortbyuservalue',
		'oopluginlistviews-sortby',
		'oopluginlistviews-sortByUserDefinedDirection',
	];
	var defaultsorts = [
		'oopluginlistviews-sortby',
		'oopluginlistviews-sortorder'
	];

	onOffice.sortInputChecker = function () {
		if (sortByUserCheckbox.prop('checked') == true) {
			sortbynames.forEach(function (item) {
				$("#viewrecordsfilter").find("[name=" + item + "]").parent().show();
			});
			defaultsorts.forEach(function (item) {
				$("#viewrecordsfilter").find("[name=" + item + "]").parent().hide();
			});
		} else {
			sortbynames.forEach(function (item) {
				$("#viewrecordsfilter").find("[name=" + item + "]").parent().hide();
			});
			defaultsorts.forEach(function (item) {
				$("#viewrecordsfilter").find("[name=" + item + "]").parent().show();
			});
		}
	};

	onOffice.generateSortByUserDefinedDefault = function () {
		if (sortByUserCheckbox.prop('checked') == true) {
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
	onOffice.sortRandomChecker = function () {
		if (sortRandom.prop('checked') == true) {
			sortByUserCheckbox.prop('disabled', true);
		} else {
			sortByUserCheckbox.removeAttr('disabled');
		}
	}

	onOffice.sortInputChecker();
	onOffice.sortRandomChecker();
	sortByUserCheckbox.change(function () {
		onOffice.sortInputChecker();
	});
	sortByUserValue.change(function () {
		onOffice.generateSortByUserDefinedDefault();
	});
	sortRandom.change(function () {
		onOffice.sortRandomChecker();
	});
});
