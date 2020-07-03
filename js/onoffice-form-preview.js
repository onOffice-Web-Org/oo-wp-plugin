(function (){
    const estate_refresh_preview = (element, name) => {
        let formData = new FormData(element);
        formData.append('preview_name', name);
        const config = {
            url: '/onoffice-estate-preview/',
            formData
        }
        refresh_preview(element, config);
    }

    const form_refresh_preview = (element, name) => {
        let formData = new FormData(element);
        formData.append('preview_name', name);
        const config = {
            url: '/onoffice-applicant-search-preview/',
            formData
        }
        refresh_preview(element, config);
    }

    const refresh_preview = (element, config) => {
        if (element.fetchAbortController) {
            element.fetchAbortController.abort();
        }

        element.fetchAbortController = new AbortController();
        const signal = element.fetchAbortController.signal;

        fetch(config.url, {
            method: 'POST',
            body: config.formData,
            signal: signal
        }).then(response => response.json())
        .then(result => create_preview(element, result))
        .catch(err => err);
    }

    const create_preview_text = amount => {
        if (amount === 0) {
            return onoffice_form_preview_strings.amount_none;
        } else if (amount === 1) {
            return onoffice_form_preview_strings.amount_one;
        }
        return onoffice_form_preview_strings.amount_other.replace('%s', amount);
    }

    const create_preview = (formElement, amount) => {
        const preview_text = create_preview_text(amount);
        let submitElement = formElement.querySelector('input[type=submit]');
        submitElement.value = preview_text;
    }

    let estateForms = document.querySelectorAll('form[data-estate-search-name]');
    estateForms.forEach(element => {
        const name = element.getAttribute('data-estate-search-name');
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

    let applicantSearchForms = document.querySelectorAll('form[data-applicant-form-id]');
    applicantSearchForms.forEach(element => {
        const id = element.getAttribute('data-applicant-form-id');
        for (let formControl of element.elements) {
            formControl.addEventListener('change', () => form_refresh_preview(element, id));
        }
    });
})();