function ooHandleBulkAction(suffix = null, boxId = null){
	const selectorId = suffix ? `oo-bulk-action-selector-${suffix}` : `oo-bulk-action-selector`
	const action = document.getElementById(selectorId).value;
	if (action === 'bulk_delete') {
		const container = boxId ? document.getElementById(boxId) : null
		ooHandleBulkDelete(container)
	}
}

function ooHandleBulkDelete(container = null){
	const element = container ?? document;
	element.querySelectorAll(`.oo-sortable-checkbox:checked`).forEach(cb => {
		const id = cb.value;
		const deleteBtns = document.querySelectorAll('.oo-delete-button-' + id)
		if(!deleteBtns || deleteBtns.length > 2){
			return
		}
		if(deleteBtns.length > 0){
			deleteBtns.forEach(deleteBtn =>{
				deleteBtn.click();
			})
		}
		else {
			//case of a newly added field that hasnt been saved yet
			const li = document.getElementById('menu-item-'+id);
			if(li){
				const deleteBtns = li.querySelectorAll('.item-delete-link.submitdelete')
				if(deleteBtns && deleteBtns.length === 1){
					deleteBtns[0].click()
				}
			}
		}
	});
}

function ooHandleMasterCheckboxChange(evt, containerId = null) {
	const masterCheckbox = evt.target;
	const container = containerId ? document.getElementById(containerId) : document
	const checkboxes = container.querySelectorAll('.oo-sortable-checkbox');

	checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
}

function ooHandleChildCheckboxChange(evt) {
	const checkbox = evt.target;

	let containerOtherCheckboxes = checkbox.closest('#single-page-container');
	if(!containerOtherCheckboxes){
		containerOtherCheckboxes = checkbox.closest('#multi-page-container');
	}
	if(!containerOtherCheckboxes){
		containerOtherCheckboxes = checkbox.closest('.fieldsSortable.postbox');
	}

	const containerMasterCheckbox = checkbox.closest('.fieldsSortable.postbox');

	if (!containerOtherCheckboxes || !containerMasterCheckbox) return;

	const otherCheckboxes = containerOtherCheckboxes.querySelectorAll('.oo-sortable-checkbox');
	const masterCheckbox = containerMasterCheckbox.querySelector('.oo-sortable-checkbox-master');
	let allCheckboxesChecked = true
	otherCheckboxes.forEach(checkbox => {
		if(!checkbox.checked && checkbox.value !== 'dummy_key'){
			allCheckboxesChecked = false
		}
	})
	masterCheckbox.checked = allCheckboxesChecked
}

