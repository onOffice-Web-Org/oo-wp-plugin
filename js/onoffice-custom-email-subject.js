jQuery(document).ready(function($) {
    const excludedFields = ['dummy_key', 'message', 'gdprcheckbox', 'DSGVOStatus'];
    let fields = getFieldsForShowTagEmailSubject();
    let cursorPosition;

    function handleLogicTagEmailSubject(editableSelector, suggestionsSelector, outputSelector) {
        const editableElement = $(editableSelector);
        const suggestionsElement = $(suggestionsSelector);
        const outputElement = $(outputSelector);

        if (outputElement.val()) {
            const { newValue } = replaceTagsWithSpans(outputElement.val());
            editableElement.html(newValue);
        }

        editableElement.on('paste', handlePaste)
                       .on('input click', () => handleInputAndClick(editableElement, suggestionsElement, outputElement));

        suggestionsElement.on('click', '.oo-suggestion-item', function() {
            handleSuggestionSelection($(this), editableElement, suggestionsElement, outputElement);
        });

        editableElement.on('click', '.oo-remove-tag', function() {
            handleTagRemove($(this), editableElement, outputElement);
        });

        $(document).on('click', (e) => {
            if (!editableElement.is(e.target) && !suggestionsElement.is(e.target) && suggestionsElement.has(e.target).length === 0) {
                suggestionsElement.hide();
            }
        });
    }

    function handlePaste(e) {
        e.preventDefault();
        pasteTextAtCursor((e.originalEvent || e).clipboardData.getData('text/plain'));
    }

    function pasteTextAtCursor(text) {
        const selection = window.getSelection();
        if (!selection || !selection.rangeCount) return;
        selection.deleteFromDocument();
        selection.getRangeAt(0).insertNode(document.createTextNode(text));
        selection.collapseToEnd();
    }

    function replaceTagsWithSpans(value) {
        let cursorPosition = 0;
        const newValue = value.replace(/%%([^%]+)%%/g, (match, p1, offset) => {
            const variableName = p1.trim();
            if (!variableName) return match;
            const variable = fields.find(variable => variable.value === variableName);
            const label = variable ? variable.label : variableName;

            cursorPosition = offset + match.length;
            return `<span class="oo-tag" contenteditable="false" data-value="${variableName}">${label} <span class="oo-remove-tag select2-selection__choice__remove"></span></span>&nbsp;`;
        });

        return { newValue, cursorPosition };
    }

    function displaySuggestions(beforeCursor, suggestionsElement) {
        const inputText = beforeCursor.slice(beforeCursor.lastIndexOf('%') + 1).trim();
        const lastPercentIndex = beforeCursor.lastIndexOf('%');
        const isValidInput = lastPercentIndex !== -1 && beforeCursor[lastPercentIndex + 1] !== ' ';
        if (!isValidInput) {
            suggestionsElement.hide();
            return;
        }

        const filteredVariables = inputText === '' || inputText.match(/^\s*$/)
            ? fields
            : fields.filter(variable => variable.label.toLowerCase().startsWith(inputText.toLowerCase()));

        suggestionsElement.empty().toggle(filteredVariables.length > 0);
        filteredVariables.forEach(variable => {
            suggestionsElement.append(`<div class="oo-suggestion-item" data-value="${variable.value}">${variable.label}</div>`);
        });
    }

    function handleInputAndClick(editableElement, suggestionsElement, outputElement) {
        const { newValue, cursorPosition } = replaceTagsWithSpans(editableElement.html());
        if (newValue !== editableElement.html()) {
            editableElement.html(newValue);
            setCursorPosition(editableElement[0], cursorPosition);
        }

        const beforeCursor = getTextBeforeCursor();
        const lastPercentIndex = beforeCursor.lastIndexOf('%');
        if (lastPercentIndex !== -1) {
            const str = beforeCursor.replace(/^\s+/, '');
            const hasAdjacentPercents = str.includes('%%');
            const isSinglePercent = checkOnlyPercentCharacter(beforeCursor, lastPercentIndex);

            if (!hasAdjacentPercents && (str.endsWith('%') || (str.includes('%') && str.split('%').pop().trim() !== '')) || isSinglePercent) {
                displaySuggestions(beforeCursor, suggestionsElement);
            } else {
                suggestionsElement.hide();
            }
        } else {
            suggestionsElement.hide();
        }
        saveCursorPosition();
        updateOutputField(editableElement, outputElement);
    }

    function setCursorPosition(element, position) {
        const range = document.createRange();
        const selection = window.getSelection();
        let currentNode = element.firstChild;
        let currentPosition = 0;

        while (currentNode) {
            const nodeLength = currentNode.nodeType === Node.TEXT_NODE ? currentNode.length : currentNode.textContent.length;

            if (currentPosition + nodeLength >= position) {
                range.setStart(currentNode, position - currentPosition);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                return;
            }

            currentPosition += nodeLength;
            currentNode = currentNode.nextSibling;
        }

        range.selectNodeContents(element);
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function checkOnlyPercentCharacter(text, index) {
        return text[index - 1] !== '%' && text[index + 1] !== '%';
    }

    function handleSuggestionSelection(suggestion, editableElement, suggestionsElement, outputElement) {
        const selectedValue = suggestion.data('value');
        const selectedLabel = suggestion.text();
        const newValue = `<span class="oo-tag" contenteditable="false" data-value="${selectedValue}">${selectedLabel} <span class="oo-remove-tag select2-selection__choice__remove"></span></span>&nbsp;`;

        if (cursorPosition) {
            cursorPosition.deleteContents();
            insertHtmlAtRange(newValue);
        }

        suggestionsElement.hide();
        updateOutputField(editableElement, outputElement);
    }

    function handleTagRemove(tagElement, editableElement, outputElement) {
        const parent = tagElement.parent();
        const nextSibling = parent[0].nextSibling;
        if (nextSibling && nextSibling.nodeType === Node.TEXT_NODE && nextSibling.nodeValue.startsWith('\u00A0')) {
            nextSibling.nodeValue = nextSibling.nodeValue.slice(1);
        }
        parent.remove();
        updateOutputField(editableElement, outputElement);
    }

    function saveCursorPosition() {
        const selection = window.getSelection();
        if (selection && selection.rangeCount > 0) {
            cursorPosition = selection.getRangeAt(0);
        }
    }

    function updateOutputField(editableElement, outputElement) {
        const text = editableElement.clone().find('.oo-remove-tag').remove().end().html();
        const replacedText = text.replace(/<span class="oo-tag" contenteditable="false" data-value="([^"]+)">[^<]+<\/span>/g, '%%$1%%').replace(/&nbsp;/g, ' ').trim();

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = replacedText;
        const plainText = tempDiv.textContent || tempDiv.innerText || '';

        outputElement.val(plainText);
    }

    function getTextBeforeCursor() {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return '';
        const tempRange = document.createRange();
        tempRange.setStart(selection.getRangeAt(0).startContainer, 0);
        tempRange.setEnd(selection.getRangeAt(0).startContainer, selection.getRangeAt(0).startOffset);

        return tempRange.toString();
    }

    function insertHtmlAtRange(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const fragment = document.createDocumentFragment();
        let node, lastNode;
        while ((node = tempDiv.firstChild)) {
            lastNode = fragment.appendChild(node);
        }

        if (!cursorPosition) return;

        const startContainer = cursorPosition.startContainer;
        if (startContainer.nodeType === Node.TEXT_NODE) {
            const textContent = startContainer.textContent;
            const cursorOffset = cursorPosition.startOffset;

            const lastPercentIndex = textContent.lastIndexOf('%', cursorOffset - 1);
            if (lastPercentIndex !== -1) {
                startContainer.textContent = textContent.slice(0, lastPercentIndex) + textContent.slice(cursorOffset);
                cursorPosition.setStart(startContainer, lastPercentIndex);
                cursorPosition.collapse(true);
            }
        }

        cursorPosition.insertNode(fragment);

        if (lastNode) {
            cursorPosition.setStartAfter(lastNode);
            cursorPosition.collapse(true);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(cursorPosition);
        }
    }

    function getFieldsForShowTagEmailSubject() {
        const fields = [];
        $('#sortableFieldsList .sortable-item').each(function() {
            const value = $(this).find('input[name^="filter_fields_order"][name$="[slug]"]').val();
            if (excludedFields.includes(value)) return;
            const label = $(this).find('.item-title').text().trim();
            fields.push({ value, label: `${label} (${value})` });
        });
        return fields;
    }

    function updateFields() {
        fields = getFieldsForShowTagEmailSubject();
    }

    function init() {
        document.addEventListener('fieldListUpdated', updateFields);
        handleLogicTagEmailSubject('.oo-email-subject-title', '.oo-email-subject-suggestions', '.oo-email-subject-output');
    }

    init();
});