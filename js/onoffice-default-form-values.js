onOffice = onOffice || {};

// polyfill
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

(function() {
    document.querySelectorAll('select[name=language-language].onoffice-input').forEach(function(element) {
        element.backupLanguageSelection = {};

        element.addEventListener('change', function(event) {
            var value = event.srcElement.value || '';

            if (value !== '') {
                var mainInput = element.parentElement.parentElement.querySelector('input[name^=oopluginfieldconfigformdefaultsvalues-value].onoffice-input');
                var clone = mainInput.cloneNode(true);
                clone.id = 'defaultvalue-lang-' + value;
                clone.name = 'defaultvalue-lang[' + value + ']';
                clone.style.width = '100%';
                clone.style.marginLeft = '20px';

                var label = document.createElement('label');
                label.classList = ['howto'];
                label.htmlFor = clone.id;
                label.style.minWidth = 'min-content';
                label.textContent = event.srcElement.selectedOptions[0].text;

                var deleteButton = document.createElement('span');
                deleteButton.id = "deleteButtonLang-" + value;
                deleteButton.className = "dashicons dashicons-dismiss deleteButtonLang";
                deleteButton.targetLanguage = value;
                deleteButton.style.display = "block";
                deleteButton.style.verticalAlign = 'middle';

                var paragraph = document.createElement('p');
                paragraph.classList = ['wp-clearfix'];
                paragraph.style.display = 'inline-flex';
                paragraph.style.width = '100%';
                paragraph.appendChild(label);
                paragraph.appendChild(clone);
                paragraph.appendChild(deleteButton);

                element.backupLanguageSelection[event.srcElement.selectedOptions[0].value] = event.srcElement.selectedOptions[0].text;
                event.srcElement.options[event.srcElement.selectedIndex] = null;

                mainInput.parentNode.parentNode.insertBefore(paragraph, event.srcElement.parentNode);
            }
        });
    });
})();