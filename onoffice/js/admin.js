jQuery(document).ready(function($){

	/********************************************/
	/* AJAX SAVE FORM */
	/********************************************/
	$(document).ready(function() {
	   $('#theme-options-form').submit(function() { 
	      $(this).ajaxSubmit({
	      	 onLoading: $('.loader').show(),
	         success: function(){
	         	$('.loader').hide();
	            $('#save-result').fadeIn();
	            setTimeout(function() {
				    $('#save-result').fadeOut('fast');
				}, 2000);
	         }, 
	         timeout: 5000
	      }); 
	      return false; 
	   });
	});
	
	/********************************************/
	/* SORTABLE FILTER FIELDS */
	/********************************************/
	$('.sortable-item').mouseover(function() {
		$(this).find('.sort-arrows').stop(true, true).show();
	});
	$('.sortable-item').mouseout(function() {
		$(this).find('.sort-arrows').stop(true, true).hide();
	});
	
	$(document).ready(function () {
		$('.filter-fields-list').sortable({
			axis: 'y',
			curosr: 'move'
		});
	});

	$(document).ready(function () {
		$('.property-detail-items-list').sortable({
			axis: 'y',
			curosr: 'move'
		});
	});

	$(document).ready(function () {
		$('.agent-detail-items-list').sortable({
			axis: 'y',
			curosr: 'move'
		});
	});

});