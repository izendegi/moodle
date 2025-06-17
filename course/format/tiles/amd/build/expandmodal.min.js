define(['jquery'], function($) {
    return {
        init: function() {
            console.log('expandModal (AMD) initialized');

            // Delegación de eventos con jQuery
            $('body').on('click', 'a[data-command="expand2"]', function(e) {
                e.preventDefault();

                console.log('Expand button clicked');
                const modalContent = $('.modal-content');
                const icon = $(this).find('i');

                modalContent.toggleClass('modal-content-expanded');

                if (modalContent.hasClass('modal-content-expanded')) {
                    icon.removeClass('fa-expand').addClass('fa-compress').attr('title', 'Collapse');
                } else {
                    icon.removeClass('fa-compress').addClass('fa-expand').attr('title', 'Expand');
                }
            });
        }
    };
});
