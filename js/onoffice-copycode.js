jQuery(document).ready(function($){
	$('#button-copy').on('click', function (event) {
		var text = $(event.target).attr('data-clipboard-text');
		navigator.clipboard.writeText(text)
	})
});