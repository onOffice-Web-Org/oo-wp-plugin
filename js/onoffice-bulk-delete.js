console.log('esgarsg')
function ooHandleBulkAction(suffix = null, boxId = null){
	const selectorId = suffix ? `oo-bulk-action-selector-${suffix}` : `oo-bulk-action-selector`
	const action = document.getElementById(selectorId).value;
	if (action === 'bulk_delete') {
		const container = boxId ? document.getElementById(boxId) : null
		ooHandleBulkDelete(container)
	}
}

function ooHandleBulkDelete(el = null){
	const element = el ?? document;
	element.querySelectorAll(`.oo-sortable-checkbox:checked`).forEach(cb => {
		const id = cb.value;
		const deleteBtn = document.getElementById('oo-delete-button-' + id);
		if (deleteBtn) {
			deleteBtn.click();
		}
		else {
			const lv4Parent = cb.parentElement?.parentElement?.parentElement?.parentElement;
			if(!lv4Parent){
				return;
			}
			const deleteButtons =  lv4Parent.querySelectorAll('.item-delete-link.submitdelete');
			if (!(deleteButtons instanceof NodeList) || deleteButtons.length !== 1) {
				return;
			}
			deleteButtons[0].click();
		}
	});
}

function ooHandleCheckboxAllChange(evt, containerId = null) {
	const masterCheckbox = evt.target;
	const container = containerId ? document.getElementById(containerId) : document
	const checkboxes = container.querySelectorAll('.oo-sortable-checkbox');

	checkboxes.forEach(cb => {
		if (cb !== masterCheckbox) {
			cb.checked = masterCheckbox.checked;
		}
	});
}

