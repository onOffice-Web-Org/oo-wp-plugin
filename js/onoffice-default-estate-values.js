onOffice = onOffice || {};
onOffice.default_values_inputs_converted = onOffice.default_values_inputs_converted || [];

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

onOffice.default_values_input_converter = function () {
    var predefinedValues = onOffice_loc_settings.defaultvalues || {};

    // plaintext
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function (element) {
        element.backupLanguageSelection = {};
        var mainInput = element.parentElement.parentElement
            .querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value].onoffice-input');
        var fieldname = element.parentElement.parentElement.parentElement
            .querySelector('span.menu-item-settings-name').textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldname) !== -1) {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldname);
        mainInput.name = 'defaultvalue-lang[' + fieldname + '][native]';

        (function () {
            if (predefinedValues[fieldname] !== undefined) {
                var predefinedValuesIsObject = (typeof predefinedValues[fieldname] === 'object') &&
                    !Array.isArray(predefinedValues[fieldname]);
                if (predefinedValuesIsObject) {
                    for (var lang in predefinedValues[fieldname]) {
                        var relevantOption = element.querySelector('option[value=' + lang + ']');
                        if (lang !== 'native') {
                            var clone = generateClone(mainInput, lang);
                            var label = generateLabel(relevantOption.text || '', clone);
                            var deleteButton = generateDeleteButton(element, lang);
                            var paragraph = generateParagraph(label, clone, deleteButton);
                            mainInput.parentNode.parentNode.insertBefore(paragraph, element.parentNode);
                            element.backupLanguageSelection[lang] = relevantOption;
                            element.options[relevantOption.index] = null;
                        }

                        var targetInput = element.parentElement.parentElement.querySelector(
                            'input[name="defaultvalue-lang[' + fieldname + '][' + lang + ']"]');
                        targetInput.value = predefinedValues[fieldname][lang];
                    }
                }
            }
        })();

        element.addEventListener('change', function (event) {
            var value = event.srcElement.value || '';

            if (value !== '') {
                var clone = generateClone(mainInput, value);
                var label = generateLabel(event.srcElement.selectedOptions[0].text, clone);
                var deleteButton = generateDeleteButton(event.srcElement, value);
                var paragraph = generateParagraph(label, clone, deleteButton);

                element.backupLanguageSelection[event.srcElement.selectedOptions[0].value] =
                    event.srcElement.selectedOptions[0];
                event.srcElement.options[event.srcElement.selectedIndex] = null;

                mainInput.parentNode.parentNode.insertBefore(paragraph, event.srcElement.parentNode);
            }
        });

        function generateClone(mainInput, language) {
            var clone = mainInput.cloneNode(true);
            clone.id = 'defaultvalue-lang-' + language;
            clone.name = 'defaultvalue-lang[' + fieldname + '][' + language + ']';
            clone.style.width = '100%';
            clone.style.marginLeft = '20px';
            clone.value = '';
            return clone;
        }

        function generateLabel(labelText, clone) {
            var label = document.createElement('label');
            label.classList = ['howto'];
            label.htmlFor = clone.id;
            label.style.minWidth = 'min-content';
            label.textContent = onOffice_loc_settings.label_default_value.replace('%s', labelText);
            return label;
        }

        function generateDeleteButton(srcElement, language) {
            var deleteButton = document.createElement('span');
            deleteButton.id = 'deleteButtonLang-' + language;
            deleteButton.className = 'dashicons dashicons-dismiss deleteButtonLang';
            deleteButton.targetLanguage = language;
            deleteButton.style.display = 'block';
            deleteButton.style.verticalAlign = 'middle';

            deleteButton.addEventListener('click', function (deleteEvent) {
                var restoreValue = element.backupLanguageSelection[deleteEvent.srcElement.targetLanguage];
                srcElement.options.add(restoreValue);
                srcElement.selectedIndex = 0;
                deleteEvent.srcElement.parentElement.remove();
            });
            return deleteButton;
        }

        function generateParagraph(label, clone, deleteButton) {
            var paragraph = document.createElement('p');
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
        var mainElement = mainInput.parentElement.parentElement.querySelector('span.menu-item-settings-name');
        if (mainElement === null) {
            return;
        }

        var fieldName = mainElement.textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldName) !== -1) {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldName);
        mainInput.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + ']';
        var predefinedValuesIsArray = (typeof predefinedValues[fieldName] === 'object') &&
            Array.isArray(predefinedValues[fieldName]);

        if (predefinedValuesIsArray) {
            mainInput.value = predefinedValues[fieldName][0];
        }

        var fieldList = onOffice_loc_settings.fieldList || {};
        var fieldDefinition = getFieldDefinition(fieldName);
        for (var module in fieldList) {
            if (fieldList[module][fieldName] !== undefined) {
                fieldDefinition = fieldList[module][fieldName];
            }
        }

        if (fieldDefinition.type === "multiselect") {
            var parent = mainInput.parentElement;
            mainInput.remove();

            var div = document.createElement('div');
            div.setAttribute('data-name', 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][]');
            div.classList.add('multiselect');

            var button = document.createElement('input');
            button.setAttribute('type', 'button');
            button.setAttribute('value', onOffice_loc_settings.field_multiselect_edit_values);
            button.classList.add('onoffice-multiselect-edit');
            div.appendChild(button);

            parent.appendChild(div);

            var multiselectOptions = {
                name_is_array: true,
                cb_class: 'onoffice-input'
            };
            var multiselect = new onOffice.multiselect(div, fieldDefinition.permittedvalues,
                onOffice_loc_settings.defaultvalues[fieldName] || [], multiselectOptions);
            button.onclick = (function(multiselect) {
                return function() {
                    multiselect.show();
                };
            })(multiselect);
        } else if (fieldDefinition.type === "boolean") {
            mainInput.innerHTML = ""; // remove options
            mainInput.options.add(new Option(fieldDefinition.permittedvalues[0], '0'));
            mainInput.options.add(new Option(fieldDefinition.permittedvalues[1], '1'));
            mainInput.selectedIndex = onOffice_loc_settings.defaultvalues[fieldName] || '0';
        }
    });

    // numeric range, int, float
    document.querySelectorAll('input[name^=oopluginfieldconfigestatedefaultsvalues-value]').forEach(function (mainInput) {
        var mainElement = mainInput.parentElement.parentElement.querySelector('span.menu-item-settings-name');
        if (mainElement === null) {
            return;
        }
        var fieldName = mainElement.textContent;
        if (onOffice.default_values_inputs_converted.indexOf(fieldName) !== -1 || fieldName === 'dummy_key') {
            return;
        }
        onOffice.default_values_inputs_converted.push(fieldName);
        var fieldDefinition = getFieldDefinition(fieldName);

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
                var mainInputClone = mainInput.cloneNode(true);
                mainInputClone.id = 'input_js_' + onOffice.js_field_count;
                onOffice.js_field_count += 1;
                var labelFrom = mainInput.parentElement.querySelector('label[for='+mainInput.id+']')
                var labelUpTo = labelFrom.cloneNode(true);
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
                var predefinedValuesIsObject = (typeof predefinedValues[fieldName] === 'object') &&
                    !Array.isArray(predefinedValues[fieldName]);
        
                if (predefinedValuesIsObject) {
                    mainInput.value = predefinedValues[fieldName]['min'] || '';
                    mainInputClone.value = predefinedValues[fieldName]['max'] || '';
                }
                return;
        }

        mainInput.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][min]';
        var mainInputClone = mainInput.cloneNode(true);
        mainInputClone.id = 'input_js_' + onOffice.js_field_count;
        onOffice.js_field_count += 1;
        var labelFrom = mainInput.parentElement.querySelector('label[for='+mainInput.id+']')
        var labelUpTo = labelFrom.cloneNode(true);
        labelUpTo.htmlFor = mainInputClone.id;
        labelFrom.textContent = onOffice_loc_settings.label_default_value_from;
        labelUpTo.textContent = onOffice_loc_settings.label_default_value_up_to;
        mainInputClone.name = 'oopluginfieldconfigestatedefaultsvalues-value[' + fieldName + '][max]';
        mainInput.parentElement.appendChild(labelUpTo);
        mainInput.parentElement.appendChild(mainInputClone);
        var predefinedValuesIsObject = (typeof predefinedValues[fieldName] === 'object') &&
            !Array.isArray(predefinedValues[fieldName]);

        if (predefinedValuesIsObject) {
            mainInput.value = predefinedValues[fieldName]['min'] || '';
            mainInputClone.value = predefinedValues[fieldName]['max'] || '';
        }
    });
};

