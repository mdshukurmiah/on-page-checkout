(function($){
    $('input.w3opcfe-enable-field[type=checkbox]').on('change', function(){
        $(this).closest('.w3opcfe-field-wrapper').find('.w3opcfe-dependent-fields').slideToggle(this.checked);
    });
})(jQuery);
