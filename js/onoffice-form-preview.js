(() => {
    const estate_refresh_preview = (element, name) => {
        let formData = new FormData(element);
        formData.append('preview_name', name);
        formData.append('nonce', onoffice_form_preview_strings.nonce_estate);
        let spinner = element.querySelector('#spinner');
        const config = {
            url: '/onoffice-estate-preview/',
            formData,
            spinner
        }
        refresh_preview(element, config);
    }

    const form_refresh_preview = (element, name) => {
        let formData = new FormData(element);
        formData.append('preview_name', name);
        formData.append('nonce', onoffice_form_preview_strings.nonce_applicant_search);
        let spinner = element.querySelector('#spinner');
        const config = {
            url: '/onoffice-applicant-search-preview/',
            formData,
            spinner
        }
        refresh_preview(element, config);
    }

    const refresh_preview = (element, config) => {
        if (element.fetchAbortController) {
            element.fetchAbortController.abort();
        }

        element.fetchAbortController = new AbortController();
        const signal = element.fetchAbortController.signal;

        if (config.spinner) {
            config.spinner.classList.add('thinking');
        }

        fetch(config.url, {
            method: 'POST',
            body: config.formData,
            signal: signal
        }).then(response => response.json())
        .then(result => create_preview(element, result, config.spinner))
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

    const create_preview = (formElement, amount, spinner) => {
        const preview_text = create_preview_text(amount);
        let submitElement = formElement.querySelector('input[type=submit]');
        submitElement.value = preview_text;
        if (spinner) {
            spinner.classList.remove('thinking');
        }
        submitElement.classList.remove('match');
        if (amount > 0) {
            submitElement.classList.add('match');
        }
    }

    document.querySelectorAll('form[data-estate-search-name]').forEach(element => {
        const name = element.getAttribute('data-estate-search-name');
        estate_refresh_preview(element, name);
        for (let formControl of element.elements) {
            formControl.addEventListener('change', () => estate_refresh_preview(element, name));
        }

        for (let multiSelect of element.querySelectorAll('div.multiselect')) {
            multiSelect.addEventListener('onoffice-multiselect-modified', (e) => {
                estate_refresh_preview(element, name);
                for (let formControlMS of e.target.getElementsByTagName("*")) {
                    formControlMS.addEventListener('change', () => estate_refresh_preview(element, name));
                }
            });
        }
    });

    document.querySelectorAll('form[data-estate-search-name]').forEach(element => {
        const name = element.getAttribute('data-estate-search-name');
        estate_refresh_preview(element, name);
        for (let formControl of element.elements) {
            let $event = $(".custom-multiple-select");
            $event.on("change", function (e) {
                estate_refresh_preview(element, name);
            });
        }
    });

    document.querySelectorAll('form[data-applicant-form-id]').forEach(element => {
        const id = element.getAttribute('data-applicant-form-id');
        form_refresh_preview(element, id);
        for (let formControl of element.elements) {
            formControl.addEventListener('change', () => form_refresh_preview(element, id));
        }
    });

    document.querySelectorAll('form[data-applicant-form-id]').forEach(element => {
        const id = element.getAttribute('data-applicant-form-id');
        form_refresh_preview(element, id);
        for (let formControl of element.elements) {
            let $eventSelect = $('.' + formControl.getAttribute("class"));
            $eventSelect.on("change", function (e) {
                form_refresh_preview(element, id);
            });
        }
    });

    let spinner = document.querySelector('#spinner');
    if (spinner) {
        let  i = 256, cir, a;
        for (; i--;) {
            a = i * Math.PI/128;
            cir = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            cir.setAttribute("cx", "" + (15.0 - Math.sin(a) * 8.0));
            cir.setAttribute("cy", "" + (15.0 - Math.cos(a) * 8.0));
            cir.setAttribute("r", "1.5");
            cir.setAttribute("fill", "rgb(" + i + ", " + i + ", " + i + ")");
            cir.setAttribute("shape-rendering", "geometricPrecision");
            spinner.appendChild(cir);
        }
    }
})();