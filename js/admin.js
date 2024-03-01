jQuery(document).ready(function($){
	function toggleViewForwardingPage() {
		var selectedTemplateInListView = $('.oo-poststuff input[name="oopluginlistviews-template"]:checked').val();
		if ($('#viewforwardingpage').length == 1) {
			if (selectedTemplateInListView.includes("SearchForm.php")) {
				$('#viewforwardingpage').show();
			} else {
				$('#viewforwardingpage').hide();
				$('select[name="oopluginlistviews-forwardingPage"]').val('');
			}
		}
	}

	toggleViewForwardingPage();

	$('.oo-poststuff input[name="oopluginlistviews-template"]').change(function() {
		toggleViewForwardingPage();
	});
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
		const data = $('.' + labelButtonHandleField);
		if(data === null){
			$(this).parent().parent().remove();
			return;
		}
		$(data).each(function() {
			const parentItem = $(this);
			parentItem.find('.dashicons').removeClass('dashicons-remove').addClass('dashicons-insert').attr('typeField', 1);
			parentItem.find('.check-action').removeClass('action-remove');
			parentItem.find('.field-item-detail').css('opacity', '1');
		});
		$(this).parent().parent().remove();
	});

	$('.oo-search-field .input-search').on('input', function() {
		let filter = $(this).val().toUpperCase();
		let clearIcon = $('.clear-icon');

		if ($(this).val().trim() !== '') {
			clearIcon.removeClass('dashicons-search').addClass('dashicons-no-alt');
		} else {
			clearIcon.removeClass('dashicons-no-alt').addClass('dashicons-search');
		}

		$('.oo-search-field .field-lists .search-field-item').each(function() {
			let dataLabel = $(this).data('label').toUpperCase();
			let dataKey = $(this).data('key').toUpperCase();
			let dataContent = $(this).data('content').toUpperCase();

			if (dataLabel.indexOf(filter) > -1 || dataKey.indexOf(filter) > -1 || dataContent.indexOf(filter) > -1) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}).on('keypress', function(event) {
		if (event.which == 13) {
			event.preventDefault();
		}
	});

	$(document).on('click', function(event) {
		let $fieldLists = $('.oo-search-field .field-lists');
		let $inputSearch = $('.oo-search-field .input-search');

		if (!$(event.target).closest($fieldLists).length && event.target !== $inputSearch[0]) {
			$fieldLists.hide();
		}
	});

	$('.oo-search-field .input-search').on('click', function(event) {
		event.stopPropagation();
		$('.field-lists').show();
	});

	$('.oo-search-field #clear-input').on('click', function() {
		$('.input-search').val('').trigger('input');
	});

	var getCheckedFieldButton = function(btn) {
		var addField = 1;
		var removeField = 2;
		var checkTypeField = $(btn).children().attr('typeField');
		if (checkTypeField == addField) {
			var label = $(btn).attr('data-action-div');
			var valElName = $(btn).attr('value');
			const valElLabel = $(btn).attr('data-onoffice-label');
			var category = $(btn).attr('data-onoffice-category');
			var module = $(btn).attr('data-onoffice-module');
			var actionFieldName = 'labelButtonHandleField-' + valElName;
			$('.' + actionFieldName).each(function() {
				const parentItem = $(this);
				parentItem.find('.dashicons').removeClass('dashicons-insert').addClass('dashicons-remove').attr('typeField', removeField);
				parentItem.find('.check-action').addClass('action-remove');
				parentItem.find('.field-item-detail').css('opacity', '0.5');
			});
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
			const actionFieldName = 'labelButtonHandleField-' + valElName;
			$('.' + actionFieldName).each(function() {
				const parentItem = $(this);
				parentItem.find('.dashicons').removeClass('dashicons-remove').addClass('dashicons-insert').attr('typeField', addField);
				parentItem.find('.check-action').removeClass('action-remove');
				parentItem.find('.field-item-detail').css('opacity', '1');
			});
			$('*#sortableFieldsList').find('#menu-item-' + valElName).remove();
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

		if(onOffice_loc_settings.modulelabels && module){
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
