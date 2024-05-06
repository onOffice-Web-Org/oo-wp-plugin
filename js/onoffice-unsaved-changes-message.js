var onOffice = typeof onOffice_loc_settings !== 'undefined' ? onOffice_loc_settings : onOffice_unsaved_changes_message;

jQuery(document).ready(function($){
	let checkUnsavedChanges = false;
	let checkNavigationTriggered = false;

	$('#poststuff :input, form[action="options.php"] :input').on("change", function() {
		checkUnsavedChanges = true;
	});

	$('#poststuff span, #poststuff li').on("click", function() {
		checkUnsavedChanges = true;
	});

	$('.filter-fields-list').sortable({
		update: function() {
			checkUnsavedChanges = true;
		}
	});

	function generateUnsavedChangesMessage(href) {
		return $(`
			<div class='notice notice-error is-dismissible notice-unsaved-changes-message'>
				<p>${onOffice.view_unsaved_changes_message} 
				<a id='leaveWithoutSaving' href='${href}'>${onOffice.view_leave_without_saving_text}</a></p>
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

	$('a[href]').on('click', function(e) {
		if (!$(this).closest('#adminmenu').length) {
			checkNavigationTriggered = true;
		}
	});

	$('#adminmenu a[href]').on('click', function(e) {
		if ($(this).attr('target') === '_blank') {
			return;
		}
		handleUnsavedChanges(e, $(this).attr('href'));
	});

	window.onbeforeunload = function() {
		if (checkUnsavedChanges && !checkNavigationTriggered) {
			return onOffice.view_unsaved_changes_message;
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