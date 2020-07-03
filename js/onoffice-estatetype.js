(() => {
    let forms = document.querySelectorAll('form');
    const fetch_possible_types = () => {
        let request = new XMLHttpRequest();
        request.open('GET', '/onoffice-estate-types.json', false);
        request.send(null);
        if (request.status === 200) {
            return JSON.parse(request.responseText);
        }
        return {};
    };

    const possibleTypes = fetch_possible_types();
    const mergeEstateTypesOfKinds = (possibleTypesValues, estateKinds) => {
        let target = [];
        estateKinds.forEach(e => target = target.concat(possibleTypesValues[e]));
        return target;
    }

    const controlMultiSelectEstateKindType = (elementKind, elementType) => {
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
                elementType.onoffice_multiselect.reloadWithOptions(newTypes);
                elementType.onoffice_multiselect.refreshlabel();
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

    const controlSingleSelectEstateKindType = (elementKind, elementType) => {
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

    forms.forEach(function (element) {
        let elementMultiType = element.querySelector('div[data-name^=objekttyp].multiselect');
        let elementMultiKind = element.querySelector('div[data-name^=objektart].multiselect');
        let elementSingleType = element.querySelector('select[name=objekttyp]');
        let elementSingleKind = element.querySelector('select[name=objektart]');
        if (elementMultiType && elementMultiKind) {
            controlMultiSelectEstateKindType(elementMultiKind, elementMultiType);
        } else if(elementSingleType && elementSingleKind) {
            controlSingleSelectEstateKindType(elementSingleKind, elementSingleType);
        }
    });
})();