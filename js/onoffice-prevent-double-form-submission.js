jQuery(document).ready(function ($) {
    const formElements = document.querySelectorAll('.oo-form, #onoffice-form');

    handleFormSubmissions(formElements);

    function handleFormSubmissions(formElements) {
        formElements.forEach((formElement) => {
            let submitted = false;
            const submitButton = $(formElement).find('.submit_button');
            const submitInput = $(formElement).find('input[type=submit]');

            $(formElement).on('submit', function (event) {
                if (submitted) {
                    event.preventDefault();
                } else {
                    submitted = true;
                }
                submitInput.prop('disabled', true);
            });

            if ($(submitButton).is(':visible')) {
                submitButton.on('click', function () {
                    submitButton.prop('disabled', true);
                    submitButton.addClass('onoffice-unclickable-form');
                });
            }
        });
    }
});
