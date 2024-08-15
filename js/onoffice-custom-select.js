const custom_select2 = (typeof custom_select2_translation !== 'undefined') ? custom_select2_translation : {};
jQuery(document).ready(function ($) {
	const $adminSelect = $('.oo-poststuff .custom-input-field .oo-custom-select2');
	const $multiSelectAdminSorting = $('#viewrecordssorting .oo-custom-select2.oo-custom-select2--multiple');
	const $singleSelectAdminSorting = $("#viewrecordssorting .oo-custom-select2.oo-custom-select2--single");

	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});

	if ($adminSelect.length > 0) {
		$adminSelect.select2({
			width: '50%'
		});
	}

	if ($multiSelectAdminSorting.length) {
		$multiSelectAdminSorting.select2({
			placeholder: custom_select2.multipleSelectOptions,
			width: '50%'
		});
	}

	if ($singleSelectAdminSorting.length) {
		$singleSelectAdminSorting.select2({
			placeholder: custom_select2.singleSelectOption,
			width: '50%'
		});
	}
});