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

	$(document).ready(function(){
		$('.inputFieldCheckboxButton').click(function(){
		   getCheckedFields(this);
		});
	});


	function getCheckedFields(but)
	{
		var category = but.name;
		var checkedFields = [];

		var inputConfigFields = $('#'+category).find('input.onoffice-possible-input:checked');
		var ulEl = $('#sortableFieldsList');
		//1.testen, ob das element existiert
		//2. falls nicht, dann anlegen

		$(inputConfigFields).each(function(index){
			var valElName = $(this).val();
			var valElLabel = $(this).next().text();
			var myLi = null;
			myLi = ulEl.find('#menu-item-'+valElName);

			if(!(myLi.length > 0))
			{
				console.log(valElName + '  ' + myLi.length + '  ' + valElLabel);
				createNewFieldItem(valElName, valElLabel);
			}
		});

		return checkedFields;
	}

	function createNewFieldItem(fieldName, fieldLabel)
	{
		var clonedElement = $('#menu-item-dummyField').clone(true, true);

		clonedElement.attr('id', 'menu-item-'+fieldName);
		clonedElement.find('span:first').text(fieldLabel);
		clonedElement.find('span:first').next().next().next().val(fieldLabel);
		clonedElement.find('span:first').next().next().next().next().val(fieldName);
		clonedElement.find('.menu-item-settings-name').text(fieldName);
		clonedElement.find('.onoffice-dummy-input').attr('value', fieldName);
		clonedElement.find('.onoffice-dummy-input').attr('class', 'onoffice-input');
		clonedElement.show();
		$('#menu-item-dummyField').parent().append(clonedElement);
	}

	$(document).ready(function(){
		$('.item-edit-link').click(function(){
			$(this).parent().parent().parent().find('.menu-item-settings').toggle();
		});
	});

	$(document).ready(function(){
		$('.item-delete-link').click(function(){
			$(this).parent().parent().remove();
		});
	});
});