onOffice.js_field_count = onOffice.js_field_count || 0;

document.addEventListener("addFieldItem", function(e) {
    var fieldName = e.detail.fieldname;
    var p = document.createElement('p');
    p.classList.add(['wp-clearfix']);
    var fieldDefinition = getFieldDefinition(fieldName);

    if (['varchar', 'text',
        'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:varchar',
        'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:Text'].indexOf(fieldDefinition.type) >= 0) {
            var element = e.detail.item.querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value]');
            var input = document.createElement('input');
            var select = document.createElement('select');
            select.id = 'select_js_' + onOffice.js_field_count;
            select.name = 'language-language';
            select.className = 'onoffice-input';
            input.id = 'select_js_' + onOffice.js_field_count;
            input.name = 'oopluginfieldconfigestatedefaultsvalues-value[]';
            input.className = 'onoffice-input';
    
            select.options.add(new Option(onOffice_loc_settings.label_choose_language, ''));
            var keys = Object.keys(onOffice_loc_settings.installed_wp_languages);
            keys.forEach(function (k) {
                var v = onOffice_loc_settings.installed_wp_languages[k];
                if (k === onOffice_loc_settings.language_native) {
                    k = 'native';
                }
                select.options.add(new Option(v, k));
            });
    
            onOffice.js_field_count += 1;
            select.options.selectedIndex = 0;
    
            var label = document.createElement('label');
            label.htmlFor = select.id;
            label.className = 'howto';
            label.textContent = onOffice_loc_settings.label_add_language;
            p.appendChild(label);
            p.appendChild(select);
            input.setAttribute('type', 'text')
            input.setAttribute('size', '50')
            element.parentNode.replaceChild(input, element);
    } else if (['singleselect', 'multiselect', 'boolean'].indexOf(fieldDefinition.type) >= 0) {
        var element = e.detail.item.querySelector('input[name^=oopluginfieldconfigestatedefaultsvalues-value]');
        var select = document.createElement('select');
        select.id = 'select_js_' + onOffice.js_field_count;
        select.name = 'oopluginfieldconfigestatedefaultsvalues-value[]';
        select.className = 'onoffice-input';

        select.options.add(new Option('', ''));
        var keys = Object.keys(fieldDefinition.permittedvalues);
        keys.forEach(function (k) {
            var v = fieldDefinition.permittedvalues[k];
            if (fieldDefinition.labelOnlyValues.indexOf(k) !== -1) {
                var group = document.createElement('optgroup');
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
    }

    var paragraph = e.detail.item.querySelectorAll('.menu-item-settings p')[2];
    paragraph.parentNode.insertBefore(p, paragraph.nextSibling);

    var index = onOffice.default_values_inputs_converted.indexOf(fieldName);
    if (index !== -1) {
        delete onOffice.default_values_inputs_converted[index];
    }

    onOffice.default_values_input_converter();
});

function getFieldDefinition(fieldName) {
    var fieldList = onOffice_loc_settings.fieldList || {};
    for (var module in fieldList) {
        if (fieldList[module][fieldName] !== undefined) {
            return fieldList[module][fieldName];
        }
    }
    return {};
}

onOffice.default_values_input_converter();