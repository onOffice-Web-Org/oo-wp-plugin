onOffice = onOffice || {};
onOffice.default_values_inputs_converted = onOffice.default_values_inputs_converted || [];

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

onOffice.default_values_input_converter = function () {
    const predefinedValues = onOffice_loc_settings.defaultvalues || {};

    // plaintext
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function (element) {
        element.backupLanguageSelection = {};
        const mainInput = element.parentElement.parentElement
            .querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value].onoffice-input');
        const fieldname = element.parentElement.parentElement.parentElement
            .querySelector('span.menu-item-settings-name').textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldname) !== -1) {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldname);
        mainInput.name = 'defaultvalue-lang[' + fieldname + '][native]';

        (function () {
            if (predefinedValues[fieldname] !== undefined) {
                const predefinedValuesIsObject = (typeof predefinedValues[fieldname] === 'object') &&
                    !Array.isArray(predefinedValues[fieldname]);
                if (predefinedValuesIsObject) {
                    for (let lang in predefinedValues[fieldname]) {
                        const relevantOption = element.querySelector('option[value=' + lang + ']');
                        if (lang !== 'native') {
                            const clone = generateClone(mainInput, lang);
                            const label = generateLabel(relevantOption.text || '', clone);
                            const deleteButton = generateDeleteButton(element, lang);
                            const paragraph = generateParagraph(label, clone, deleteButton);
                            mainInput.parentNode.parentNode.insertBefore(paragraph, element.parentNode);
                            element.backupLanguageSelection[lang] = relevantOption;
                            element.options[relevantOption.index] = null;
                        }

                        const targetInput = element.parentElement.parentElement.querySelector(
                            'input[name="defaultvalue-lang[' + fieldname + '][' + lang + ']"]');
                        targetInput.value = predefinedValues[fieldname][lang];
                    }
                }
            }
        })();

        element.addEventListener('change', function (event) {
            const value = event.target.value || '';

            if (value !== '') {
                const clone = generateClone(mainInput, value);
                const label = generateLabel(event.target.selectedOptions[0].text, clone);
                const deleteButton = generateDeleteButton(event.target, value);
                const paragraph = generateParagraph(label, clone, deleteButton);

                element.backupLanguageSelection[event.target.selectedOptions[0].value] =
                    event.target.selectedOptions[0];
                event.target.options[event.target.selectedIndex] = null;

                mainInput.parentNode.parentNode.insertBefore(paragraph, event.target.parentNode);
            }
        });

        function generateClone(mainInput, language) {
            const clone = mainInput.cloneNode(true);
            clone.id = 'defaultvalue-lang-' + language;
            clone.name = 'defaultvalue-lang[' + fieldname + '][' + language + ']';
            clone.style.width = '100%';
            clone.style.marginLeft = '20px';
            clone.value = '';
            return clone;
        }

        function generateLabel(labelText, clone) {
            const label = document.createElement('label');
            label.classList = ['howto'];
            label.htmlFor = clone.id;
            label.style.minWidth = 'min-content';
            label.textContent = onOffice_loc_settings.label_default_value.replace('%s', labelText);
            return label;
        }

        function generateDeleteButton(target, language) {
            const deleteButton = document.createElement('span');
            deleteButton.id = 'deleteButtonLang-' + language;
            deleteButton.className = 'dashicons dashicons-dismiss deleteButtonLang';
            deleteButton.targetLanguage = language;
            deleteButton.style.display = 'block';
            deleteButton.style.verticalAlign = 'middle';

            deleteButton.addEventListener('click', function (deleteEvent) {
                const restoreValue = element.backupLanguageSelection[deleteEvent.target.targetLanguage];
                target.options.add(restoreValue);
                target.selectedIndex = 0;
                deleteEvent.target.parentElement.remove();
            });
            return deleteButton;
        }

        function generateParagraph(label, clone, deleteButton) {
            const paragraph = document.createElement('p');
            paragraph.classList = ['wp-clearfix'];
            paragraph.style.display = 'inline-flex';
            paragraph.style.width = '100%';
            paragraph.appendChild(label);
            paragraph.appendChild(clone);
            paragraph.appendChild(deleteButton);
            return paragraph;
        }
    });

    // single-select, multi-select, boolean
    document.querySelectorAll('select[name^=oopluginfieldconfigestatedefaultsvalues-value]')
        .forEach(function (mainInput) {
        const mainElement = mainInput.parentElement.parentElement.querySelector('span.menu-item-settings-name');
        if (mainElement === null) {
            return;
        }

        const fieldName = mainElement.textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldName) !== -1) {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldName);
        mainInput.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + ']';
        const predefinedValuesIsArray = (typeof predefinedValues[fieldName] === 'object') &&
            Array.isArray(predefinedValues[fieldName]);

        if (predefinedValuesIsArray) {
            mainInput.value = predefinedValues[fieldName][0];
        }

        const fieldList = onOffice_loc_settings.fieldList || {};
        let fieldDefinition = getFieldDefinition(fieldName);
        for (const module in fieldList) {
            if (fieldList[module][fieldName] !== undefined) {
                fieldDefinition = fieldList[module][fieldName];
            }
        }

        if (fieldDefinition.type === "multiselect") {
            const parent = mainInput.parentElement;
            mainInput.remove();

            const div = document.createElement('div');
            div.setAttribute('data-name', 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][]');
            div.classList.add('multiselect');

            const button = document.createElement('input');
            button.setAttribute('type', 'button');
            button.setAttribute('value', onOffice_loc_settings.field_multiselect_edit_values);
            button.classList.add('onoffice-multiselect-edit');
            div.appendChild(button);

            parent.appendChild(div);

            const multiselectOptions = {
                name_is_array: true,
                cb_class: 'onoffice-input'
            };
            const multiselect = new onOffice.multiselect(div, fieldDefinition.permittedvalues,
                onOffice_loc_settings.defaultvalues[fieldName] || [], multiselectOptions);
            button.onclick = (function(multiselect) {
                return function() {
                    multiselect.show();
                };
            })(multiselect);
        }
    });

    // numeric range, int, float, boolean
    document.querySelectorAll('input[name^=oopluginfieldconfigestatedefaultsvalues-value]').forEach(function (mainInput) {
        const mainElement = mainInput.parentElement.parentElement.querySelector('span.menu-item-settings-name');
        if (mainElement === null) {
            return;
        }
        const fieldName = mainElement.textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldName) !== -1 || fieldName === 'dummy_key') {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldName);
        const fieldDefinition = getFieldDefinition(fieldName);

        if (fieldDefinition.type === "boolean") {
            const parent = mainInput;
            const element = document.createElement('fieldset');
            const keys = Object.keys(fieldDefinition.permittedvalues).sort();
            parent.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + ']';

            element.className = 'onoffice-input-radio';
            keys.forEach(k => {
                const mainInputClone = parent.cloneNode(true);
                const label = document.createElement('label');
                onOffice.js_field_count += 1;
                mainInputClone.id = 'input_radio_js_' + onOffice.js_field_count;
                mainInputClone.value = k;
                label.textContent = fieldDefinition.permittedvalues[k];
                if(k == onOffice_loc_settings.defaultvalues[fieldName]){
                    mainInputClone.checked = true;
                }
                label.appendChild(mainInputClone);
                element.appendChild(label)
                parent.parentElement.appendChild(element)
            }); 
            mainInput.remove();
            return;
        }

        if (!fieldDefinition.rangefield &&
            [
                'integer', 'float', 'date', 'datetime',
                'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float',
                'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int',
                'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer',
                'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal'
            ].indexOf(fieldDefinition.type) >= 0) {
                mainInput.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][min]';
                mainInput.className = 'onoffice-input-date';
                const mainInputClone = mainInput.cloneNode(true);
                mainInputClone.id = 'input_js_' + onOffice.js_field_count;
                onOffice.js_field_count += 1;
                const labelFrom = mainInput.parentElement.querySelector('label[for='+mainInput.id+']')
                const labelUpTo = labelFrom.cloneNode(true);
                labelUpTo.htmlFor = mainInputClone.id;
                labelFrom.textContent = onOffice_loc_settings.label_default_value_from;
                labelUpTo.textContent = onOffice_loc_settings.label_default_value_up_to;
                mainInputClone.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][max]';
                if (fieldDefinition.type === 'date') {
                    mainInput.setAttribute('type', 'date');
                    mainInputClone.setAttribute('type', 'date');
                }
                mainInput.parentElement.appendChild(labelUpTo);
                mainInput.parentElement.appendChild(mainInputClone);
                const predefinedValuesIsObject = (typeof predefinedValues[fieldName] === 'object') &&
                    !Array.isArray(predefinedValues[fieldName]);
        
                if (predefinedValuesIsObject) {
                    mainInput.value = predefinedValues[fieldName]['min'] || '';
                    mainInputClone.value = predefinedValues[fieldName]['max'] || '';
                }
                return;
        }

        mainInput.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][min]';
        const mainInputClone = mainInput.cloneNode(true);
        mainInputClone.id = 'input_js_' + onOffice.js_field_count;
        onOffice.js_field_count += 1;
        const labelFrom = mainInput.parentElement.querySelector('label[for='+mainInput.id+']')
        const labelUpTo = labelFrom.cloneNode(true);
        labelUpTo.htmlFor = mainInputClone.id;
        labelFrom.textContent = onOffice_loc_settings.label_default_value_from;
        labelUpTo.textContent = onOffice_loc_settings.label_default_value_up_to;
        mainInputClone.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][max]';
        mainInput.parentElement.appendChild(labelUpTo);
        mainInput.parentElement.appendChild(mainInputClone);
        const predefinedValuesIsObject = (typeof predefinedValues[fieldName] === 'object') &&
            !Array.isArray(predefinedValues[fieldName]);

        if (predefinedValuesIsObject) {
            mainInput.value = predefinedValues[fieldName]['min'] || '';
            mainInputClone.value = predefinedValues[fieldName]['max'] || '';
        }
    });
};

