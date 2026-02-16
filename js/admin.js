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

	const MultiPageTitle = {
		init: function() {
			this.convertMultiPageTitles();
		},
		convertMultiPageTitles: function () {
			document.querySelectorAll('.multi-page-title').forEach((multiPageTitleSection, index) => {
				const titleInputs = multiPageTitleSection.querySelectorAll('input[name^=oopluginformmultipagetitle-value]');
				const page = multiPageTitleSection.getAttribute('data-page');
				const localeSelect = multiPageTitleSection.querySelector('select[name=oopluginformmultipagetitle-locale].onoffice-input');

				if (!localeSelect) {
					console.warn(`Locale select not found for page ${page}. Skipping.`);
					return;
				}

				titleInputs.forEach((titleInput) => {
					const parent = titleInput.closest('.wp-clearfix.custom-input-field');
					const langCode = titleInput.getAttribute('data-localized');
					const selectOption = Array.from(localeSelect.options).find(opt => opt.value === langCode);

					if (langCode !== 'native') {
						parent.appendChild(this.createDeleteButton(page, langCode, parent, selectOption));
						this.removeLanguageFromSelect(selectOption);
					}
					titleInput.name = `oopluginformmultipagetitle[${page}][${langCode}]`;
				});

				//Add new page title input for localization
				this.addMultiPageLanguageSelectEventListeners(localeSelect, multiPageTitleSection, page);
			});
		},
		addMultiPageTitle: function(pageNumber) {
			const container = document.createElement('div');
			const span = document.createElement('span');
			const paragraph = document.createElement('p');
			const label = document.createElement('label');
			const input = document.createElement('input');

			container.classList.add('multi-page-title');
			span.className = 'page-title';
			span.textContent = `${onOffice_loc_settings.page_title} ${pageNumber}`;

			paragraph.classList.add('wp-clearfix', 'custom-input-field');
			label.classList.add('howto', 'custom-label');
			label.textContent = oOMultiPageI18n.pageTitle;
			input.classList.add('onoffice-input');
			input.setAttribute('type', 'text');
			input.name = `oopluginformmultipagetitle[${pageNumber}][native]`;

			paragraph.appendChild(label);
			paragraph.appendChild(input);
			container.appendChild(span);
			container.appendChild(paragraph);
			container.appendChild(this.addMultiPageLanguageSelect(container));

			return container;
		},

		addMultiPageLanguageSelect: function(multiPageTitleSection) {
			const paragraph = document.createElement('p');
			const label = document.createElement('label');
			const select = document.createElement('select');
			const installedLanguages = Object.keys(onOffice_loc_settings.installed_wp_languages);


			paragraph.classList.add('wp-clearfix', 'custom-input-field');
			label.classList.add('howto', 'custom-label');
			label.textContent = oOMultiPageI18n.addLanguage;
			select.classList.add('onoffice-input');
			select.name = `oopluginformmultipagetitle-locale`;
			select.options.add(new Option(onOffice_loc_settings.label_choose_language, ''));
			installedLanguages.forEach(function (k) {
				const v = onOffice_loc_settings.installed_wp_languages[k];
				if (k === onOffice_loc_settings.language_native) {
					k = 'native';
				}
				select.options.add(new Option(v, k));
			});
			select.options.selectedIndex = 0;
			this.addMultiPageLanguageSelectEventListeners(select, multiPageTitleSection);

			paragraph.appendChild(label);
			paragraph.appendChild(select);

			return paragraph;
		},

		addMultiPageLanguageSelectEventListeners: function(localeSelect, multiPageTitleSection) {
			const self = this;

			localeSelect.addEventListener('change', function () {
				const page = $(this).closest('.multi-page-title').attr('data-page');
				const selectedLocale = this.value;
				const selectedOption = this.options[localeSelect.selectedIndex];
				const selectedLocaleText = selectedOption.text;
				const parent = localeSelect.closest('.wp-clearfix.custom-input-field');
				const identifier = `oopluginformmultipagetitle[${page}][${selectedLocale}]`;

				let existingInput = multiPageTitleSection.querySelector(`input[name="${identifier}"]`);
				if (!existingInput) {
					self.removeLanguageFromSelect(selectedOption);
					const paragraph = document.createElement('p');
					paragraph.classList.add('wp-clearfix', 'custom-input-field');
					paragraph.appendChild(self.createLabel(identifier, page, selectedLocaleText));
					paragraph.appendChild(self.createInput(identifier, selectedLocale));
					paragraph.appendChild(self.createDeleteButton(page, selectedLocale, paragraph, selectedOption));
					parent.insertAdjacentElement('beforebegin', paragraph);
				}
				localeSelect.value = '';
			});
		},

		createInput: function(identifier, langCode) {
			const input = document.createElement('input');
			const langShort = langCode.split('_')[0]; //e.g. "es_ES" → "es"
			input.id = identifier;
			input.type = 'text';
			input.name = identifier;
			input.setAttribute('lang', langShort);
			return input;
		},

		removeLanguageFromSelect: function(selectOption) {
			if (selectOption) {
				selectOption.hidden = true;
				selectOption.style.display = 'none';
				selectOption.disabled = true;
			}
		},

	 	addLanguageToSelect: function(lang, selectOption) {
			if (selectOption) {
				selectOption.hidden = false;
				selectOption.style.display = 'block';
				selectOption.disabled = false;
			}
		},

		createLabel: function (identifier, page, selectedLocaleText) {
			const label = document.createElement('label');
			const nativeInput = document.querySelector(`input[name="oopluginformmultipagetitle[${page}][native]"]`);

			if (nativeInput) {
				const nativeLabel = document.querySelector(`label[for="${nativeInput.id}"]`);
				label.textContent = (nativeLabel?.textContent ?? "") + " " + selectedLocaleText;
			}
			label.htmlFor = identifier;
			return label;
		},

	 	createDeleteButton: function(page, langCode, parent, selectOption) {
			const self = this;
			const deleteButton = document.createElement('button');
			deleteButton.id = 'deletePageTitle' + `[${page}][${langCode}]`;
			deleteButton.className = 'dashicons dashicons-dismiss multi-page-title-delete';
			deleteButton.type = 'button';
			deleteButton.setAttribute('aria-label', oOMultiPageI18n.removeTitleForLanguage.replace('%s', langCode));

			deleteButton.addEventListener('click', function (e) {
				if (parent) {
					parent.remove();
					self.addLanguageToSelect(langCode, selectOption);
				}
			});
			return deleteButton;
		}
	}
	try {
		MultiPageTitle.init();
	} catch (error) {
		console.error('Error initializing MultiPageTitle:', error);
	}


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
			this.draggablePages();
			this.multiSelectItems();
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
				},
				update: function(event, ui) {
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
					FormMultiPageManager.draggablePages();
				}
			});
		},

		addNewPage: function() {
			const newPageNumber = this.clickCount + 1;
			const newPage = this.createNewPage(newPageNumber);
			$('#multi-page-container').append(newPage);
			this.initializeSortable();
			this.checkTemplate();
			this.draggablePages();
		},

		createNewPage: function(pageNumber) {
			const newPage = $('<div>', { class: `list-fields-for-each-page fieldsListPage-${pageNumber}` })
				.append(MultiPageTitle.addMultiPageTitle(pageNumber))
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
				FormMultiPageManager.updateMultiPageTitleAttributes($(this), newPageNumber);
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
		},

		updateMultiPageTitleAttributes: function(page, pageNumber) {
			// Update data-page attribute on .multi-page-title element
			const multiPageTitle = page.find('.multi-page-title');
			if (multiPageTitle.length) {
				multiPageTitle.attr('data-page', pageNumber);
				const multiPageCounter = multiPageTitle.find('.multi-page-counter');
				if (multiPageCounter.length) {
					multiPageCounter.text(multiPageCounter.text().replace(/\d+/, pageNumber));
				}
				// Update all title input names to reflect the new page number
				multiPageTitle.find('input[name^="oopluginformmultipagetitle"]').each(function() {
					const currentName = $(this).attr('name');
					// Replace [X] with [newPageNumber] in the name attribute
					const newName = currentName.replace(/\[\d+\]/, `[${pageNumber}]`);
					$(this).attr('name', newName);
				});
				// Update delete button IDs to reflect the new page number
				multiPageTitle.find('.multi-page-title-delete').each(function() {
					const currentId = $(this).attr('id');
					// Replace deletePageTitle[X][langCode] with deletePageTitle[newPageNumber][langCode]
					const newId = currentId.replace(/deletePageTitle\[\d+\]/, `deletePageTitle[${pageNumber}]`);
					$(this).attr('id', newId);
				});
			}
		},

		draggablePages: function () {
			if ($('#multi-page-container .list-fields-for-each-page').length > 1) {
				$('#multi-page-container .list-fields-for-each-page').css("cursor", "move");

				$('#multi-page-container').sortable({
					items: '.list-fields-for-each-page',
					placeholder: 'sortable-placeholder',
					tolerance: 'pointer',
					cursor: 'move',
					cursorAt: { top: 5, left: 5 },
					delay: 40,
					opacity: 0.8,
					axis: 'y',
					forcePlaceholderSize: true,
					start: function(e, ui) {
						ui.placeholder.height(ui.item.height());
						$('.list-fields-for-each-page').css('transition', 'all 0.08s');
						ui.item.addClass('page-dragging');

						const $pages = $('.list-fields-for-each-page');
						const childLists = $pages.map(function() {
							return $(this).find('.filter-fields-list')[0];
						}).get();

						$(document).on('mousemove.draggablePages', function(e) {
							const x = e.clientX;
							const y = e.clientY;

							$pages.each(function(index) {
								const $page = $(this);
								const pageRect = this.getBoundingClientRect();
								const childRect = childLists[index].getBoundingClientRect();

								const inPage = (
									x >= pageRect.left &&
									x <= pageRect.right &&
									y >= pageRect.top &&
									y <= pageRect.bottom
								);

								const inChild = (
									x >= childRect.left &&
									x <= childRect.right &&
									y >= childRect.top &&
									y <= childRect.bottom
								);

								if (!$page.is(ui.item)) {
									$page.toggleClass('page-dragging', inPage && !inChild);
								}
							});
						});
					},
					stop: function(e, ui) {
						$('.list-fields-for-each-page').css('transition', '');
						$(document).off('mousemove.draggablePages');
						$('.list-fields-for-each-page').removeClass('page-dragging');
					},
					update: function(e, ui) {
						FormMultiPageManager.reorderPages();
					}
				});
			} else {
				if ($('#multi-page-container').data("ui-sortable")) {
					$('#multi-page-container').sortable('destroy');
					$('#multi-page-container .list-fields-for-each-page').css("cursor", "default");
				}

				$(document).off('mousemove.draggablePages');
			}
		},

		toggleAddPageButton: function () {
			const hasSelectedItems = $('.list-fields-for-each-page .item.selected').length > 0;
			$('.add-page-button').prop('disabled', hasSelectedItems);
		},

		updateSelectAllCheckbox: function () {
			const $checkboxes = $('#multi-page-container .list-fields-for-each-page .menu-item-handle input[type="checkbox"]').not('#postbox-select-all');
			const $selectAll = $('#postbox-select-all');
			const total = $checkboxes.length;
			const checked = $checkboxes.filter(':checked').length;
		
			$selectAll.prop('checked', total > 0 && checked === total);
		},
		
		multiSelectItems: function () {
			const $container = $('#multi-page-container');
		
			$container.off('change.multiSelect').on('change.multiSelect', '.list-fields-for-each-page .menu-item-handle input[type="checkbox"]', (event) => {
				const $checkbox = $(event.target);
				const $item = $checkbox.closest('.item');
		
				if ($checkbox.prop('checked')) {
					$item.addClass('selected');
				} else {
					$item.removeClass('selected');
				}
				this.toggleAddPageButton();
				setTimeout(() => {
					this.updateSelectAllCheckbox();
				}, 0);
				this.multiSortable();
			});
		
			$(document).off('click.multiSelectDeselect').on('click.multiSelectDeselect', (event) => {
				const $target = $(event.target);

				const clickedInsideContainer  = $target.closest('#oo-fields-sortable-container').length > 0;
				const clickedInsideBulkAction = $target.closest('#oo-bulk-action-container').length > 0;

				if (!clickedInsideContainer && !clickedInsideBulkAction) {
					$container.find('.list-fields-for-each-page .selected').removeClass('selected');
					$container.find('.list-fields-for-each-page .menu-item-handle input[type="checkbox"]').prop('checked', false);
					$('#postbox-select-all').prop('checked', false);
					this.toggleAddPageButton();
					this.updateSelectAllCheckbox();
					this.multiSortable();
				}
			});
		
			$('#postbox-select-all').off('change.multiSelectAll').on('change.multiSelectAll', (event) => {
				const isChecked = $(event.target).prop('checked');
				const $checkboxes = $container.find('.list-fields-for-each-page .menu-item-handle input[type="checkbox"]').not('#postbox-select-all');
		
				$checkboxes.each(function () {
					const $cb = $(this);
					$cb.prop('checked', isChecked);
					const $item = $cb.closest('.item');
					if (isChecked) {
						$item.addClass('selected');
					} else {
						$item.removeClass('selected');
					}
				});
				this.toggleAddPageButton();
				this.updateSelectAllCheckbox();
				this.multiSortable();
			});
		},

		multiSortable: function () {
			let multiDragSelectedOrdered = [];
			let draggedOriginalItem = null;
			let isMultiDrag = false;
			const $list = $('.filter-fields-list');
			if ($list.data('ui-sortable')) {
				$list.sortable('destroy');
			}

			$list.sortable({
				axis: 'y',
				connectWith: '.filter-fields-list',
				items: '> li.sortable-item:visible',
				revert: false,
				helper: function (event, item) {
					const $selected = $('#multi-page-container .selected');
					isMultiDrag = item.hasClass('selected') && $selected.length > 1;

					if (isMultiDrag) {
						multiDragSelectedOrdered = $selected.toArray();
						draggedOriginalItem = item[0];
						return $('<li class="multi-drag-helper" style="pointer-events:none;"/>').append($selected.clone());
					} else {
						multiDragSelectedOrdered = [item[0]];
						draggedOriginalItem = item[0];
						return item.clone().css('pointer-events', 'none');
					}
				},
				start: function (event, ui) {
					ui.item.data('origIndex', ui.item.index());
					ui.item.data('origParent', ui.item.parent());
				},
				stop: function (event, ui) {
					const $droppedItem = ui.item;

					if (isMultiDrag) {
						const targetList = $droppedItem.closest('.filter-fields-list');

						if (!targetList.length) {
							$(this).sortable('cancel');
							return;
						}

						const dropIndex = $droppedItem.index();

						$droppedItem.detach();
						multiDragSelectedOrdered.forEach(item => {
							$(item).detach();
						});

						if (targetList.children().length === 0) {
							targetList.append(multiDragSelectedOrdered);
						} else if (dropIndex >= targetList.children().length) {
							targetList.append(multiDragSelectedOrdered);
						} else {
							targetList.children().eq(dropIndex).before(multiDragSelectedOrdered);
						}
					}

					FormMultiPageManager.reorderPages();
				}
			});
		}
	};

	if ($('#multi-page-container').length) {
		var cbAdmin = new onOffice.checkboxAdmin();
		$('input[name="oopluginforms-template"]').change(function () {
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

	$('.oo-search-field .input-search').on('click', function (event) {
		event.stopPropagation();
		$('.field-lists').show();
	});

	$('.oo-search-field #clear-input').on('click', function () {
		$('.input-search').val('').trigger('input');
	});

	var getCheckedFieldButton = function (btn, pageId) {
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
			$('.' + actionFieldName).each(function () {
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
				FormMultiPageManager.multiSortable();
			}
		} else {
			var valElName = $(btn).attr('value');
			var checkedFields = [];
			const actionFieldName = 'labelButtonHandleField-' + valElName;
			$('.' + actionFieldName).each(function () {
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
	var getCheckedFields = function (but) {
		var label = $(but).attr('data-action-div');
		var categoryShort = but.name;
		var category = $(but).attr('data-onoffice-category');
		var checkedFields = [];
		var inputConfigFields = $('#' + categoryShort).find('input.onoffice-possible-input:checked');

		$(inputConfigFields).each(function (index) {
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
			} else {
				//this case is for estate detail view
				var detailViewDivId = 'actionForestate';
				if (categoryShort.startsWith('address')) {
					detailViewDivId = 'actionForaddress';
				}
				if ($('#' + detailViewDivId).find('#sortableFieldsList').find('#menu-item-' + valElName).length === 0) {
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

	var createNewFieldItem = function (fieldName, fieldLabel, fieldCategory, module, label, optionsAvailable, actionFieldName, pageId) {
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

			if (pageContainer === '#multi-page-container' && !$(pageContainer + ' .list-fields-for-each-page').length) {
				return ''
			}
		} else {
			if (myLabel.length) {
				dummyKey = myLabel.find('#menu-item-dummy_key');
			} else {
				dummyKey = $('#menu-item-dummy_key');
			}
		}
		var clonedElement = dummyKey.clone(true, false);
		clonedElement.removeData();
		clonedElement.removeClass('ui-sortable-handle');

		clonedElement.attr('id', 'menu-item-' + fieldName);
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
		clonedElement.find('[name^=exclude]').attr('name', function (index, name) {
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

		if (onOffice_loc_settings.modulelabels && module) {
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


(function ($) {
	var refreshTemplateMouseOver = function () {
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
