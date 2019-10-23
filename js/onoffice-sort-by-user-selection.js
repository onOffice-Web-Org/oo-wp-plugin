var onOffice = onOffice || {};

onOffice.sortByUserSelection = function(){
	var sortbynames = ['oopluginlistviews-sortByUserDefinedDefault',
		'oopluginsortbyuservalues-sortbyuservalue',
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

onOffice.generateSortByUserDefinedDefault = function(){
	if ($("#viewrecordsfilter").find("[name=oopluginlistviews-sortBySetting]").attr('checked') == 'checked') {

		var oldSelected;

		oldSelected = $("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault] :selected").val();

		$("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault] option").remove();

		$("#viewrecordsfilter").find("[name=oopluginsortbyuservalues-sortbyuservalue] :selected").each(function(i, option){
		if (option.value == oldSelected) {
			$("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault]").append("<option value='"+option.value+"' selected>"+option.text+"</option>");
		} else {
			$("#viewrecordsfilter").find("[name=oopluginlistviews-sortByUserDefinedDefault]").append("<option value='"+option.value+"'>"+option.text+"</option>");
		}
		});
	}
}

$(document).ready(function() {
	onOffice.sortByUserSelection();
});