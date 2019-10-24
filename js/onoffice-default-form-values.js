onOffice = onOffice || {};

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

(function() {
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function(element) {
        element.backupLanguageSelection = {};
        var mainInput = element.parentElement.parentElement.querySelector('input[name^=oopluginfieldconfigformdefaultsvalues-value].onoffice-input');
        var fieldname = element.parentElement.parentElement.parentElement.querySelector('span.menu-item-settings-name').textContent;
        mainInput.name = 'defaultvalue-lang[' + fieldname + '][native]';

        element.addEventListener('change', function(event) {
            var value = event.srcElement.value || '';

            if (value !== '') {
                var clone = mainInput.cloneNode(true);
                clone.id = 'defaultvalue-lang-' + value;
                clone.name = 'defaultvalue-lang[' + fieldname + '][' + value + ']';
                clone.style.width = '100%';
                clone.style.marginLeft = '20px';
                clone.value = '';

                var label = document.createElement('label');
                label.classList = ['howto'];
                label.htmlFor = clone.id;
                label.style.minWidth = 'min-content';
                label.textContent = event.srcElement.selectedOptions[0].text;

                var deleteButton = document.createElement('span');
                deleteButton.id = 'deleteButtonLang-' + value;
                deleteButton.className = 'dashicons dashicons-dismiss deleteButtonLang';
                deleteButton.targetLanguage = value;
                deleteButton.style.display = 'block';
                deleteButton.style.verticalAlign = 'middle';

                deleteButton.addEventListener('click', function(deleteEvent) {
                    var restoreValue = element.backupLanguageSelection[deleteEvent.srcElement.targetLanguage];
                    event.srcElement.options.add(restoreValue);
                    event.srcElement.selectedIndex = 0;
                    deleteEvent.srcElement.parentElement.remove();
                });

                var paragraph = document.createElement('p');
                paragraph.classList = ['wp-clearfix'];
                paragraph.style.display = 'inline-flex';
                paragraph.style.width = '100%';
                paragraph.appendChild(label);
                paragraph.appendChild(clone);
                paragraph.appendChild(deleteButton);

                element.backupLanguageSelection[event.srcElement.selectedOptions[0].value] = event.srcElement.selectedOptions[0];
                event.srcElement.options[event.srcElement.selectedIndex] = null;

                mainInput.parentNode.parentNode.insertBefore(paragraph, event.srcElement.parentNode);
            }
        });
    });
})();