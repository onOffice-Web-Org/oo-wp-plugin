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
			deleteBtn.click(); // triggers existing delete logic
		}
	});
}

