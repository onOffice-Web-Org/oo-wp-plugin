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

	$(document).on('click', '.item-edit', function() {
		$(this).parent().parent().parent().parent().find('.menu-item-settings').toggle();
	});

	$(".sortable-tags").sortable({
		update: function() {
			var sortedKeys = [];
			$(".sortable-tags .sortable-tag").each(function() {
				sortedKeys.push($(this).data("key"));
			});
			$(".hidden-sortable-tags").val(sortedKeys);
		}
	});

	$(document).on('click', '.item-delete-link', function() {
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
		document.dispatchEvent(new CustomEvent('fieldListUpdated'));
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

	let isMultiplePages = $('#multi-page-container').is(':visible');

	const FormMultiPageManager = {
		maxClicks: 6,
		clickCount: $('.list-fields-for-each-page').length,

		init: function() {
			this.checkTemplate();
			this.initializeDraggable();
			this.initializeSortable();
			this.initializeDroppable();
			this.checkSortableFieldsList();
			this.bindEvents();
		},

		checkTemplate: function() {
			if ($('#multi-page-container').is(':visible')) {
				$('#single-page-container').find('input, select, textarea').prop('disabled', true);
				$('#multi-page-container ul').find('input, select, textarea').prop('disabled', false);
			} else {
				$('#single-page-container').find('input, select, textarea').prop('disabled', false);
				$('#multi-page-container ul').find('input, select, textarea').prop('disabled', true);
			}
		},

		initializeSortable: function() {
			$('.multi-page-list').sortable({
				connectWith: ".filter-fields-list",
				handle: ".menu-item-bar",
				placeholder: "ui-sortable-placeholder",
				stop: function(event, ui) {
					FormMultiPageManager.reorderPages();
				}
			}).disableSelection();
		},

		initializeDroppable: function() {
			if ($('.filter-fields-list').length) {
				$('.filter-fields-list').droppable({
					accept: '.inputFieldButton',
					hoverClass: 'ui-droppable-hover',
					drop: function(event, ui) {
						const field = ui.helper.clone();
						const classList = $(this).attr('class').split(/\s+/);
						const id = classList.find(cls => cls.startsWith('fieldsListPage'));
						const output = id ? id.split('-')[1] : '1';
						getCheckedFieldButton(field, output);
					}
				});
			}
		},

		initializeDraggable: function() {
			if ($('.filter-fields-list').length) {
				$('.inputFieldButton').draggable({
					helper: 'clone',
					revert: 'invalid',
					appendTo: 'body',
					zIndex: 1000
				});
			}
		},

		checkSortableFieldsList: function() {
			if ($('#multi-page-container').is(':visible')) {
				if ($('.list-fields-for-each-page').length >= this.maxClicks) {
					$('.add-page-button').hide();
				} else {
					$('.add-page-button').show();
				}
				if ($('.list-fields-for-each-page').length <= 1) {
					$('.item-remove-page-link').hide();
				} else {
					$('.item-remove-page-link').show();
				}
			}
		},

		bindEvents: function() {
			$('.add-page-button').on('click', function() {
				if (FormMultiPageManager.clickCount < FormMultiPageManager.maxClicks) {
					FormMultiPageManager.addNewPage();
					FormMultiPageManager.initializeSortable();
					FormMultiPageManager.initializeDroppable();
					FormMultiPageManager.reorderPages();
					FormMultiPageManager.checkSortableFieldsList();
					FormMultiPageManager.clickCount++;
				}
			});

			$(document).on('click', '.item-remove-page-link', function() {
				if ($('.list-fields-for-each-page').length > 1) {
					const page = $(this).parent().parent();
					page.find('.sortable-item').each(function() {
						const actionFieldName = $(this).attr('action-field-name');
						const valElName = actionFieldName ? actionFieldName.replace('labelButtonHandleField-', '') : '';
						if (valElName && valElName !== 'dummy_key') {
							FormMultiPageManager.resetFieldState(actionFieldName, valElName);
						}
					});
					page.remove();
					FormMultiPageManager.clickCount--;
					FormMultiPageManager.reorderPages();
					FormMultiPageManager.checkSortableFieldsList();
				}
			});
		},

		addNewPage: function() {
			const newPageNumber = this.clickCount + 1;
			const newPage = this.createNewPage(newPageNumber);
			$('#multi-page-container').append(newPage);
			this.initializeSortable();
			this.checkTemplate();
		},

		createNewPage: function(pageNumber) {
			const newPage = $('<div>', { class: `list-fields-for-each-page fieldsListPage-${pageNumber}` })
				.append(`<span class="page-title">${onOffice_loc_settings.page_title} ${pageNumber}</span>`)
				.append(`<ul class="filter-fields-list attachSortableFieldsList multi-page-list fieldsListPage-${pageNumber} sortableFieldsListForForm"></ul>`)
				.append(`<div class="item-remove-page"><a class="item-remove-page-link submitdelete">${onOffice_loc_settings.remove_page}</a></div>`);

			const clonedElement = $('#menu-item-dummy_key').clone();
			if (clonedElement) {
				this.updateClonedElement(clonedElement, pageNumber);
				newPage.find('ul').append(clonedElement);
			}

			return newPage;
		},

		updateClonedElement: function(element, pageNumber) {
			if (element && element.length) {
				const currentClass = element.attr('class');
				const newClass = currentClass.replace(/page-\d+/, `page-${pageNumber}`);
				element.attr('class', newClass)
					.find('input[name^="oopluginformfieldconfig-pageperform"]').val(pageNumber);
			}
		},

		resetFieldState: function(actionFieldName, valElName) {
			if (actionFieldName && valElName) {
				$(`.${actionFieldName}`).each(function() {
					const parentItem = $(this);
					parentItem.find('.dashicons').removeClass('dashicons-remove').addClass('dashicons-insert').attr('typeField', 1);
					parentItem.find('.check-action').removeClass('action-remove');
					parentItem.find('.field-item-detail').css('opacity', '1');
				});
				$(`.sortableFieldsListForForm`).find(`#menu-item-${valElName}`).remove();
			}
		},

		reorderPages: function() {
			$('.list-fields-for-each-page').each(function(index) {
				const newPageNumber = index + 1;
				$(this).find('.page-title').text(`${onOffice_loc_settings.page_title} ${newPageNumber}`);
				FormMultiPageManager.updatePageClassAndId($(this), newPageNumber);
			});
			this.checkSortableFieldsList();
		},

		updatePageClassAndId: function(page, pageNumber) {
			page.removeClass(function(index, className) {
				return (className.match(/(^|\s)fieldsListPage-\S+/g) || []).join(' ');
			}).addClass(`fieldsListPage-${pageNumber}`);

			page.find('.sortable-item').each(function() {
				const currentClass = $(this).attr('class');
				const newClass = currentClass.replace(/page-\d+/, `page-${pageNumber}`);
				$(this).attr('class', newClass);

				const currentId = $(this).attr('id');
				const newId = currentId.replace(/page-\d+/, `page-${pageNumber}`);
				$(this).attr('id', newId);

				$(this).find('input[name^="oopluginformfieldconfig-pageperform"]').val(pageNumber);
			});

			const ulElement = page.find('ul');
			ulElement.removeClass(function(index, className) {
				return (className.match(/(^|\s)fieldsListPage-\S+/g) || []).join(' ');
			}).addClass(`fieldsListPage-${pageNumber}`);
		}
	};

	if ($('#multi-page-container').length) {
		var cbAdmin = new onOffice.checkboxAdmin();
		$('input[name="oopluginforms-template"]').change(function() {
			if ($(this).is(':checked') && $(this).val().includes('ownerleadgeneratorform.php')) {
				isMultiplePages = true;
				$('#single-page-container').hide().find('input, select, textarea').prop('disabled', true);
				$('#multi-page-container').show();
				$('#multi-page-container ul').find('input, select, textarea').prop('disabled', false);
				$('.add-page-button').show()
				parentContainer = '#multi-page-container';
			} else {
				isMultiplePages = false;
				$('#single-page-container').show().find('input, select, textarea').prop('disabled', false);
				$('#multi-page-container').hide();
				$('#multi-page-container ul').find('input, select, textarea').prop('disabled', true);
				$('.add-page-button').hide()
				parentContainer = '#single-page-container';
			}
			cbAdmin.changeCbStatus(document, parentContainer);
		});

		FormMultiPageManager.init();
	}

	$('.oo-search-field .input-search').on('click', function(event) {
		event.stopPropagation();
		$('.field-lists').show();
	});

	$('.oo-search-field #clear-input').on('click', function() {
		$('.input-search').val('').trigger('input');
	});

	var getCheckedFieldButton = function(btn, pageId) {
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

			var clonedItem = createNewFieldItem(valElName, valElLabel, category, module, label, optionsAvailable, actionFieldName, pageId);

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
			document.dispatchEvent(new CustomEvent('fieldListUpdated'));
			if ($('#multi-page-container').length) {
				FormMultiPageManager.reorderPages()
			}
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
			if ($('#multi-page-container').length && $('#multi-page-container').is(':visible')) {
				$('.sortableFieldsListForForm').find('#menu-item-' + valElName).remove();
			} else {
				$('*#sortableFieldsList').find('#menu-item-' + valElName).remove();
			}
			document.dispatchEvent(new CustomEvent('fieldListUpdated'));
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

	var createNewFieldItem = function(fieldName, fieldLabel, fieldCategory, module, label, optionsAvailable, actionFieldName, pageId) {
		var myLabel = label ? $('#' + label) : {};
		var dummyKey;
		const pageContainer = isMultiplePages ? '#multi-page-container' : '#single-page-container';

		if ($(pageContainer).length) {
			let pageIdSelector = pageId || $('#multi-page-container').is(':visible') ? '.page-' + (pageId ?? '1') : '';

			if (myLabel.length) {
				dummyKey = myLabel.find(pageContainer + ' #menu-item-dummy_key' + pageIdSelector);
			} else {
				dummyKey = $(pageContainer + ' #menu-item-dummy_key' + pageIdSelector);
			}

			if (pageContainer === '#multi-page-container' && !$(pageContainer + ' .list-fields-for-each-page').length) { return '' };
		} else {
			if (myLabel.length) {
				dummyKey = myLabel.find('#menu-item-dummy_key');
			} else {
				dummyKey = $('#menu-item-dummy_key');
			}
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

		if (fieldName === 'DSGVOStatus' || fieldName === 'AGB_akzeptiert' || fieldName === 'gdprcheckbox') {
			var selectors = ['oopluginformfieldconfig-hiddenfield'];
			var hiddenField = clonedElement.find('input[name^=' + selectors.join('],input[name^=') + ']');
			hiddenField.parent().remove();
		}

		if ($(pageContainer).length) {
			clonedElement.find('input[name^="oopluginformfieldconfig-pageperform"]').val(pageId);
		}
		if (!optionsAvailable) {
            var selectors = ['oopluginformfieldconfig-availableOptions', 'oopluginfieldconfig-availableOptions'];
			var availableOptionEl = clonedElement.find('input[name^=' + selectors.join('],input[name^=') + ']');
			availableOptionEl.parent().remove();
		}

		if (fieldName !== 'ort') {
			const selectors = ['oopluginfieldconfig-convertTextToSelectForCityField'];
			let convertTextToSelectForCityField = clonedElement.find('input[name^=' + selectors.join('],input[name^=') + ']');
			convertTextToSelectForCityField.parent().remove();
		}

		if (fieldName !== 'Ort') {
			const selectors = ['oopluginaddressfieldconfig-convertInputTextToSelectField'];
			let convertTextToSelectForField = clonedElement.find('input[name^=' + selectors.join('],input[name^=') + ']');
			convertTextToSelectForField.parent().remove();
		}

		if (fieldName === 'fax' || fieldName === 'mobile' || fieldName === 'phone' || fieldName === 'email' || fieldName === 'imageUrl') {
			const filterableSelectors = ['oopluginaddressfieldconfig-filterable'];
			let filterable = clonedElement.find('input[name^=' + filterableSelectors.join('],input[name^=') + ']');
			filterable.parent().remove();
			const hiddenSelectors = ['oopluginaddressfieldconfig-hidden'];
			let hidden = clonedElement.find('input[name^=' + hiddenSelectors.join('],input[name^=') + ']');
			hidden.parent().remove();
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
