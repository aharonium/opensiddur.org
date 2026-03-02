jQuery(document).ready(function($) {
    $('#load-filters-button').on('click', function() {
        var button = $(this);
        var container = $('#filters-container');
        button.prop('disabled', true).text('Loading filters...');

        $.post(ac_ajax_object.ajax_url, {
            action: 'ac_ajax_load_filters',
            user_id: ac_ajax_object.user_id
        }, function(response) {
            if (response.success) {
                container.html(response.data);
                button.remove();

                // Initialize accordion on new content
                if (typeof initializeAccordions === 'function') {
                    initializeAccordions(container[0]);
                }
            } else {
                container.html('<p>Failed to load filters.</p>');
                button.prop('disabled', false).text('Show Filters');
            }
        });
    });
});
