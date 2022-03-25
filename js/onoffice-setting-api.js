jQuery(document).ready(function($) {
    var url = window.location.href;
    var page = url.split('?')[1];
    if ( page && page.search('onoffice-settings') != -1 && document.querySelector('.notice-warning') !== null )
    {
        $('.notice-warning').css('display','none');
    }
});