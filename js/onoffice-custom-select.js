const custom_select2 = (typeof custom_select2_translation !== 'undefined')
  ? custom_select2_translation
  : {};

jQuery(document).ready(function ($) {

  const $adminSelect = $('.oo-poststuff .custom-input-field .oo-custom-select2');
  const $multiSelectAdminSorting = $('#viewrecordssorting .oo-custom-select2.oo-custom-select2--multiple');
  const $singleSelectAdminSorting = $("#viewrecordssorting .oo-custom-select2.oo-custom-select2--single");

  // Init select2 for normal selects
  document.querySelectorAll(".custom-single-select, .custom-multiple-select").forEach(function (select) {
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
      $(select).select2({ width: '100%' });
    }
  });

  // Validation rules
  const rules = {
    email: node => /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(node.value),
    name: node => /^\s*[a-zA-Z0-9,\s]+\s*$/.test(node.value),
    text: node => node.value.trim().length > 0,
    checkbox: node => node.checked
  };

  function isValid(node) {
    if (!node || !node.dataset) {
      return false;
    }

    const ruleName = node.dataset.rule;
    return rules[ruleName] ? rules[ruleName](node) : node.checkValidity();
  }

  function validate(node) {
    const $node = $(node);
    const $formRow = $node.closest('label, .oo-form');
    const $error = $formRow.find('.error');

    const isTomSelectControl = $node.hasClass('ts-control');
    const targetNode = isTomSelectControl ? $formRow.find('select').get(0) : node;
    const valid = isValid(targetNode);

    const $nodeToMark = isTomSelectControl ? $node.closest('.ts-wrapper') : $node;
    $nodeToMark.attr('aria-invalid', !valid);

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

  // Form submit
  jQuery(document).on('submit', '.oo-form', function (e) {
    const $form = $(this);
    const isValidForm = validateForm($form);

    if (!isValidForm) {
      e.preventDefault();
      e.stopPropagation();

      const firstInvalid = $form.find('[aria-invalid="true"]').first()[0];
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
      }
      $form.find('[type="submit"]').prop('disabled', true);
    } else {
      $form.find('[type="submit"]').prop('disabled', false);
    }

    $form.addClass('oo-validated');
  });

  // Blur + input change validation
  jQuery(document).on('blur', '.oo-form [aria-invalid]', function () {
    validate(this);
  });

  jQuery(document).on('input change', '.oo-form [aria-invalid]', function () {
    validate(this);
  });

  window.validateForm = validateForm;

  // Init TomSelect
  document.querySelectorAll(".custom-single-select-tom, .custom-multiple-select-tom").forEach(function (select) {
    if (typeof TomSelect !== 'undefined') {

      let config = {
        hidePlaceholder: true,
        sortField: {
          field: "text",
          direction: "asc"
        },
        plugins: {
          remove_button: {
            title: 'Remove this item'
          }
        },
        onItemAdd: function () {
          this.setTextboxValue('');
          this.refreshOptions();
        },
        create: true,
        onBlur: function () {
          validate(this.input);
        },
        render: {
          option: function (data, escape) {
            return '<div class="d-flex"><span>' + escape(data.text) + '</span></div>';
          },
          item: function (data, escape) {
            if (this.items.length >= 2) {
              return '<div title="' + escape(data.text) + '">...</div>';
            } else {
              return '<div>' + escape(data.text) + '</div>';
            }
          }
        }
      };

      if (select.classList.contains("custom-multiple-select-tom")) {
        config.plugins.checkbox_options = {
          checkedClassNames: ['ts-checked'],
          uncheckedClassNames: ['ts-unchecked']
        };
      }

      new TomSelect(select, config);
    }
  });

  // Select2 admin selects
  if ($adminSelect.length > 0) {
    $adminSelect.select2({ width: '50%' });
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
