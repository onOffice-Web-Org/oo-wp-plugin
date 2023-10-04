jQuery(document).ready(function ($) {
    const formElements = getFormsBySpecialHtmlAttribute();

    addHoneypotToForms(formElements);

    function getFormsBySpecialHtmlAttribute() {
        const formElements = [];
        const forms = document.querySelectorAll('.oo-form, #onoffice-form');

        forms.forEach(form => {
            const dataAttr = form.getAttribute('data-applicant-form-id');
            if (dataAttr === null) {
                formElements.push(form);
            }
        });

        return formElements;
    }

    function addHoneypotToForms(formElements) {
        formElements.forEach((formElement) => {
            const messageTextarea = $(formElement).find('textarea[name="message"]');
            const messageInput = $(formElement).find('input[name="message"]');
            if (messageTextarea.length === 1) {
                messageTextarea.attr('name', 'tmpField');
            } else if (messageInput.length === 1)  {
                messageInput.attr('name', 'tmpField');
            }
            const label = $('<label>').text('Message:').attr("class", "message");
            const input = $('<input>').attr({
                'type': 'text',
                'name': 'message',
                'class': 'message'
            });
            $(formElement).prepend(input);
            $(formElement).prepend(label);
            const originalInput = $(formElement).find('input[name="message"]');
            originalInput.before(label, input);
        });
    };
});