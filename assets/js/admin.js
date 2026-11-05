(function($){
    // Tab switching for Checkout Fields Editor
    $(document).on('click', '.w3opcfe-tabs a.nav-tab', function(e){
        e.preventDefault();
        var tab = $(this).data('tab');
        if (!tab) return;

        $('.w3opcfe-tabs a.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.w3opcfe-tab-pane').hide();
        $('#w3opcfe-tab-' + tab).show();

        try {
            sessionStorage.setItem('w3opcfe_checkout_fields_tab', tab);
        } catch(err){}
    });

    // Restore active tab after form save/reload
    try {
        var savedTab = sessionStorage.getItem('w3opcfe_checkout_fields_tab');
        if (savedTab && $('#w3opcfe-tab-' + savedTab).length) {
            $('.w3opcfe-tabs a.nav-tab[data-tab="' + savedTab + '"]').trigger('click');
        }
    } catch(err){}

    // Toggle field dependent options & disabled state on modern switch change
    $(document).on('change', '.w3opcfe-field-card input.w3opcfe-enable-toggle', function(){
        var $card = $(this).closest('.w3opcfe-field-card');
        var $options = $card.find('.w3opcfe-dependent-options');
        if (this.checked) {
            $card.removeClass('is-disabled');
            $options.slideDown(200);
        } else {
            $card.addClass('is-disabled');
            $options.slideUp(200);
        }
    });

    // Legacy handler fallback
    $('input.w3opcfe-enable-field[type=checkbox]').on('change', function(){
        $(this).closest('.w3opcfe-field-wrapper').find('.w3opcfe-dependent-fields').slideToggle(this.checked);
    });
})(jQuery);
