jQuery(document).ready(function ($) {
	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});
	if ($('.custom-multi-select2').length > 0) {
		$('.custom-multi-select2').select2();
	}
});