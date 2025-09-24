const custom_select2 = (typeof custom_select2_translation !== 'undefined')
  ? custom_select2_translation
  : {};

jQuery(document).ready(function ($) {

  const $adminSelect = $('.oo-poststuff .custom-input-field .oo-custom-select2');
  const $multiSelectAdminSorting = $('#viewrecordssorting .oo-custom-select2.oo-custom-select2--multiple');
  const $singleSelectAdminSorting = $("#viewrecordssorting .oo-custom-select2.oo-custom-select2--single");

  document.querySelectorAll(".custom-single-select, .custom-multiple-select").forEach(function (select) {
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
      $(select).select2({ width: '100%' });
    }
  });

    const forms = document.querySelectorAll('.oo-form');
  
    forms.forEach(function(form) {
      form.setAttribute('novalidate', '');
      const inputs = form.querySelectorAll('input, textarea');
      const selects = form.querySelectorAll('select');
  
      const showError = (input, message) => {
        const errorDiv = input.closest('label, div')?.querySelector('.error');
        if (errorDiv) {      
          $(errorDiv).css('display', 'block');
          errorDiv.setAttribute('aria-live', 'polite');
        }
      };
  
      const hideError = (input) => {
        const errorDiv = input.closest('label, div')?.querySelector('.error');
        if (errorDiv) {
          errorDiv.textContent = '';
          errorDiv.classList.remove('is-visible');
          errorDiv.removeAttribute('aria-live');
        }
      };
  
      const inputHandleBlur = (input) => {
        if (!input.checkValidity()) {
          showError(input);
        } else {
          hideError(input);
        }
      }
  
      inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
          inputHandleBlur(input)
        });
  
        input.addEventListener('input', function() {
          if (input.checkValidity()) {
            hideError(input);
          }
        });
      });

      const selectHandleChange = (select) => {
        const tomSelectControl = select.nextElementSibling;
        if (!select.checkValidity()) {
            tomSelectControl.classList.add('is-invalid');
            showError(select);
        } else {
            tomSelectControl.classList.remove('is-invalid');
            hideError(select);
        }
    }

    const jumpToFirstInvalidInput = (form) => {
      const firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus({ preventScroll: true });
      }
  }
    selects.forEach(select => {
      select.addEventListener('change', function() {
        selectHandleChange(select)
      });
    });

    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          inputs.forEach(input => {
              inputHandleBlur(input)
          });
          selects.forEach(select => {
              selectHandleChange(select)
          })
          jumpToFirstInvalidInput(form);
      }
      form.classList.add('validated');
  });
  });


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