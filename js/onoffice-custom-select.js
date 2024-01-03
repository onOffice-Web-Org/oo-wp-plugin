jQuery(document).ready(function ($) {
	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});

	$('.onoffice-custom-select2').select2({
		placeholder: 'Select Some Options'
	});

	$(".onoffice-custom-select2[name='oopluginlistviews-sortByUserDefinedDefault']").select2({
		placeholder: 'Select an Option'
	});
});