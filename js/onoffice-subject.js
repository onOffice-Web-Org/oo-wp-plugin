jQuery(document).ready(function($) {
    const fieldList = onOffice_loc_settings.fieldList || {};
    const snippetVariables = getFieldDefinition();

    function setupInput(editableId, suggestionsId, outputId) {
        const editable = $(editableId);
        const suggestions = $(suggestionsId);
        const output = $(outputId);

        const initialValue = output.val();
        if (initialValue) {
            editable.html(replaceTags(initialValue));
        }

        editable.on('input', function() {
            const newValue = replaceTags(editable.html());
            if (newValue !== editable.html()) editable.html(newValue);
            showSuggestions($(this).text(), suggestions);
            updateOutput(editable, output);
        });

        editable.on('click', function(event) {
            handleEditableClick(event, $(this));
        });

        suggestions.on('click', '.oo-suggestion-item', function() {
            handleSuggestionClick($(this), editable, suggestions, output);
        });

        editable.on('click', '.oo-remove-tag', function() {
            $(this).parent().remove();
            updateOutput(editable, output);
        });

        $(document).on('click', function(e) {
            if (!editable.is(e.target) && !suggestions.is(e.target) && suggestions.has(e.target).length === 0) {
                suggestions.hide();
            }
        });
    }

    const replaceTags = (value) => {
        const pattern = /%%([^%]+)%%/g;
        return value.replace(pattern, (match, p1) => {
            const variable = snippetVariables.find(variable => variable.value === p1);
            const label = variable ? variable.label : p1;
            return `<span class="oo-tag" contenteditable="false" data-value="${p1}">${label} <span class="oo-remove-tag select2-selection__choice__remove"></span></span>`;
        });
    };

    function showSuggestions(value, suggestions) {
        const percentCount = (value.match(/%/g) || []).length;
        const hasDoublePercent = value.includes('%%');
        const hasSinglePercentWithSpace = value.split(/ +/).filter(part => part === '%').length === 1;

        if ((percentCount === 1 && !hasDoublePercent) || hasSinglePercentWithSpace) {
            const inputText = value.split('%').pop().trim();
            const filteredVariables = inputText === '' 
                ? snippetVariables.slice(0, 4) 
                : snippetVariables.filter(variable => variable.label.toLowerCase().startsWith(inputText.toLowerCase()));

            suggestions.empty().toggle(filteredVariables.length > 0);
            filteredVariables.forEach(variable => {
                suggestions.append(`<div class="oo-suggestion-item" data-value="${variable.value}">${variable.label}</div>`);
            });
        } else {
            suggestions.hide();
        }
    }

    function handleEditableClick(event, editable) {
        const text = editable.text();
        const range = document.createRange();
        const selection = window.getSelection();
        let offset = 0;

        if (editable[0].firstChild && event.target.firstChild) {
            range.setStart(editable[0].firstChild, 0);
            range.setEnd(event.target.firstChild, Math.min(selection.anchorOffset, event.target.firstChild.length));
            offset = range.toString().length;

            const beforeClick = text.substring(0, offset);
        }
    }

    function handleSuggestionClick(suggestion, editable, suggestions, output) {
        const selectedValue = suggestion.data('value');
        const selectedLabel = suggestion.text();
        const newValue = `<span class="oo-tag" contenteditable="false" data-value="${selectedValue}">${selectedLabel} <span class="oo-remove-tag select2-selection__choice__remove"></span></span>`;
        const value = editable.html();
        const lastPercentIndex = value.lastIndexOf('%');
        editable.html(value.slice(0, lastPercentIndex) + newValue);
        suggestions.hide();
        updateOutput(editable, output);
    }

    function updateOutput(editable, output) {
        const text = editable.clone().find('.oo-remove-tag').remove().end().html();
        const pattern = /<span class="oo-tag" contenteditable="false" data-value="([^"]+)">[^<]+<\/span>/g;
        const replacedText = text.replace(pattern, '%%$1%%').replace(/&nbsp;/g, ' ').trim();

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = replacedText;
        const plainText = tempDiv.textContent || tempDiv.innerText || '';

        output.val(plainText);
    }

    function getFieldDefinition() {
        return Object.entries(fieldList).flatMap(([module, fields]) =>
            Object.entries(fields).map(([fieldName, field]) => ({
                value: fieldName,
                label: `${field.label} (${module})`
            }))
        );
    }

    setupInput('.oo-subject-title', '.oo-subject-suggestions', '.oo-subject-output');
});