document.addEventListener('DOMContentLoaded', () => {
	if (!oo_field_dependencies) {
		console.warn("missing field_dependencies.json");
		return;
	}

	const fieldsToCheck = Object.values(oo_field_dependencies).flat() ?? [];
	const ownerLeadgenerator = document.querySelectorAll(`form[name="leadgenerator"]`);

	ownerLeadgenerator.forEach(formObject => {
		formObject.addEventListener("change", (event) => {
			if (event.target.id === 'objektart') {
				const propertyType = event.target.value;
				handleSelection(formObject, propertyType, fieldsToCheck);
			}
		});
	});
});


function handleSelection(form, propertyType, fieldsToCheck) {
	const dependants = oo_field_dependencies[propertyType] ?? [];
	const labels = form.querySelectorAll('label') ?? [];
	const selects = form.querySelectorAll('div[class*="oo-single-select"]') ?? [];

	const toggleShown = (element, selector) => {
		const field = element.querySelector(selector);
		if (!field) return;

		const name = field.getAttribute('name');
		if (!name) return;

		if (!fieldsToCheck.includes(name)) return;

		const hide = propertyType
			? !dependants.includes(name)
			: false

		element.classList.toggle("oo-hidden-by-filter", hide);
		if (hide) {
			element.setAttribute('aria-hidden', true);
		} else {
			element.removeAttribute('aria-hidden');
		}
		field.dataset.defaultRequired |= field.required;
		field.required = !hide && (field.dataset.defaultRequired ?? "false") === "true";
	}

	labels.forEach((label) => toggleShown(label, 'input[name]'));
	selects.forEach((select) => toggleShown(select, 'select[name]'));
}

