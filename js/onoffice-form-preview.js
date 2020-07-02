(function (){
    let estateForms = document.querySelectorAll('form[data-view-name]');
    const estate_refresh_preview = (element, viewName) => {
        let formData = new FormData(element);
        formData.append('preview_name', viewName);

        if (element.fetchAbortController) {
            element.fetchAbortController.abort();
        }

        element.fetchAbortController = new AbortController();
        const signal = element.fetchAbortController.signal;

        fetch('/onoffice-estate-preview/', {
            method: 'POST',
            body: formData,
            signal: signal
        }).then(response => response.json())
        .then(result => create_preview(element, viewName, result))
        .catch(err => err);
    }

    const create_preview_text = amount => {
        if (amount === 0) {
            return 'Keine Ergebnisse';
        } else if (amount === 1) {
            return 'Ein Ergebnis anzeigen';
        }
        return '%s Ergebnisse anzeigen'.replace('%s', amount);
    }

    const create_preview = (formElement, identifier, amount) => {
        const preview_text = create_preview_text(amount);
        let submitElement = formElement.querySelector('input[type=submit]');
        submitElement.value = preview_text;
    }

    estateForms.forEach(element => {
        const name = element.getAttribute('data-view-name');
        for (let formControl of element.elements) {
            formControl.addEventListener('change', () => estate_refresh_preview(element, name));
        }
        for (let multiSelect of element.querySelectorAll('div.multiselect')) {
            multiSelect.addEventListener('onoffice-multiselect-modified', (e) => {
                for (let formControlMS of e.target.getElementsByTagName("*")) {
                    formControlMS.addEventListener('change', () => estate_refresh_preview(element, name));
                }
            });
        }
    });
})();