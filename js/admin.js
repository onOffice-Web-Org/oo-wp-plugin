jQuery(document).ready(function($){
	$(document).on('click', '.notice-save-view.notice-dismiss', function () {
		$(this).parent().remove();
	});
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
		axis: 'y',
		handle: '.menu-item-bar',
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

	$('.inputFieldButton').click(function() {
		getCheckedFieldButton(this);
	});

	$('.item-edit').click(function() {
		$(this).parent().parent().parent().parent().find('.menu-item-settings').toggle();
	});

	$('.item-delete-link').click(function() {
		var labelButtonHandleField= $(this).parent().parent().attr('action-field-name');
		var data = document.querySelector("."+labelButtonHandleField);

		data.classList.remove("dashicons-remove");
		data.classList.add("dashicons-insert");
		$(data).attr('typeField', 1);
		$(this).parent().parent().remove();
	});
	var getCheckedFieldButton = function(btn) {
		var addField= 1;
		var removeField= 2;
		var checkTypeField = $(btn).attr('typeField');
		if(checkTypeField == addField){
			btn.classList.remove("dashicons-insert");
			btn.classList.add("dashicons-remove");
			var label = $(btn).attr('data-action-div');
			var valElName = $(btn).attr('value');
			var valElLabel = $(btn).next().text();
			var category = $(btn).attr('data-onoffice-category');
			var module = $(btn).attr('data-onoffice-module');
			var actionFieldName = 'labelButtonHandleField-'+valElName;

			$(btn).attr('typeField', removeField);
			var optionsAvailable = false;
			var checkedFields = [];

			if ($(btn).attr('onoffice-multipleSelectType')) {
				optionsAvailable = $(btn).attr('onoffice-multipleSelectType') === '1';
			}

			var clonedItem = createNewFieldItem(valElName, valElLabel, category, module, label, optionsAvailable, actionFieldName);

			var event = new CustomEvent('addFieldItem', {
				detail: {
					fieldname: valElName,
					fieldlabel: valElLabel,
					category,
					module,
					item: clonedItem
				}
			});
			document.dispatchEvent(event);
		} else {
			var valElName = $(btn).attr('value');
			var checkedFields = [];

			btn.classList.remove("dashicons-remove");
			btn.classList.add("dashicons-insert");
			$(btn).attr('typeField', addField);
			$('*#sortableFieldsList').find('#menu-item-'+valElName).remove();
		}

		return checkedFields;
	};
	var getCheckedFields = function(but) {
		var label = $(but).attr('data-action-div');
		var categoryShort = but.name;
		var category = $(but).attr('data-onoffice-category');
		var checkedFields = [];
		var inputConfigFields = $('#' + categoryShort).find('input.onoffice-possible-input:checked');

		$(inputConfigFields).each(function(index) {
			var valElName = $(this).val();
			var valElLabel = $(this).next().text();
			var module = $(this).attr('data-onoffice-module');

			var optionsAvailable = false;

			if ($(this).attr('onoffice-multipleSelectType')) {
				optionsAvailable = $(this).attr('onoffice-multipleSelectType') === '1';
			}

			var attachField = false;

			if ($(".attachSortableFieldsList").length == 1) {
				if ($('#sortableFieldsList').find('#menu-item-' + valElName).length === 0) {
					attachField = true;
				}
			}
			else {
				//this case is for estate detail view
				var detailViewDivId='actionForestate';
				if (categoryShort.startsWith('address')) {
					detailViewDivId = 'actionForaddress';
				}
				if ($('#'+detailViewDivId).find('#sortableFieldsList').find('#menu-item-' + valElName).length === 0) {
					attachField = true;
				}
			}

			if (attachField) {
				var clonedItem = createNewFieldItem(valElName, valElLabel, category, module, label, optionsAvailable);
				var event = new CustomEvent('addFieldItem', {
					detail: {
						fieldname: valElName,
						fieldlabel: valElLabel,
						category,
						module,
						item: clonedItem
					}
				});
				document.dispatchEvent(event);
			}
		});

		return checkedFields;
	};

	var createNewFieldItem = function(fieldName, fieldLabel, fieldCategory, module, label, optionsAvailable, actionFieldName) {
		var myLabel = label ? $('#' + label) : {};
		var dummyKey;

		if (myLabel.length) {
			dummyKey = myLabel.find('#menu-item-dummy_key');
		} else {
			dummyKey = $('#menu-item-dummy_key');
		}

		var clonedElement = dummyKey.clone(true, true);

		clonedElement.attr('id', 'menu-item-'+fieldName);
		clonedElement.attr('action-field-name', actionFieldName);
		clonedElement.find('span.item-title:contains("dummy_label")').text(fieldLabel);
		clonedElement.find('span.item-type:contains("dummy_category")').text(fieldCategory);
		clonedElement.find('input[value=dummy_key]').val(fieldName);
		clonedElement.find('input[name*=dummy_key]').attr('name', function (index, name) {
			return name.replace('dummy_key', fieldName);
		});
		clonedElement.find('input[value=dummy_label]').val(fieldLabel);
		clonedElement.find('span.menu-item-settings-name').text(fieldName);
		clonedElement.find('input[data-onoffice-ignore=true]').removeAttr('data-onoffice-ignore');
		clonedElement.find('[name^=exclude]').attr('name', function(index, name) {
			return name.replace('exclude', '');
		})

		if (!optionsAvailable) {
            var selectors = ['oopluginformfieldconfig-availableOptions', 'oopluginfieldconfig-availableOptions'];
			var availableOptionEl = clonedElement.find('input[name^=' + selectors.join('],input[name^=') + ']');
			availableOptionEl.parent().remove();
		}

		if (module) {
			var inputModule = clonedElement.find('input[name^=oopluginformfieldconfig-module]');
			inputModule.val(module);
			var labelIdFor = inputModule.attr('id');
			var moduleStr = onOffice_loc_settings.modulelabels[module];
			var newLabelText = onOffice_loc_settings.fieldmodule.replace('%s', moduleStr);
			clonedElement.find('label[for=' + labelIdFor + ']').text(newLabelText);
		}

		if (onOffice !== undefined && onOffice.checkboxAdmin) {
			var cbAdmin = new onOffice.checkboxAdmin();
			cbAdmin.changeCbStatus(clonedElement);
		}
		clonedElement.show();
		dummyKey.parent().append(clonedElement);
        return clonedElement[0];
	};
});


(function($) {
	var refreshTemplateMouseOver = function() {
		var value = templateSelector.find('option:selected').text();
		templateSelector.attr('title', value);
	};

	var templateNameAttrs = [
		'oopluginforms-template',
		'oopluginlistviews-template',
		'oopluginlistviewsaddress-template',
		'onoffice-template'
	];

	var templateSelectorStr = 'select.onoffice-input[name=' +
		templateNameAttrs.join('], select.onoffice-input[name=') + ']';

	var templateSelector = $(templateSelectorStr).first();
	templateSelector.on('change', refreshTemplateMouseOver);
	refreshTemplateMouseOver();
})(jQuery);
