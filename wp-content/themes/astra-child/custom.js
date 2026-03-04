


<!-- Start of Tawk.to Script -->

    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function(){
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/63e67d62c2f1ac1e203295fe/1gou5jihf';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();

<!-- End of Tawk.to Script -->


    jQuery(document).ready(function($) {
        $('.quantity-btn-wrapper').on('click', 'button.plus, button.minus', function() {
            var qty = $(this).closest('.quantity').find('.qty');
            var currentVal = parseFloat(qty.val());
            var max = parseFloat(qty.attr('max'));
            var min = parseFloat(qty.attr('min'));
            var step = parseFloat(qty.attr('step'));

            if ($(this).is('.plus')) {
                if (currentVal < max || isNaN(max)) {
                    qty.val(currentVal + step).change();
                }
            } else {
                if (currentVal > min) {
                    qty.val(currentVal - step).change();
                }
            }
        });
    });

    // How to Order Modal
    jQuery(document).ready(function($) {
        $(".custom-modal-button").click(function() {
            $(".custom-modal").fadeIn();
        });
        $(".custom-modal-close, .custom-modal-overlay").click(function() {
            $(".custom-modal").fadeOut();
        });
    });

