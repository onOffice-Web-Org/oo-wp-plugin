const onOfficeLocalized = typeof onOffice_loc_settings !== 'undefined' ? onOffice_loc_settings : onOffice_unsaved_changes_message;

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

	function generateUnsavedChangesMessage(href) {
		return $(`
			<div class='notice notice-error is-dismissible notice-unsaved-changes-message'>
				<p>${onOfficeLocalized.view_unsaved_changes_message} 
				<a id='leaveWithoutSaving' href='${href}'>${onOfficeLocalized.view_leave_without_saving_text}</a></p>
				<button type='button' class='notice-dismiss notice-save-view'></button>
			</div>
		`);
	}

	function handleUnsavedChanges(e, href) {
		if (checkUnsavedChanges) {
			$('.notice-unsaved-changes-message').remove();
			e.preventDefault();
			let appendUnsavedChangesHtml = generateUnsavedChangesMessage(href);
			appendUnsavedChangesHtml.insertAfter('.wp-header-end');
			$('html, body').animate({ scrollTop: 0 }, 1000);

			return false;
		}
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