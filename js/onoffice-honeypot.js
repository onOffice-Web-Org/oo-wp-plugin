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
            var messageTextarea = $(formElement).find('textarea[name="message"]');
            var messageInput = $(formElement).find('input[name="message"]');
            if (messageTextarea.length === 1) {
                messageTextarea.attr('name', 'tmpField');
            } else if (messageInput.length === 1)  {
                messageInput.attr('name', 'tmpField');
            }
            var label = $('<label>').text('Message:').attr("class", "message");
            var input = $('<input>').attr({
                'type': 'text',
                'name': 'message',
                'class': 'message'
            });
            $(formElement).prepend(input);
            $(formElement).prepend(label);
            var originalInput = $(formElement).find('input[name="message"]');
            originalInput.before(label, input);
        });
    };
});