var form = form || {};
form.type = form.type || '';
jQuery(document).ready(function ($) {
    if (form.type !== 'applicantsearch') {
        const formClassList = ['owner', 'applicant', 'default'];
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
    }
});