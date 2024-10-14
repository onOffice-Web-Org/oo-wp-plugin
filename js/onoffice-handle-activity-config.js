jQuery(document).ready(function($) {
	const actionKindSelect = $('.oo-poststuff select[name="oopluginformactivityconfig-actionkind"]');
	const actionTypeSelect = $('.oo-poststuff select[name="oopluginformactivityconfig-actiontype"]');

	if (actionTypeSelect.length && actionKindSelect.length) {
		const actionTypes = onOffice_loc_settings.action_type;
		function populateActionTypes(actionKind) {
			if (actionTypeSelect.children('option').length === 0) {
				const defaultOption = $('<option></option>').attr('value', '').text(onOffice_loc_settings.default_label_choose);
				actionTypeSelect.append(defaultOption);
				const selectedActionTypes = actionTypes[actionKind];
				if (selectedActionTypes) {
					Object.entries(selectedActionTypes).forEach(([value, label]) => {
						if (value !== '') {
							const option = $('<option></option>').attr('value', value).text(label);
							actionTypeSelect.append(option);
						}
					});
				}
			}
		}
	
		actionKindSelect.on('change', function() {
			actionTypeSelect.empty();
			const selectedActionKind = $(this).val();
			populateActionTypes(selectedActionKind);
		});
	
		const initialActionKind = actionKindSelect.val();
		populateActionTypes(initialActionKind);
	}
});