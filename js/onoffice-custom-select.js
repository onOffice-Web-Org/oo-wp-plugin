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


  document.querySelectorAll(".custom-single-select-tom, .custom-multiple-select-tom").forEach(function (select) {
    if (typeof TomSelect !== 'undefined') {

      let config = {
        hidePlaceholder: true,
        maxOptions: null,
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


document.addEventListener('DOMContentLoaded', function () {

  const forms = document.querySelectorAll('.oo-form');

  forms.forEach(function (form) {
    form.setAttribute('novalidate', '');
    const inputs = form.querySelectorAll('input, textarea');
    const selects = form.querySelectorAll('select');
    const submitInput = form.querySelector('input[type=submit]');
    const forwardLinks = form.querySelectorAll('.leadform-forward');

    const showError = (input) => {
      const errorDiv = input.parentElement.querySelector('.error') || input.closest('label')?.querySelector('.error');
  
      if (errorDiv) {
        errorDiv.style.display = 'block';
        errorDiv.setAttribute('aria-hidden', 'false'); 
        errorDiv.setAttribute('aria-live', 'polite');
        errorDiv.setAttribute('role', 'alert');
        input.setAttribute('aria-invalid', 'true');
        
      }
    };
    
    const hideError = (input) => {
      const errorDiv = input.parentElement.querySelector('.error') || input.closest('label')?.querySelector('.error');
                       
      if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.setAttribute('aria-hidden', 'true');
        errorDiv.removeAttribute('aria-live');
        
        input.setAttribute('aria-invalid', 'false');
      }
    };

    const inputHandleBlur = (input) => {
      if (!input.checkValidity()) {
        showError(input);
      } else {
        hideError(input);
      }
    };

    const selectHandleChange = (select) => {
      const tomSelectControl = select.nextElementSibling;
      if (!select.checkValidity()) {
        if (tomSelectControl) tomSelectControl.classList.add('is-invalid');
        showError(select);
      } else {
        if (tomSelectControl) tomSelectControl.classList.remove('is-invalid');
        hideError(select);
      }
    };
    const jumpToFirstInvalidInput = (form) => {
      const firstInvalid = Array.from(form.querySelectorAll(':invalid'))
        .find(el => el.offsetWidth > 0 || el.offsetHeight > 0);
      
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
      }
    };
  
    const toggleSubmitButton = () => {
      if (submitInput && form.classList.contains('validated')) {
        submitInput.disabled = !form.checkValidity();
      }
    };
  
    forwardLinks.forEach(link => {
      link.addEventListener('click', function (event) {
        form.classList.add('validated');
        const visibleFields = Array.from(form.querySelectorAll('input, textarea, select'))
          .filter(el => el.offsetWidth > 0 || el.offsetHeight > 0);
  
        let stepIsValid = true;
        visibleFields.forEach(field => {
          if (!field.checkValidity()) {
            stepIsValid = false;
            if (field.tagName === 'SELECT') {
              selectHandleChange(field);
            } else {
              inputHandleBlur(field);
            }
          }
        });
  
        if (!stepIsValid) {
          event.preventDefault();
          event.stopImmediatePropagation();
          jumpToFirstInvalidInput(form);
        }
      });
    });
  
    inputs.forEach(function (input) {
      input.addEventListener('blur', function () {
        inputHandleBlur(input);
        toggleSubmitButton();
      });

      input.addEventListener('input', function () {
        if (input.checkValidity()) {
          hideError(input);
        }
        toggleSubmitButton();
      });
    });

    selects.forEach(select => {
      select.addEventListener('change', function () {
        selectHandleChange(select);
        toggleSubmitButton();
      });
    });
    
    if (submitInput) submitInput.disabled = false;
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopImmediatePropagation();
        inputs.forEach(input => inputHandleBlur(input));
        selects.forEach(select => selectHandleChange(select));
        jumpToFirstInvalidInput(form);
      } else {
        if (submitInput) {
          submitInput.disabled = true;
        }
      }
      form.classList.add('validated');
      toggleSubmitButton();
    });
  });
});