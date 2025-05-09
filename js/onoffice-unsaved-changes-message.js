const onOfficeLocalized = typeof onOffice_loc_settings !== 'undefined' ? onOffice_loc_settings : onOffice_unsaved_changes_message;
const screenDataUnsavedChanges = screen_data_unsaved_changes ?? [];
jQuery(document).ready(function($){
	let checkUnsavedChanges = false;
	let checkNavigationTriggered = false;
	const $filterFieldsList =  $('.filter-fields-list');

	$('.oo-poststuff :input, .oo-page-api-settings :input').on("change", function() {
		checkUnsavedChanges = true;
	});

	$('.oo-poststuff span.dashicons, .oo-poststuff li').on("click", function() {
		checkUnsavedChanges = true;
	});

	if ($filterFieldsList.length) {
		$filterFieldsList.sortable({
			update: function() {
				checkUnsavedChanges = true;
			}
		});
	}

	function generateUnsavedChangesMessage(href, message) {
		return $(`
			<div class='notice notice-error is-dismissible notice-unsaved-changes-message'>
				<p>${message}
				<a id='leaveWithoutSaving' href='${href}'>${onOfficeLocalized.view_leave_without_saving_text}</a></p>
				<button type='button' class='notice-dismiss notice-save-view'></button>
			</div>
		`);
	}

	function showUnsavedChangesMessage(href, message) {
		$('.notice-unsaved-changes-message').remove();
		let messageHtml = generateUnsavedChangesMessage(href, message);
		messageHtml.insertAfter('.wp-header-end');
		$('html, body').animate({ scrollTop: 0 }, 1000);
	}

	function handleUnsavedChanges(e, href) {
		if (!checkUnsavedChanges) {
			return;
		}

		e.preventDefault();
		const title = $(`[name="${screenDataUnsavedChanges.name}"]`).val();
		if (title.length == 0) {
			showUnsavedChangesMessage(href, onOfficeLocalized.view_unsaved_change_empty_name_message);
			return false;
		}
		processAjaxRequest(title, href);

		return false;
	}

	function processAjaxRequest(title, href) {
		const urlParams = new URLSearchParams(window.location.search);
		const pageId = urlParams.get('id');
		const data = {
			'action': screenDataUnsavedChanges.action,
			'name': title,
			'id': pageId
		};

		$.get(screenDataUnsavedChanges.ajaxurl, data, function(response) {
			let message = response.success === false ?
				onOfficeLocalized.view_unsaved_change_same_name_message
				: onOfficeLocalized.view_unsaved_changes_message;
			showUnsavedChangesMessage(href, message);
		}, 'json');
	}

	$('#adminmenu a[href], #wpadminbar a[href], .oo-admin-tab').on('click', function(e) {
		checkNavigationTriggered = true;
		if ($(this).attr('target') === '_blank') {
			return;
		}
		handleUnsavedChanges(e, $(this).attr('href'));
	});

	window.onbeforeunload = function() {
		if (checkUnsavedChanges && !checkNavigationTriggered) {
			return onOfficeLocalized.view_unsaved_changes_message;
		}
	};

	$('#onoffice-ajax').submit(function () {
		checkUnsavedChanges = false;
	});

	$(document).on('click', '#leaveWithoutSaving, #submit', function(e) {
		checkUnsavedChanges = false;
	});

	$(document).on('click', '.notice-save-view.notice-dismiss', function () {
		$(this).parent().remove();
	});
});