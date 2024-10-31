jQuery(document).ready(function($){
	$(document).on('click', '.button-copy', function (event) {
		var text = $(event.target).attr('data-clipboard-text');
		navigator.clipboard.writeText(text)
	})
});