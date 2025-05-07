// console.log('testes1434wt54et')
document.addEventListener('DOMContentLoaded', function () {
	console.log('tetsetest')
	document.getElementById('oo-bulk-action-button').addEventListener('click', function () {
		const action = document.getElementById('oo-bulk-action-selector-top').value;

		if (action === 'bulk_delete') {
			document.querySelectorAll('.oo-sortable-checkbox:checked').forEach(cb => {
				const id = cb.value;
				const deleteBtn = document.getElementById('oo-delete-button-' + id);
				if (deleteBtn) {
					deleteBtn.click(); // triggers existing delete logic
				}
			});
		}
	});
});