jQuery(document).ready(function($){
	$('.oo-form [name="Email"], #onoffice-form [name="Email"]').keyup(function() {
		const oldVal = $(this).val();
		const sanitizedVal = oldVal.replace(/\s/g, '');
		$(this).val(sanitizedVal);
	});
});