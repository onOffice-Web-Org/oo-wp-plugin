var onOffice = onOffice || {};
onOffice.settings = onOffice_loc_settings;
window.wp = window.wp || {};

const estatePage = 'onoffice_page_onoffice-estates';
if (onOffice.settings.page === estatePage) {
    var fields = ['name', 'template', 'filterId', 'list_type'];
}

(function ($) {
    window.inlineEditPost = {
        init: function () {
            const self = this;
            const inlineEdit = $("#inline-edit");
            self.what = "#post-";

            $(".cancel", inlineEdit).on("click", function () {
                return self.revert();
            });

            $(".save", inlineEdit).on("click", function () {
                return self.save(this);
            });

            $('td', inlineEdit).on( 'keydown', function(e){
                if ( e.which === 13 && ! $( e.target ).hasClass( 'cancel' ) ) {
                    return self.save(this);
                }
            });

            $("#the-list").on("click", ".editinline", function () {
                $(this).attr("aria-expanded", "true");
                self.quickEdit(this);
            });
        },
        quickEdit: function (id) {
            this.revert();
            if (typeof id === "object") {
                id = this.getItemId(id);
            }
            const cloneInlineEdit = $("#inline-edit").clone(true);
            $(this.what + id).removeClass("is-expanded").hide().after(cloneInlineEdit).after('<tr class="hidden"></tr>');
            $(cloneInlineEdit).attr("id", "quickedit-" + id).addClass("inline-editor").show();

            const rowData = $(inlineEditPost.what + id).data('inline-data');
            for (let index = 0; index < fields.length; index++) {
                let value = $('.' + fields[index], rowData);
                value = rowData[fields[index]];
                $(':input[name="' + fields[index] + '"]', cloneInlineEdit).val(value);
            }
            $(".ptitle", cloneInlineEdit).trigger("focus");
            return false;
        },
        save: function (id) {
            let params, dataForm;
        
            if (typeof id === 'object') {
                id = this.getItemId(id);
            }
        
            $('table.widefat .spinner').addClass('is-active');
        
            params = {
                page: 'onoffice-estates',
                action: 'update_estate_list_view',
                record_id: id,
            };
        
            dataForm = $('#quickedit-' + id).find(':input').serialize();
            params = dataForm + '&' + $.param(params);
            $.post(onOffice.settings.ajaxurl, params,
                function (response) {
                    const $errorNotice = $('#quickedit-' + id + ' .inline-edit-save .notice-error'),
                        $error = $errorNotice.find('.error');
                    $('table.widefat .spinner').removeClass('is-active');
                    if (response.success) {
                        const rowElement = $(inlineEditPost.what + id);
                        fields.forEach(function (fieldName) {
                            updateField(id, rowElement, fieldName, response.data[fieldName]);
                        });
                        rowElement.siblings('tr.hidden').remove();
                        $('#quickedit-' + id).before(response).remove();
                        rowElement.hide().fadeIn(400, function () {
                            $(this).find('.editinline')
                                .attr('aria-expanded', 'false')
                                .trigger('focus');
                        });
                    } else {
                        $errorNotice.removeClass('hidden');
                        $error.text(onOffice.settings.error);
                    }
                },
                'json'
            );

            return false;
        },
        revert: function () {
            const $tableWideFat = $('.widefat');
            let id = $('.inline-editor', $tableWideFat).attr('id');

            if (id) {
                $('.spinner', $tableWideFat).removeClass('is-active');

                $('#' + id).siblings('tr.hidden').addBack().remove();
                id = id.substr(id.lastIndexOf('-') + 1);

                $(this.what + id).show().find('.editinline')
                    .attr('aria-expanded', 'false')
                    .trigger('focus');
            }

            return false;
        },
        getItemId: function (element) {
            const id = $(element).closest("tr").attr("id");
            const ids = id.split("-");
            return ids[ids.length - 1];
        }
    };

    function updateField(id, rowElement, fieldName, value) {
        const rowData = rowElement.data('inline-data');
        rowData[fieldName] = value;
        const updatedInlineValue = JSON.stringify(rowData);
        rowElement.attr('data-inline-data', updatedInlineValue);
        if ($("#quickedit-" + id).find("#" + fieldName).is('select')) {
            value = $("#quickedit-" + id).find("#" + fieldName + " option[value='" + value + "']").text();
        }
        if (fieldName === 'filterId') {
            fieldName = 'filtername';
        }
        if (fieldName === 'name') {
            const element = rowElement.find('.shortcode');
            updateShortCodeField(element, value);
        }
        const element = rowElement.find('.' + fieldName);
        const parts = element.html()?.split(/(<[^>]+>)/);
        const result = element.html()?.replace(parts[0], value);
        element.html(result);
    }

    function updateShortCodeField(element, value) {
        const shortcode = element.html();
        const parts = shortcode.split(/(<[^>]+>)/);
        parts[1] = parts[1].replace(/(&quot;)([^&]+)(&quot;)/, '$1' + value + '$3');
        parts[3] = parts[3].replace(/(&quot;)([^&]+)(&quot;)/, '$1' + value + '$3');
        const updatedShortcode = parts.join('');

        element.html(updatedShortcode);
    }

    $(function () {
        inlineEditPost.init();
    });
})(jQuery);