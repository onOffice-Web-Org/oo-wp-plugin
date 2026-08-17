jQuery(document).ready(($) => {
    const thousandSeparator = onoffice_apply_thousand_separator.thousand_separator || '.';
    const decimalSeparator = onoffice_apply_thousand_separator.decimal_separator || ',';

    const cleanInputValue = (value) => {
        const sep = thousandSeparator === '.' ? '\\.' : thousandSeparator === "'" ? "'" : ',';
        const re = new RegExp(sep, 'g');
        return value.replace(re, '').replace(decimalSeparator, '.');
    };

    const stripNonNumeric = (value) => value.replace(/[^0-9.]/g, '');

    const formatNumber = (value) => {
        const parts = value.split('.');
        const intPart = parts[0];
        const decPart = parts.length > 1 ? decimalSeparator + parts.slice(1).join('') : '';
        const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        return formattedInt + decPart;
    };

    const applyThousandSeparatorFormat = (element) => {
        let inputName = $(element).attr('name');
        let inputValue = cleanInputValue($(element).val());
        inputValue = stripNonNumeric(inputValue);
        let formatted = formatNumber(inputValue);
        $(`input[name="${inputName}"]`).val(formatted);
    };

    if ($('.apply-thousand-separator-format').length) {
        $('.apply-thousand-separator-format').on('input', function() {
            applyThousandSeparatorFormat(this);
        }).each(function() {
            applyThousandSeparatorFormat(this);
        });
    }
});