onOffice.js_field_count = onOffice.js_field_count || 0;

document.addEventListener("addFieldItem", function(e) {
    const fieldName = e.detail.fieldname;
    const p = document.createElement('p');
    p.classList.add(['wp-clearfix']);
    const fieldDefinition = getFieldDefinition(fieldName);

    if (['varchar', 'text',
        'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:varchar',
        'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:Text'].indexOf(fieldDefinition.type) >= 0) {
            const element = e.detail.item.querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value]');
            const input = document.createElement('input');
            const select = document.createElement('select');
            select.id = 'select_js_' + onOffice.js_field_count;
            select.name = 'language-language';
            select.className = 'onoffice-input';
            input.id = 'select_js_' + onOffice.js_field_count;
            input.name = 'oopluginfieldconfigestatedefaultsvalues-value[]';
            input.className = 'onoffice-input';

            select.options.add(new Option(onOffice_loc_settings.label_choose_language, ''));
            const keys = Object.keys(onOffice_loc_settings.installed_wp_languages);
            keys.forEach(function (k) {
                const v = onOffice_loc_settings.installed_wp_languages[k];
                if (k !== onOffice_loc_settings.language_native) {
                    select.options.add(new Option(v, k));
                }
            });

            onOffice.js_field_count += 1;
            select.options.selectedIndex = 0;
    
            const label = document.createElement('label');
            label.htmlFor = select.id;
            label.className = 'howto';
            label.textContent = onOffice_loc_settings.label_add_language;
            p.appendChild(label);
            p.appendChild(select);
            input.setAttribute('type', 'text')
            input.setAttribute('size', '50')
            element.parentNode.replaceChild(input, element);
    } else if (['singleselect', 'multiselect'].indexOf(fieldDefinition.type) >= 0) {
        const element = e.detail.item.querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value]');
        const select = document.createElement('select');
        select.id = 'select_js_' + onOffice.js_field_count;
        select.name = 'oopluginfieldconfigestatedefaultsvalues-value[]';
        select.className = 'onoffice-input';

        select.options.add(new Option('', ''));
        const keys = Object.keys(fieldDefinition.permittedvalues);
        keys.forEach(function (k) {
            const v = fieldDefinition.permittedvalues[k];
            if (fieldDefinition.labelOnlyValues.indexOf(k) !== -1) {
                const group = document.createElement('optgroup');
                group.label = v;
                select.options.add(group);
            } else {
                select.options.add(new Option(v, k));
                if (k === fieldDefinition.selectedvalue) {
                    select.selectedIndex = select.options.length - 1;
                }
            }
        });

        onOffice.js_field_count += 1;
        select.options.selectedIndex = 0;
        element.parentNode.replaceChild(select, element);
    } else if(['boolean'].indexOf(fieldDefinition.type) >= 0){
        const element = e.detail.item.querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value]');
        const fieldset = document.createElement('fieldset');
        const keys = Object.keys(fieldDefinition.permittedvalues).sort();
        fieldset.className = 'onoffice-input-radio';

        keys.forEach(function (k) {
            const label = document.createElement('label');
            const input = document.createElement('input');
            input.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + ']';
            input.type = 'radio';
            input.value = k;
            input.className = 'onoffice-input';
            onOffice.js_field_count += 1;
            label.htmlFor = 'input_radio_js_' + onOffice.js_field_count;
            label.textContent = fieldDefinition.permittedvalues[k];
            if(k == onOffice_loc_settings.defaultvalues[fieldName]){
                input.checked = true;
            }
            label.appendChild(input);
            fieldset.appendChild(label)
        });

        element.parentNode.replaceChild(fieldset, element);
    }

    const paragraph = e.detail.item.querySelectorAll('.menu-item-settings p')[3];
    paragraph.parentNode.insertBefore(p, paragraph.nextSibling);

    const index = onOffice.default_values_inputs_converted.indexOf(fieldName);
    if (index !== -1) {
        delete onOffice.default_values_inputs_converted[index];
    }

    onOffice.default_values_input_converter();
});

function getFieldDefinition(fieldName) {
    const fieldList = onOffice_loc_settings.fieldList || {};
    for (const module in fieldList) {
        if (fieldList[module][fieldName] !== undefined) {
            return fieldList[module][fieldName];
        }
    }
    return {};
}

onOffice.default_values_input_converter();