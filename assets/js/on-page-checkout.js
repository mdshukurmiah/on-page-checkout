jQuery(document).ready(function($) {
    'use strict';

    // Function to show loading overlay
    function w3_opc_showLoading(widget) {
        widget.addClass('opc-loading');
        widget.append('<div class="opc-loading-overlay"><div class="opc-loading-spinner"></div></div>');
    }

    // Function to hide loading overlay
    function w3_opc_hideLoading(widget) {
        widget.removeClass('opc-loading');
        widget.find('.opc-loading-overlay').remove();
    }

    // Function to get selected product ID
    function w3_opc_getSelectedProductId(form) {
        var productId = form.find('input[name="opc_product_id"]:checked').val();
        if (!productId) {
            productId = form.find('input[name="opc_product_id"]').val(); // For hidden input (single product)
        }
        return productId;
    }

    // Function to load checkout fields via AJAX
    function w3_opc_loadCheckoutFields(widget) {
        var form = widget.find('.opc-checkout-form');
        var productId = w3_opc_getSelectedProductId(form);
        var quantity = form.find('input[name="opc_quantity"]').val() || 1;
        var variation_id = form.find('select[name="variation_id"]').val() || 1;
        var checkoutFieldsContainer = widget.find('[id^="opc-checkout-fields-container-"]');

        if (!productId) {
            checkoutFieldsContainer.html('<p class="opc-info">' + (opc_ajax_object.no_product_selected_text || 'Please select a product.') + '</p>');
            return;
        }

        var currentFormData = form.serializeArray(); // all inputs in the form

        // Convert serializeArray into object
        var formDataObj = {};
        $.each(currentFormData, function(i, field){
            formDataObj[field.name] = field.value;
        });

        // Add product_id and quantity
        formDataObj.action = 'opc_load_checkout_fields';
        formDataObj.nonce = opc_ajax_object.nonce;
        formDataObj.product_id = productId;
        formDataObj.quantity = quantity;
        formDataObj.variation_id = variation_id;

        w3_opc_showLoading(widget);
        // checkoutFieldsContainer.html('<p>' + (opc_ajax_object.loading_text || 'Loading...') + '</p>');

        $.ajax({
            type: 'POST',
            url: opc_ajax_object.ajax_url,
            data: formDataObj, // send all data
            success: function(response) {
                if (response.success) {
                    checkoutFieldsContainer.html(response.data.fields);

                    // Re-trigger WooCommerce checkout events so gateway scripts re-init
                    $(document.body).trigger('init_checkout');
                    $(document.body).trigger('updated_checkout');
                    $(document.body).trigger('update_checkout');

                    // Re-bind payment_box show/hide scoped to this widget
                    var $widget = widget;
                    $widget.find('input[name="payment_method"]').on('change', function(){
                        var selected = $(this).val();
                        $widget.find('.payment_box').hide();
                        $widget.find('.payment_box.payment_method_' + selected).show();
                    });
                    console.log(response);
                } else {
                    checkoutFieldsContainer.html('<p class="opc-error">' + (response.data.message || 'Error loading checkout fields.') + '</p>');
                }
            },


            error: function() {
                checkoutFieldsContainer.html('<p class="opc-error">' + (opc_ajax_object.error_text || 'An error occurred. Please try again.') + '</p>');
            },
            complete: function() {
                w3_opc_hideLoading(widget);
            }
        });
    }

    // Initial load of checkout fields for each widget on the page
    $('.on-page-checkout-widget').each(function() {
        w3_opc_loadCheckoutFields($(this));
    });

    // Event handler for product selection change
    $(document).on('change', '.opc-product-selection input[name="opc_product_id"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3_opc_loadCheckoutFields(widget);
    });

    $(document).on('change', '.opc-product-selection select[name="variation_id"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3_opc_loadCheckoutFields(widget);
    });

    // Event handler for state change
    $(document).on('change', '.opc-checkout-form #billing_state', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3_opc_loadCheckoutFields(widget);
    });

    // Event handler for shipping change
    $(document).on('change', '#shipping_method input[type="radio"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3_opc_loadCheckoutFields(widget);
    });

    // Event handler for quantity change
    $(document).on('change', '.opc-quantity-selector input[name="opc_quantity"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3_opc_loadCheckoutFields(widget);
    });

    // Event handler for payment method selection
    $(document).on('change', 'input[name="payment_method"]', function() {
        var selectedMethod = $(this).val();
        $('.payment_box').hide();
        $('.payment_box.payment_method_' + selectedMethod).show();
    });

    // Event handler for form submission
    $(document).on('submit', '.opc-checkout-form', function(e) {
        e.preventDefault();

        var form = $(this);
        var widget = form.closest('.on-page-checkout-widget');
        var submitButton = form.find('.opc-place-order-button');
        var productId = w3_opc_getSelectedProductId(form);
        var quantity = form.find('input[name="opc_quantity"]').val() || 1;

        if (!productId) {
            alert(opc_ajax_object.no_product_selected_text || 'Please select a product.');
            return;
        }

        // Check if payment method is selected
        var paymentMethod = form.find('input[name="payment_method"]:checked').val();
        if (!paymentMethod) {
            alert('Please select a payment method.');
            return;
        }

        w3_opc_showLoading(widget);
        submitButton.prop('disabled', true).text(opc_ajax_object.processing_text || 'Processing...');

        var formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: opc_ajax_object.ajax_url,
            data: formData + '&action=opc_process_checkout&nonce=' + opc_ajax_object.nonce + '&product_id=' + productId + '&quantity=' + quantity,
            success: function(response) {
                if (response.result == 'success') {
                    if (response.redirect) {
                        // console.log(response);
                        window.location.href = response.redirect;
                    } else {
                        alert('Order processed successfully!');
                    }
                } else {
                    console.log(response);
                    // alert(response.message || 'An error occurred while processing your order.');
                    submitButton.prop('disabled', false).text(opc_ajax_object.place_order_text || 'Place Order');
                }
            },
            error: function() {
                alert(opc_ajax_object.error_text || 'An error occurred. Please try again.');
                submitButton.prop('disabled', false).text(opc_ajax_object.place_order_text || 'Place Order');
            },
            complete: function() {
                w3_opc_hideLoading(widget);
            }
        });
    });

    // Show/hide shipping address fields
    $(document).on('change', '#ship-to-different-address-checkbox', function() {
        var shippingAddress = $('.shipping_address');
        if ($(this).is(':checked')) {
            shippingAddress.slideDown();
        } else {
            shippingAddress.slideUp();
        }
    });
});

