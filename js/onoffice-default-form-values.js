onOffice = onOffice || {};

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

(function() {
    var predefinedValues = onOffice_loc_settings.defaultvalues || {};

    // plaintext
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function(element) {
        element.backupLanguageSelection = {};
        var mainInput = element.parentElement.parentElement.querySelector('input[name^=oopluginfieldconfigformdefaultsvalues-value].onoffice-input');
        var fieldname = element.parentElement.parentElement.parentElement.querySelector('span.menu-item-settings-name').textContent;
        mainInput.name = 'defaultvalue-lang[' + fieldname + '][native]';

        (function() {
            if (predefinedValues[fieldname] !== undefined) {
                var predefinedValuesIsObject = (typeof predefinedValues[fieldname] === 'object') &&
                   !Array.isArray(predefinedValues[fieldname]);
                if (predefinedValuesIsObject) {
                    for (var lang in predefinedValues[fieldname]) {
                        var relevantOption = element.querySelector('option[value=' + lang +']');
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

            element.addEventListener('change', function(event) {
            var value = event.srcElement.value || '';

            if (value !== '') {
                var clone = generateClone(mainInput, value);
                var label = generateLabel(event.srcElement.selectedOptions[0].text, clone);
                var deleteButton = generateDeleteButton(event.srcElement, value);
                var paragraph = generateParagraph(label, clone, deleteButton);

                element.backupLanguageSelection[event.srcElement.selectedOptions[0].value] = event.srcElement.selectedOptions[0];
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
                label.textContent = labelText;
            return label;
        }

        function generateDeleteButton(srcElement, language) {
            var deleteButton = document.createElement('span');
            deleteButton.id = 'deleteButtonLang-' + language;
            deleteButton.className = 'dashicons dashicons-dismiss deleteButtonLang';
            deleteButton.targetLanguage = language;
            deleteButton.style.display = 'block';
            deleteButton.style.verticalAlign = 'middle';

            deleteButton.addEventListener('click', function(deleteEvent) {
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

    // single-select
    document.querySelectorAll('select[name^=oopluginfieldconfigformdefaultsvalues-value]').forEach(function(mainInput) {
        var fieldName = mainInput.parentElement.parentElement.querySelector('span.menu-item-settings-name').textContent;

        var predefinedValuesIsArray = (typeof predefinedValues[fieldName] === 'object') &&
            Array.isArray(predefinedValues[fieldName]);

        if (predefinedValuesIsArray) {
            mainInput.value = predefinedValues[fieldName][0];
        }
    });
})();