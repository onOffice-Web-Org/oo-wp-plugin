var onOffice = onOffice || {};
jQuery(document).ready(function ($) {
    var sortByUserValue = $("#viewrecordssorting").find('[name="oopluginsortbyuservalues-sortbyuservalue[]"]');
    var sortByUserDefinedDirection = $("#viewrecordssorting").find("[name=oopluginlistviews-sortByUserDefinedDirection]");
    var sortRamdom = $("#viewrecordssorting").find("[name=oopluginlistviews-random]");
    var sortingSelection = $("#viewrecordssorting").find("[name=oopluginlistviews-sortBySetting]");

    var displayFieldsDefaultSortValue = [
        'oopluginlistviews-sortBySetting',
        'oopluginlistviews-sortby',
        'oopluginlistviews-sortorder',
    ];

    var displayFieldsRandomSortValue = [
        'oopluginlistviews-sortBySetting',
    ];

    var displayFieldsUserSelectionValue = [
        'oopluginlistviews-sortBySetting',
        'oopluginsortbyuservalues-sortbyuservalue[]',
        'oopluginlistviews-sortByUserDefinedDefault',
        'oopluginlistviews-sortByUserDefinedDirection',
    ];

    onOffice.generateSortByUserDefinedDefault = function () {
        var oldSelected = $("#viewrecordssorting")
            .find("[name=oopluginlistviews-sortByUserDefinedDefault] :selected").val();
        var selectedDirection = $("#viewrecordssorting")
            .find("[name=oopluginlistviews-sortByUserDefinedDirection] :selected").val();
        var standardSortInput = $("#viewrecordssorting")
            .find("[name=oopluginlistviews-sortByUserDefinedDefault]");
        var sortByInput = $("#viewrecordssorting")
            .find('[name="oopluginsortbyuservalues-sortbyuservalue[]"] optgroup option:selected');
        var directions = ['ASC', 'DESC'];
        var translationsMapping = onoffice_mapping_translations[selectedDirection];

        standardSortInput.find("option").remove();
        if (sortByInput.length > 0) {
            sortByInput.each(function (i, option) {
                for (var i = 0; i < directions.length; i++) {
                    if (option.value + '#' + directions[i] == oldSelected) {
                        standardSortInput.append("<option value='" + option.value + '#' + directions[i] + "' selected>"
                            + option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                    } else {
                        standardSortInput.append("<option value='" + option.value + '#' + directions[i] + "'>"
                            + option.text + " (" + translationsMapping[directions[i]] + ")" + "</option>");
                    }
                }
            });
        }
        standardSortInput.trigger("chosen:updated");
    };

    onOffice.sortingSelection = function (sortingSelectionVal) {
        if (sortingSelection.length) {
            $("#viewrecordssorting").find('p.wp-clearfix').hide();
            sortRamdom.prop('checked', false);
        }

        if (sortingSelectionVal === '0') {
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if (displayFieldsDefaultSortValue.includes($(item).attr('name'))) {
                    $(item).closest('p.wp-clearfix').show();
                }
            });
        } else if (sortingSelectionVal === '1') {
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if (displayFieldsUserSelectionValue.includes($(item).attr('name'))) {
                    $(item).closest('p.wp-clearfix').show();
                }
            });
        } else {
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if (displayFieldsRandomSortValue.includes($(item).attr('name'))) {
                    $(item).closest('p.wp-clearfix').show();
                }
            });
            sortingSelection.val('')
            sortRamdom.prop('checked', true);
        }

        onOffice.eventSortingChange();
    };

    onOffice.eventSortingChange = function () {
        $("#viewrecordssorting").find("[name=oopluginlistviews-sortBySetting]").on('change', function () {
            $("#viewrecordssorting").find('p.wp-clearfix').show();
            let sortingSelection = $("#viewrecordssorting").find("[name=oopluginlistviews-sortBySetting]").val();
            onOffice.sortingSelection(sortingSelection)
        })
    };

    onOffice.sortingSelection(sortingSelection.val());

    sortByUserValue.change(function () {
        onOffice.generateSortByUserDefinedDefault();
    });

    sortByUserDefinedDirection.change(function () {
        onOffice.generateSortByUserDefinedDefault();
    });
});
