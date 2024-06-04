onOffice = onOffice || {};
onOffice.custom_select2_translation = onOffice.custom_select2_translation || [];
jQuery(document).ready(function ($) {
	const $multiSelectAdminSorting = $('#viewrecordssorting .oo-custom-select2.oo-custom-select2--multiple');
	const $singleSelectAdminSorting = $("#viewrecordssorting .oo-custom-select2.oo-custom-select2--single");

	$('.custom-multiple-select, .custom-single-select').select2({
		width: '100%'
	});

	$multiSelectAdminSorting.select2({
		placeholder: custom_select2_translation.multipleSelectOptions,
		width: '50%'
	});

	$singleSelectAdminSorting.select2({
		placeholder: custom_select2_translation.singleSelectOption,
		width: '50%'
	});
});