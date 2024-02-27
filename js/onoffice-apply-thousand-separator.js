onoffice_setting = onoffice_setting || [];
jQuery(document).ready(($) => {
    const thousandSeparatorFormat = onoffice_setting.thousand_separator_format || '';

    const getSeparators = (separatorFormat) => ({
        thousandSeparator: separatorFormat === 'comma-separator' ? ',' : '.',
        decimalSeparator: separatorFormat === 'comma-separator' ? '.' : ','
    });

    const cleanInputValue = (value, separatorFormat) => {
        return separatorFormat === 'comma-separator' ? value.replace(/[^0-9.]/g, '') : value.replace(/[^0-9,]/g, '');
    };

    const processSeparator = (value, thousandSeparator, decimalSeparator) => {
        let parts = value.split(/[,.]/);
        if (parts.length > 2) {
            let integerPart = parts.shift();
            let decimalPart = parts.join('');
            value = integerPart + '.' + decimalPart;
        }

        let match = value.match(/^(\d+)[,.](\d+)$/);
        return match ? match[1].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator) + decimalSeparator + match[2] : value.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
    };

    const formatForThousandSeparator = (fieldKey, fieldValue, separatorFormat) => {
        const { thousandSeparator, decimalSeparator } = getSeparators(separatorFormat);
        let value = processSeparator(fieldValue, thousandSeparator, decimalSeparator);
        $(`input[name="${fieldKey}"]`).val(value);
    };

    const applyThousandSeparatorFormat = (element) => {
        let inputName = $(element).attr('name');
        let inputValue = cleanInputValue($(element).val(), thousandSeparatorFormat);
        if ($(element).data('step') === 1) {
            let numericValue = parseFloat(inputValue.replace(/,/g, '.'));
            if (!isNaN(numericValue)) {
                numericValue = Math.round(numericValue);
                inputValue = numericValue.toString();
            }
        }
        formatForThousandSeparator(inputName, inputValue, thousandSeparatorFormat);
    };

    const normalizeInputValue = () => {
        const { decimalSeparator } = getSeparators(thousandSeparatorFormat);
        $('.apply-thousand-separator-format').each(function() {
            let value = $(this).val().replace(new RegExp(`\\${decimalSeparator}$`), '');
            $(this).val(value);
        });
    };

    $('.apply-thousand-separator-format').on('blur', normalizeInputValue).on('input', function() {
        applyThousandSeparatorFormat(this);
    }).each(function() {
        applyThousandSeparatorFormat(this);
    });
});