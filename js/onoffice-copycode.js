jQuery(document).ready(function($){
    $('#button-copy').click(function() {
        var copyCode = document.getElementsByTagName("code").item(0);
        var textCode = copyCode.innerText;
        /* Copy the text inside the text field */
        navigator.clipboard.writeText(textCode);
    });
});