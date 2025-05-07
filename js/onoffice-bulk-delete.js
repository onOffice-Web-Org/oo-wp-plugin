// console.log('testes1434wt54et')
document.addEventListener('DOMContentLoaded', function () {
	const bulkDeleteBtn = document.getElementById('oo-bulk-delete-btn');

	bulkDeleteBtn.addEventListener('click', function () {
		document.querySelectorAll('.oo-sortable-checkbox:checked').forEach(cb => {
			const id = cb.value
			const deleteBtn = document.getElementById('oo-delete-button-'+id)
			if(deleteBtn){
				deleteBtn.click();
			}
		});
	});
});