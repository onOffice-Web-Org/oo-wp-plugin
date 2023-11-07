jQuery(document).ready(function ($) {
    const formElements = document.querySelectorAll('.oo-form, #onoffice-form');

    handleFormSubmissions(formElements);

    function handleFormSubmissions(formElements) {
        formElements.forEach((formElement) => {
            let submitted = false;
            const submitInput = $(formElement).find('input[type=submit]');

            $(formElement).on('submit', function (event) {
                if (submitted) {
                    event.preventDefault();
                } else {
                    submitted = true;
                }
                submitInput.prop('disabled', true);
            });
        });
    }
});
