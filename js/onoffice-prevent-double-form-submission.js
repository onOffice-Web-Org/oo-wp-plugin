jQuery(document).ready(function ($) {
	const formElements = document.querySelectorAll('.oo-form, #onoffice-form');

	formElements.forEach((formElement) => {
		let submitted = false;
		const $form = $(formElement);
		const submitInput = $form.find('input[type=submit]');

		$form.on('submit', function (event) {
			const isValid = validateForm($form); // eigene Validierungsmethode

			if (!isValid) {
				event.preventDefault();
				event.stopPropagation();

				// Zum ersten ungültigen Feld scrollen
				const firstInvalid = $form.find('[aria-invalid="true"]').first()[0];
				if (firstInvalid) {
					firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
					firstInvalid.focus({ preventScroll: true });
				}

				submitInput.prop('disabled', false); // Sicherstellen, dass der Button aktiv bleibt
				return;
			}

			if (submitted) {
				event.preventDefault(); // Doppelsubmits blockieren
			} else {
				submitted = true;
				submitInput.prop('disabled', true); // Jetzt sicher deaktivieren
			}
		});
	});
});