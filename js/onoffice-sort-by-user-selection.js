var onOffice = onOffice || {};

onOffice.sortByUserSelection = function(){
	var sortbynames = ['oopluginlistviews-sortByUserDefinedDefault',
		'oopluginlistviews-sortByValuesUserDefined',
		'oopluginlistviews-sortByUserDefinedDirection',
		'oopluginlistviews-sortby'];

	var defaultsorts = ['oopluginlistviews-sortby',
		'oopluginlistviews-sortorder'];

	if ($("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]").attr('checked') == 'checked') {
		sortbynames.forEach(function(item){
			$("#viewrecordsfilter").find("[name="+item+"]").parent().show();
		});

		defaultsorts.forEach(function(item){
			$("#viewrecordsfilter").find("[name="+item+"]").parent().hide();
		});
	}
	else {
		sortbynames.forEach(function(item){
			$("#viewrecordsfilter").find("[name="+item+"]").parent().hide();
		});

		defaultsorts.forEach(function(item){
			$("#viewrecordsfilter").find("[name="+item+"]").parent().show();
		});
	}
}

$(document).ready(function() {
	onOffice.sortByUserSelection();
});