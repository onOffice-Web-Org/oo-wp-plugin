jQuery(document).ready(function ($) {
    const formClassList = ['owner', 'applicant', 'applicantsearch', 'default'];
    const selectedForms = [];

    for (const formClass of formClassList) {
        const formSelector = '.' + formClass;
        const forms = document.querySelectorAll(formSelector);
        selectedForms.push(...forms);
    }

    if (!selectedForms.length) {
        const formById = document.querySelectorAll('#onoffice-form');
        selectedForms.push(...formById);
    }

    selectedForms.forEach(formElement => {
        $(formElement).on('submit', function (event) {
            if ($(formElement).data('submitted')) {
                event.preventDefault();
                return;
            }
            disableFormSubmission($(formElement));
        });
    });

    function disableFormSubmission(form) {
        form.data('submitted', true);
    }
});
