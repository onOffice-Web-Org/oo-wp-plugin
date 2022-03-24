jQuery(document).ready(function($) {
    var url = window.location.href;
    if ( url.search('onoffice-settings') != -1 && document.querySelector('.notice-warning') !== null )
    {
        console.log('page setting');
        $('.notice-warning').css('display','none');
    }
});