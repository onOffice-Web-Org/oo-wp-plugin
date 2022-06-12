jQuery(document).ready(function($){
	if (navigator.clipboard) {
		$('.button-copy').show();
	}
	$(document).on('click', '.button-copy', function (event) {
		var text = $(event.target).attr('data-clipboard-text');
		navigator.clipboard.writeText(text)
	})
});