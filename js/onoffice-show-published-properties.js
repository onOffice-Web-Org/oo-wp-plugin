jQuery(document).ready(function ($) {
    const selectorElements = [
        "oopluginlistviews-filterId",
        "oopluginlistviews-listtype",
        "oopluginlistviews-showreferenceestate"
    ];

    $(document).on('click', '#show-published-properties', function (event) {
        event.preventDefault();
        const button = $(this);
        const spinner = button.parent().find('.spinner');
        const message = button.parent().parent().find('.message-show-published-properties');

        let data = {
            'action': 'show_published_properties',
            'elements': {}
        };

        selectorElements.forEach((name) => {
            data.elements[name] = $(`select[name=${name}] option`).map(function() {
                return $(this).val();
            }).get();
        });

        spinner.addClass('is-active');

        jQuery.post(show_published_properties.ajaxurl, data, (response) => {
            if(response.success) {
                let showMessage = false;

                selectorElements.forEach((name) => {
                    const options = $(`select[name=${name}] option`);
                    const counts = response.data[name];
                    options.each(function(index) {
                        const count = counts[index];
                        const option = $(this);
                        let text = option.text().replace(/\(\d+ estates?\)/, '').trim();
                        option.text(`${text} (${count} ${show_published_properties.title})`);
                        if (count === 0) {
                            showMessage = true;
                        }
                    });
                });

                if (showMessage) {
                    message.show();
                }
            }
            spinner.removeClass('is-active');
            button.prop('disabled', true);
        }, 'json');
    });
});