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


  $(function () {
    const rules = {
      email: node => /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(node.value),
      name: node => /^\s*[a-zA-Z0-9,\s]+\s*$/.test(node.value),
      text: node => node.value.trim().length > 0,
		  checkbox: node => node.checked 
    };

    function isValid(node) {
      const ruleName = node.dataset.rule;
      return rules[ruleName] ? rules[ruleName](node) : node.checkValidity();
    }

	function validate(node) {
		const $node = $(node);
		const $formRow = $node.closest('label, .oo-form');
		const $error = $formRow.find('.error');
		const valid = isValid(node);

		$node.attr('aria-invalid', !valid);
		$error
			.attr('aria-hidden', valid ? 'true' : 'false')
			[valid ? 'hide' : 'show']();
	} 
	  
	function validateForm($form) {
		let allValid = true;

		$form.find('[aria-invalid]').each(function () {
		validate(this);
		if (this.getAttribute('aria-invalid') === 'true') {
			allValid = false;
		}
		});

		return allValid && $form[0].checkValidity();
	}

    $(document).on('focusout', '[aria-invalid]', function () {
      validate(this);
    });

    $(document).on('submit', '.oo-form', function (e) {
      const $form = $(this);
      const isValidForm = validateForm($form);

      if (!isValidForm) {
        e.preventDefault();
        e.stopPropagation();
      } 
      $form.addClass('oo-validated');
    });
  });

