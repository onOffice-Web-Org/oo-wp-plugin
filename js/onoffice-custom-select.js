jQuery(document).ready(function ($) {
	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});

	if ($('.custom-multiple-select').length > 0) {
		const selectElement = $('.custom-multiple-select').first();
		const selectStyles = window.getComputedStyle(selectElement.get(0));

		$('.select2-container--default .select2-selection--multiple').css({
			'padding': selectStyles.padding,
			'display': 'flex'
		});
	}
});