jQuery(document).ready(function ($) {
    if (postTitles['tmpField'] == true && form['type'] !== 'applicantsearch') {
        var message = $('textarea[name="message"]');
        var newInput = $('<textarea>').attr({
            'type': 'text',
            'name': 'tmpField',
        });
        message.replaceWith(newInput);
        var label = $('<label>').text('Message:').attr("class", "honeypot-message");
        var input = $('<input>').attr({
            'type': 'text',
            'name': 'message',
            'class': 'honeypot-message'
        });
        $("#onoffice-form").prepend(input);
        $("#onoffice-form").prepend(label);
        var originalInput = $('input[name="message"]');
        originalInput.before(label, input);
    }
});