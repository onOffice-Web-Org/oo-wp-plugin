jQuery(document).ready(function ($) {
    const formElements = getFormsBySpecialHtmlAttribute();

    handleFormSubmissions(formElements);

    function getFormsBySpecialHtmlAttribute() {
        const formElements = [];

        const forms = document.querySelectorAll('.oo-form, #onoffice-form');
        formElements.push(...forms);

        return formElements;
    }

    function handleFormSubmissions(formElements) {
        formElements.forEach((formElement) => {
            let submitted = false;
            const submitButton = $(formElement).find('.submit_button');

            $(formElement).on('submit', function (event) {
                if (submitted) {
                    event.preventDefault();
                } else {
                    submitted = true;
                }
            });

            submitButton.on('click', function () {
                submitButton.prop('disabled', true);
            });
        });
    }
});
