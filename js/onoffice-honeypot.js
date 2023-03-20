jQuery(document).ready(function ($) {
    if (honeypot_enabled['honeypotValue'] == true && form['type'] !== 'applicantsearch') {
        var messageTextarea = $('textarea[name="message"]');
        var messageInput = $('input[name="message"]');
        if(messageTextarea.val() == ''){
            messageTextarea.attr('name', 'tmpField');
        } else if (messageInput.val() == '')  {
            messageInput.attr('name', 'tmpField');
        }
        var label = $('<label>').text('Message:').attr("class", "message");
        var input = $('<input>').attr({
            'type': 'text',
            'name': 'message',
            'class': 'message'
        });
        $("#onoffice-form").prepend(input);
        $("#onoffice-form").prepend(label);
        var originalInput = $('input[name="message"]');
        originalInput.before(label, input);
    }
});