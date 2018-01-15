jQuery(document).ready(function($){
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

	$('.sortable-item').mouseover(function() {
		$(this).find('.sort-arrows').stop(true, true).show();
	});
	$('.sortable-item').mouseout(function() {
		$(this).find('.sort-arrows').stop(true, true).hide();
	});

	$('.filter-fields-list').sortable({
		axis: 'y'
	});

	$('.property-detail-items-list').sortable({
		axis: 'y'
	});

	$('.agent-detail-items-list').sortable({
		axis: 'y'
	});

	$('.inputFieldCheckboxButton').click(function() {
	   getCheckedFields(this);
	});

	$('.item-edit').click(function() {
		$(this).parent().parent().parent().parent().find('.menu-item-settings').toggle();
	});

	$('.item-delete-link').click(function() {
		$(this).parent().parent().remove();
	});

	var getCheckedFields = function(but) {
		var label = $(but).attr('data-action-div');
		var categoryShort = but.name;
		var category = $(but).attr('data-onoffice-category');
		var checkedFields = [];
		var inputConfigFields = $('#' + categoryShort).find('input.onoffice-possible-input:checked');
		var ulEl = $('#sortableFieldsList');

		$(inputConfigFields).each(function(index) {
			var valElName = $(this).val();
			var valElLabel = $(this).next().text();
			var myLi = myLi = ulEl.find('#menu-item-' + valElName);
			var module = $(this).attr('data-onoffice-module');
			if (myLi.length === 0) {
				createNewFieldItem(valElName, valElLabel, category, module, label);
			}
		});

		return checkedFields;
	};

	var createNewFieldItem = function(fieldName, fieldLabel, fieldCategory, module, label) {
		var myLabel = $('#' + label);

		if (myLabel.length)
		{
			var myDummyKey = myLabel.find('#menu-item-dummy_key');
		}
		else
		{
			var myDummyKey = $('#menu-item-dummy_key');
		}

		var clonedElement = myDummyKey.clone(true, true);
		clonedElement.attr('id', 'menu-item-'+fieldName);
		clonedElement.find('span.item-title:contains("dummy_label")').text(fieldLabel);
		clonedElement.find('span.item-type:contains("dummy_category")').text(fieldCategory);
		clonedElement.find('input[value=dummy_key]').val(fieldName);
		clonedElement.find('input[value=dummy_label]').val(fieldLabel);
		clonedElement.find('span.menu-item-settings-name').text(fieldName);
		clonedElement.find('input[data-onoffice-ignore=true]').removeAttr('data-onoffice-ignore');

		if (module) {
			var inputModule = clonedElement.find('input[name^=oopluginformfieldconfig-module]');
			inputModule.val(module);
			var labelIdFor = inputModule.attr('id');
			var moduleStr = onOffice_loc_settings.modulelabels[module];
			var newLabelText = onOffice_loc_settings.fieldmodule.replace('%s', moduleStr);
			clonedElement.find('label[for=' + labelIdFor + ']').text(newLabelText);
		}
		clonedElement.show();
		myDummyKey.parent().append(clonedElement);
	};
});