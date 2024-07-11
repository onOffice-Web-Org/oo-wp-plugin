jQuery(document).ready(($) => {
    const thousandSeparatorFormat = onoffice_apply_thousand_separator.thousand_separator_format || '';

    const getSeparators = (separatorFormat) => ({
        thousandSeparator: separatorFormat === 'comma-separator' ? ',' : '.',
    });

    const cleanInputValue = (value) => value.replace(/[^0-9]/g, '');

    const processSeparator = (value, thousandSeparator) => {
        value = value.replace(/[,.]/g, '');
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
    };

    const formatForThousandSeparator = (fieldKey, fieldValue, separatorFormat) => {
        const { thousandSeparator } = getSeparators(separatorFormat);
        let value = processSeparator(fieldValue, thousandSeparator);
        $(`input[name="${fieldKey}"]`).val(value);
    };

    const applyThousandSeparatorFormat = (element) => {
        let inputName = $(element).attr('name');
        let inputValue = cleanInputValue($(element).val());
        formatForThousandSeparator(inputName, inputValue, thousandSeparatorFormat);
    };

    if ($('.apply-thousand-separator-format').length) {
        $('.apply-thousand-separator-format').on('input', function() {
            applyThousandSeparatorFormat(this);
        }).each(function() {
            applyThousandSeparatorFormat(this);
        });
    }
});