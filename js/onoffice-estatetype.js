(() => {
    let forms = document.querySelectorAll('form');
    const fetch_possible_types = () => {
        return fetch('/onoffice-estate-types.json', {method: 'GET'})
            .then(response => response.json())
            .catch(() => ({}));
    };

    const mergeEstateTypesOfKinds = (possibleTypesValues, estateKinds) => {
        let target = [];
        estateKinds.forEach(e => target = target.concat(possibleTypesValues[e]));
        return target;
    }

    const controlMultiSelectEstateKindType = (elementKind, elementType, possibleTypes) => {
        elementType.allOptions =  elementType.onoffice_multiselect._options;
        const multiSelectChangeFn = (e) => {
            if (e.detail.name === 'objektart[]') {
                const selection = e.detail.selection;
                let newTypes = {};
                // clone
                Object.assign(newTypes, elementType.allOptions);
                const allowedTypes = mergeEstateTypesOfKinds(possibleTypes, selection);

                for (const k in newTypes) {
                    if (allowedTypes.indexOf(k) < 0) {
                        delete newTypes[k];
                    }
                }
                const oldSelection = elementType.onoffice_multiselect._getSelection();
                const newSelection = allowedTypes.filter(value => oldSelection.includes(value))
                elementType.onoffice_multiselect.reloadWithOptions(newTypes, newSelection);
                elementType.onoffice_multiselect.refreshlabel();
                elementType.setAttribute('data-values', JSON.stringify(newTypes));
                elementType.dispatchEvent(new Event('onoffice-multiselect-modified'));
            }
        }
        elementKind.addEventListener('onoffice-multiselect-change', multiSelectChangeFn, false);
        const e = new CustomEvent('ready',  { detail: {
            name: 'objektart[]',
            selection: elementKind.onoffice_multiselect._getSelection()
        }});
        multiSelectChangeFn(e);
    }

    const controlSingleSelectEstateKindType = (elementKind, elementType, possibleTypes) => {
        if (!elementType.allOptions) {
            let newTypes = {};
            Object.assign(newTypes, [...elementType.options]);
            elementType.allOptions = newTypes;
        }
        elementType.allOptions = elementType.allOptions || elementType.options;
        const singleSelectChangeFn = () => {
            const selection = elementKind.selectedOptions[0].value;
            let newTypes = {};
            // clone
            Object.assign(newTypes, elementType.allOptions);
            const allowedTypes = mergeEstateTypesOfKinds(possibleTypes, [selection]);

            for (const k in newTypes) {
                if (allowedTypes.indexOf(newTypes[k].value) < 0 && newTypes[k].value !== "") {
                    delete newTypes[k];
                }
            }
            elementType.options;
            for (const [key, value] of Object.entries(elementType.options)) {
                elementType.remove(value.index);
            }
            for (const [key, value] of Object.entries(newTypes)) {
                elementType.add(value);
            }
            elementType.options.selectedIndex = 0;
        }
        elementKind.addEventListener('change', singleSelectChangeFn, false);
        singleSelectChangeFn(new Event('ready'));
    }

    fetch_possible_types().then(possibleTypes => {
        forms.forEach(function (element) {
            let elementMultiType = element.querySelector('div[data-name^=objekttyp].multiselect');
            let elementMultiKind = element.querySelector('div[data-name^=objektart].multiselect');
            let elementSingleType = element.querySelector('select[name=objekttyp]');
            let elementSingleKind = element.querySelector('select[name=objektart]');
            if (elementMultiType && elementMultiKind) {
                controlMultiSelectEstateKindType(elementMultiKind, elementMultiType, possibleTypes);
            } else if(elementSingleType && elementSingleKind) {
                controlSingleSelectEstateKindType(elementSingleKind, elementSingleType, possibleTypes);
            }
        });
    });
})();