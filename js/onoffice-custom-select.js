jQuery(document).ready(function ($) {
	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});

	const selectElement = $('.form-control').first();

	if (selectElement.hasClass('custom-multiple-select')) {
		const selectStyles = window.getComputedStyle(selectElement.get(0));
		$('.select2-container--default .select2-selection--multiple').css({
			'padding': selectStyles.padding
		});
	}
});