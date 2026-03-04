(function($){
    $('input.opc-enable-field[type=checkbox]').on('change', function(){
        $(this).closest('.w3-opc-field-wrapper').find('.w3-opc-dependent-fields').slideToggle(this.checked);
    });
})(jQuery);