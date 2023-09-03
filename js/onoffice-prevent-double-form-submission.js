jQuery(document).ready(function ($) {
    const formClassList = ['owner', 'applicant', 'applicantsearch', 'default'];
    const selectedForms = getSelectedForms(formClassList);

    handleFormSubmissions(selectedForms);

    function getSelectedForms(formClassList) {
        const selectedForms = [];
        formClassList.forEach((formClass) => {
            const formSelector = `.${formClass}`;
            const forms = document.querySelectorAll(formSelector);
            selectedForms.push(...forms);
        });
        if (!selectedForms.length) {
            const formById = document.querySelectorAll('#onoffice-form');
            selectedForms.push(...formById);
        }
        return selectedForms;
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
