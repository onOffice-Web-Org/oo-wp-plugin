var onOffice = onOffice || {};
jQuery(document).ready(function($) {
    let sortingSelection = $("#viewrecordssorting").find("[name=oopluginlistviews-sortingtypedirection]").val();
    onOffice.sortingSelection = function(sortingSelection) {
        $("#viewrecordssorting").find('p.wp-clearfix').hide();

        let displayFieldsDefaultSortValue = [
            'oopluginlistviews-sortingtypedirection',
            'oopluginlistviews-sortby',
            'oopluginlistviews-sortorder',
        ];

        let displayFieldsRandomSortValue = [
            'oopluginlistviews-sortingtypedirection',
        ];

        let displayFieldsUserSelectionValue = [
            'oopluginlistviews-sortingtypedirection',
            'oopluginsortbyuservalues-sortbyuservalue',
            'oopluginlistviews-sortByUserDefinedDefault',
            'oopluginlistviews-sortByUserDefinedDirection',
        ];

        if (sortingSelection === 'default_sort'){
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if(displayFieldsDefaultSortValue.includes($(item).attr('name'))){
                    $(item).closest('p.wp-clearfix').show();
                }
            });
        }

        if (sortingSelection === 'random_order'){
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if(displayFieldsRandomSortValue.includes($(item).attr('name'))){
                    $(item).closest('p.wp-clearfix').show();
                }
            });
        }

        if (sortingSelection === 'user_selection'){
            $("#viewrecordssorting").find("input, select").each(function (key, item) {
                if(displayFieldsUserSelectionValue.includes($(item).attr('name'))){
                    $(item).closest('p.wp-clearfix').show();
                }
            });
        }

        onOffice.eventSortingChange();
    };

    onOffice.eventSortingChange = function(){
        $("#viewrecordssorting").find("[name=oopluginlistviews-sortingtypedirection]").on('change', function () {
            $("#viewrecordssorting").find('p.wp-clearfix').show();
            let sortingSelection = $("#viewrecordssorting").find("[name=oopluginlistviews-sortingtypedirection]").val();
            onOffice.sortingSelection(sortingSelection)
        })
    };

	onOffice.sortingSelection(sortingSelection);
});