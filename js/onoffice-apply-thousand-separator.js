onoffice_setting = onoffice_setting || [];
jQuery(document).ready(function ($) {
    let thousandSeparatorFormat = onoffice_setting.thousand_separator_format || '';

    function formatForThousandSeparator(fieldKey, fieldValue, separatorFormat) {
        let thousandSeparator = separatorFormat === 'comma-separator' ? ',' : '.';
        let decimalSeparator = separatorFormat === 'comma-separator' ? '.' : ',';
        let value = processSeparator(fieldValue, thousandSeparator, decimalSeparator);

        $('input[name="' + fieldKey + '"]').val(value);
    }

    function processSeparator(value, thousandSeparator , decimalSeparator) {
        let match = value.match(/^(\d+)[,.](\d+)$/);
        if (match) {
            return match[1].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator) + decimalSeparator + match[2];
        } else {
            return value.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        }
    }

    $('.apply-thousand-separator-format').on('input', function() {
        let inputName = $(this).attr('name');
        let inputValue = $(this).val();
        inputValue = thousandSeparatorFormat === 'comma-separator' ? inputValue.replace(/[^0-9.]/g, '') : inputValue.replace(/[^0-9,]/g, '');
        formatForThousandSeparator(inputName, inputValue, thousandSeparatorFormat);
    }).each(function() {
        let inputName = $(this).attr('name');
        let inputValue = $(this).val();
        inputValue = thousandSeparatorFormat === 'comma-separator' ? inputValue.replace(/[^0-9.]/g, '') : inputValue.replace(/[^0-9,]/g, '');
        formatForThousandSeparator(inputName, inputValue, thousandSeparatorFormat);
    });
});