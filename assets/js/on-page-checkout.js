jQuery(document).ready(function($) {
    'use strict';

    var w3opcfe_ajax_object = window.w3opcfe_ajax_object || {};

    // Function to show loading overlay
    function w3opcfe_showLoading(widget) {
        widget.addClass('opc-loading');
        widget.append('<div class="opc-loading-overlay"><div class="opc-loading-spinner"></div></div>');
    }

    // Function to hide loading overlay
    function w3opcfe_hideLoading(widget) {
        widget.removeClass('opc-loading');
        widget.find('.opc-loading-overlay').remove();
    }

    // Function to get selected product ID
    function w3opcfe_getSelectedProductId(form) {
        var productId = form.find('input[name="w3opcfe_product_id"]:checked').val();
        if (!productId) {
            productId = form.find('input[name="w3opcfe_product_id"]').val(); // For hidden input (single product)
        }
        return productId;
    }

    // Function to load checkout fields via AJAX
    function w3opcfe_loadCheckoutFields(widget) {
        if (!w3opcfe_ajax_object.ajax_url) {
            console.error('w3opcfe_ajax_object.ajax_url is missing. Check wp_localize_script and caching/minify.');
            return;
        }

        var form = widget.find('.opc-checkout-form');
        var productId = w3opcfe_getSelectedProductId(form);
        var quantity = form.find('input[name="w3opcfe_quantity"]').val() || 1;
        var variation_id = form.find('select[name="variation_id"]').val() || 0;
        var checkoutFieldsContainer = widget.find('[id^="opc-checkout-fields-container-"]');

        var widgetparent = widget.closest('.on-page-checkout-widget');
        var widgetSettings = {}
        widgetSettings['shippingTitle'] = widgetparent.attr('data-shipping-title');
        widgetSettings['billingTitle'] = widgetparent.attr('data-billing-title');
        widgetSettings['additionalInfoTitle'] = widgetparent.attr('data-additional-info-title');
        widgetSettings['orderSummaryTitle'] = widgetparent.attr('data-order-summery-title');
        widgetSettings['paymentMethodTitle'] = widgetparent.attr('data-payment-method-title');
        widgetSettings['orderButtonName'] = widgetparent.attr('data-order-button-name');

        if (!productId) {
            checkoutFieldsContainer.html('<p class="opc-info">' + (w3opcfe_ajax_object.no_product_selected_text || 'Please select a product.') + '</p>');
            return;
        }

        var currentFormData = form.serializeArray(); // all inputs in the form

        // Convert serializeArray into object
        var formDataObj = {};
        $.each(currentFormData, function(i, field){
            formDataObj[field.name] = field.value;
        });

        // Add product_id and quantity
        formDataObj.action = 'w3opcfe_load_checkout_fields';
        formDataObj.nonce = w3opcfe_ajax_object.nonce;
        formDataObj.product_id = productId;
        formDataObj.quantity = quantity;
        formDataObj.variation_id = variation_id;
        formDataObj.widgetSettings = widgetSettings;

        w3opcfe_showLoading(widget);
        // checkoutFieldsContainer.html('<p>' + (w3opcfe_ajax_object.loading_text || 'Loading...') + '</p>');

        $.ajax({
            type: 'POST',
            url: w3opcfe_ajax_object.ajax_url,
            data: formDataObj, // send all data
            success: function(response) {
                if (response.success) {
                    checkoutFieldsContainer.html(response.data.fields);

                    // Synchronize WooCommerce cart fragments with PixelBeacon (fires deduplicated AddToCart browser pixel)
                    if (response.data.fragments) {
                        $(document.body).trigger('added_to_cart', [ response.data.fragments, response.data.cart_hash ]);
                    }

                    // Re-trigger WooCommerce checkout lifecycle events
                    $(document.body).trigger('init_checkout');
                    $(document.body).trigger('country_to_state_changed');
                    $(document.body).trigger('payment_method_selected');
                    $(document.body).trigger('updated_checkout', [ response.data ]);
                    $(document.body).trigger('opc_checkout_fields_loaded', [ widget, response.data ]);

                    // Deduplicated InitiateCheckout with Facebook Pixel, PixelBeacon CAPI, and GA4
                    var eventId = response.data.event_id || (
                        (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.guidv4 === 'function')
                            ? window.PixelBeaconTracker.guidv4()
                            : (function () {
                                if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
                                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                                    var r = (Math.random() * 16) | 0;
                                    var v = c === 'x' ? r : (r & 0x3) | 0x8;
                                    return v.toString(16);
                                });
                            })()
                    );

                    var trackingPayload = response.data.tracking || {
                        content_ids: [ String(productId) ],
                        content_type: 'product',
                        num_items: parseInt(quantity, 10) || 1,
                        value: response.data && response.data.value ? response.data.value : undefined,
                        currency: response.data && response.data.currency ? response.data.currency : (w3opcfe_ajax_object.currency || 'USD')
                    };

                    widget.data('opc_tracking', trackingPayload);

                    // 1. Browser Meta Pixel
                    if (typeof window.fbq === 'function') {
                        var trackOpts = { eventID: eventId };
                        var pbOpts = window.pixelBeaconOptions || window.w3PixelOptions;
                        var testCode = (pbOpts && pbOpts.testEventCode) || w3opcfe_ajax_object.test_event_code;
                        if (testCode) {
                            trackOpts.test_event_code = testCode;
                        }
                        window.fbq('track', 'InitiateCheckout', trackingPayload, trackOpts);
                    }

                    // 2. Server-Side CAPI via PixelBeacon
                    if (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.sendServerEvent === 'function') {
                        window.PixelBeaconTracker.sendServerEvent('InitiateCheckout', eventId, trackingPayload);
                    } else if (w3opcfe_ajax_object.rest_url && navigator.sendBeacon) {
                        try {
                            var fd = new FormData();
                            fd.append('event_name', 'InitiateCheckout');
                            fd.append('event_id', eventId);
                            fd.append('event_source_url', window.location.href);
                            fd.append('referrer_url', document.referrer || '');
                            fd.append('custom_data', JSON.stringify(trackingPayload));
                            navigator.sendBeacon(w3opcfe_ajax_object.rest_url, fd);
                        } catch (e) {}
                    }

                    // 3. Google Analytics 4 / GTM dataLayer
                    if (window.dataLayer && Array.isArray(window.dataLayer)) {
                        window.dataLayer.push({
                            event: 'begin_checkout',
                            ecommerce: {
                                currency: trackingPayload.currency || (w3opcfe_ajax_object.currency || 'USD'),
                                value: trackingPayload.value || 0,
                                items: (trackingPayload.contents && trackingPayload.contents.length) ? trackingPayload.contents.map(function(item) {
                                    return {
                                        item_id: String(item.id),
                                        quantity: parseInt(item.quantity, 10) || 1,
                                        price: parseFloat(item.item_price) || 0
                                    };
                                }) : [{
                                    item_id: String(productId),
                                    item_variant: String(variation_id || ''),
                                    quantity: parseInt(quantity, 10) || 1
                                }]
                            }
                        });
                    }
                } else {
                    checkoutFieldsContainer.html('<p class="opc-error">' + (response.data.message || 'Error loading checkout fields.') + '</p>');
                }
            },

            error: function() {
                checkoutFieldsContainer.html('<p class="opc-error">' + (w3opcfe_ajax_object.error_text || 'An error occurred. Please try again.') + '</p>');
            },
            complete: function() {
                w3opcfe_hideLoading(widget);
            }
        });
    }

    // Initial load of checkout fields for each widget on the page
    $('.on-page-checkout-widget').each(function() {
        w3opcfe_loadCheckoutFields($(this));
    });

    // Event handler for product selection change
    $(document).on('change', '.opc-product-selection input[name="w3opcfe_product_id"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Event handler for variation selection with CustomizeProduct tracking
    $(document).on('change', '.opc-products-wrapper select[name="variation_id"]', function() {
        var variationId = $(this).val();
        var widget = $(this).closest('.on-page-checkout-widget');
        var $item = $(this).closest('.opc-product-item');

        if (variationId && variationId !== '0') {
            var eventId = (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.guidv4 === 'function')
                ? window.PixelBeaconTracker.guidv4()
                : (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : 'pb-' + Date.now());

            var availableVariations = $item.data('available-variations') || [];
            if (typeof availableVariations === 'string') {
                try {
                    availableVariations = JSON.parse(availableVariations);
                } catch(e) {
                    availableVariations = [];
                }
            }

            var matchedVar = null;
            if (Array.isArray(availableVariations)) {
                for (var v = 0; v < availableVariations.length; v++) {
                    if (String(availableVariations[v].variation_id) === String(variationId)) {
                        matchedVar = availableVariations[v];
                        break;
                    }
                }
            }

            var varPrice = matchedVar ? (parseFloat(matchedVar.display_price) || 0) : 0;
            var selectedText = $(this).find('option:selected').text();
            var pbOpts = window.pixelBeaconOptions || window.w3PixelOptions;
            var currency = (pbOpts && pbOpts.currency) || w3opcfe_ajax_object.currency || 'USD';

            var customizeParams = {
                content_name: selectedText,
                content_ids: [ String(variationId) ],
                content_type: 'product',
                value: varPrice,
                currency: currency
            };

            if (matchedVar && matchedVar.sku) {
                customizeParams.content_sku = matchedVar.sku;
            }

            if (typeof window.fbq === 'function') {
                var trackOpts = { eventID: eventId };
                var testCode = (pbOpts && pbOpts.testEventCode) || w3opcfe_ajax_object.test_event_code;
                if (testCode) {
                    trackOpts.test_event_code = testCode;
                }
                window.fbq('track', 'CustomizeProduct', customizeParams, trackOpts);
            }

            if (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.sendServerEvent === 'function') {
                window.PixelBeaconTracker.sendServerEvent('CustomizeProduct', eventId, customizeParams);
            } else if (w3opcfe_ajax_object.rest_url && navigator.sendBeacon) {
                try {
                    var cfd = new FormData();
                    cfd.append('event_name', 'CustomizeProduct');
                    cfd.append('event_id', eventId);
                    cfd.append('event_source_url', window.location.href);
                    cfd.append('referrer_url', document.referrer || '');
                    cfd.append('custom_data', JSON.stringify(customizeParams));
                    navigator.sendBeacon(w3opcfe_ajax_object.rest_url, cfd);
                } catch(e) {}
            }
        }

        w3opcfe_loadCheckoutFields(widget);
    });

    // Unbind PixelBeacon's basic payment listener to avoid duplicate AddPaymentInfo dispatch
    if (window.PixelBeaconTracker) {
        $(document.body).off('change', 'input[name="payment_method"]');
    }

    // Event handler for payment method selection with enriched AddPaymentInfo tracking
    $(document).on('change', 'input[name="payment_method"]', function () {
        var selectedMethod = $(this).val();
        if (!selectedMethod) return;

        var widget = $(this).closest('.on-page-checkout-widget');
        widget.find('.payment_box').hide();
        widget.find('.payment_box.payment_method_' + selectedMethod).slideDown(200);
        $(document.body).trigger('payment_method_selected');

        var eventId = (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.guidv4 === 'function')
            ? window.PixelBeaconTracker.guidv4()
            : (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : 'pb-' + Date.now());

        var lastTracking = widget.data('opc_tracking') || {};
        var pbOpts = window.pixelBeaconOptions || window.w3PixelOptions;
        var currency = lastTracking.currency || (pbOpts && pbOpts.currency) || w3opcfe_ajax_object.currency || 'USD';

        var paymentParams = {
            payment_method: selectedMethod,
            content_ids: lastTracking.content_ids || [ String(widget.find('input[name="w3opcfe_product_id"]:checked').val() || '') ],
            content_type: 'product',
            contents: lastTracking.contents || [],
            num_items: lastTracking.num_items || 1,
            value: lastTracking.value || 0,
            currency: currency
        };

        if (typeof window.fbq === 'function') {
            var trackOpts = { eventID: eventId };
            var testCode = (pbOpts && pbOpts.testEventCode) || w3opcfe_ajax_object.test_event_code;
            if (testCode) {
                trackOpts.test_event_code = testCode;
            }
            window.fbq('track', 'AddPaymentInfo', paymentParams, trackOpts);
        }

        if (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.sendServerEvent === 'function') {
            window.PixelBeaconTracker.sendServerEvent('AddPaymentInfo', eventId, paymentParams);
        } else if (w3opcfe_ajax_object.rest_url && navigator.sendBeacon) {
            try {
                var pfd = new FormData();
                pfd.append('event_name', 'AddPaymentInfo');
                pfd.append('event_id', eventId);
                pfd.append('event_source_url', window.location.href);
                pfd.append('referrer_url', document.referrer || '');
                pfd.append('custom_data', JSON.stringify(paymentParams));
                navigator.sendBeacon(w3opcfe_ajax_object.rest_url, pfd);
            } catch (e) {}
        }
    });

    // Event handler for state and country change
    $(document).on('change', '.opc-checkout-form #billing_state, .opc-checkout-form #billing_country, .opc-checkout-form #shipping_state, .opc-checkout-form #shipping_country', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Event handler for shipping change
    $(document).on('change', '#shipping_method input[type="radio"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Event handler for quantity change
    $(document).on('change', '.opc-quantity-selector input[name="w3opcfe_quantity"]', function() {
        var widget = $(this).closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Prevent clicking variation dropdown from toggling the outer product label
    $(document).on('click mousedown touchstart', '.opc-combined-variants select', function(e) {
        e.stopPropagation();
    });

    // Event handler for form submission
    $(document).on('submit', '.opc-checkout-form', function(e) {
        e.preventDefault();

        var form = $(this);
        var widget = form.closest('.on-page-checkout-widget');
        var submitButton = form.find('.opc-place-order-button');
        var productId = w3opcfe_getSelectedProductId(form);
        var quantity = form.find('input[name="w3opcfe_quantity"]').val() || 1;
        var noticesContainer = widget.find('.opc-checkout-notices');

        // Clear previous notices
        if (noticesContainer.length) {
            noticesContainer.empty().hide();
        }

        if (!productId) {
            var noProductMsg = w3opcfe_ajax_object.no_product_selected_text || 'Please select a product.';
            if (noticesContainer.length) {
                noticesContainer.html('<div class="woocommerce-error">' + noProductMsg + '</div>').slideDown();
            } else {
                alert(noProductMsg);
            }
            return;
        }

        // Check if payment method is selected
        var paymentMethod = form.find('input[name="payment_method"]:checked').val();
        if (!paymentMethod) {
            var noPaymentMsg = 'Please select a payment method.';
            if (noticesContainer.length) {
                noticesContainer.html('<div class="woocommerce-error">' + noPaymentMsg + '</div>').slideDown();
            } else {
                alert(noPaymentMsg);
            }
            return;
        }

        // Allow payment gateways to perform custom client-side validation / tokenization (e.g. Stripe Elements, bKash, PayPal)
        if (form.triggerHandler('checkout_place_order') === false || (paymentMethod && form.triggerHandler('checkout_place_order_' + paymentMethod) === false)) {
            return;
        }

        w3opcfe_showLoading(widget);
        submitButton.prop('disabled', true).text(w3opcfe_ajax_object.processing_text || 'Processing...');

        var formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: w3opcfe_ajax_object.ajax_url,
            data: formData + '&action=w3opcfe_process_checkout&nonce=' + encodeURIComponent(w3opcfe_ajax_object.nonce) + '&product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity),
            dataType: 'json',
            success: function(response) {
                var isSuccess = false;
                var redirectUrl = '';

                if (response && (response.result === 'success' || response.success === true)) {
                    isSuccess = true;
                    redirectUrl = response.redirect || (response.data && response.data.redirect) || '';
                }

                if (isSuccess && redirectUrl) {
                    $(document.body).trigger('checkout_place_order_success', [ response, form ]);
                    $(document.body).trigger('opc_order_completed', [ response, form ]);
                    window.location.href = redirectUrl;
                    return;
                }

                // Checkout failed or returned validation errors
                var errorHtml = '';
                if (response && response.messages) {
                    errorHtml = response.messages;
                } else if (response && response.data && response.data.message) {
                    errorHtml = '<div class="woocommerce-error">' + response.data.message + '</div>';
                } else if (response && response.message) {
                    errorHtml = '<div class="woocommerce-error">' + response.message + '</div>';
                } else {
                    errorHtml = '<div class="woocommerce-error">' + (w3opcfe_ajax_object.error_text || 'An error occurred. Please try again.') + '</div>';
                }

                if (noticesContainer.length) {
                    noticesContainer.html(errorHtml).slideDown();
                    $('html, body').animate({
                        scrollTop: noticesContainer.offset().top - 80
                    }, 400);
                } else {
                    alert($('<div>').html(errorHtml).text() || 'An error occurred while processing your order.');
                }

                submitButton.prop('disabled', false).text(w3opcfe_ajax_object.place_order_text || 'Place Order');
            },
            error: function(xhr) {
                var errorMsg = w3opcfe_ajax_object.error_text || 'An error occurred. Please try again.';
                if (xhr && xhr.responseJSON) {
                    var r = xhr.responseJSON;
                    if (r.messages) {
                        errorMsg = r.messages;
                    } else if (r.data && r.data.message) {
                        errorMsg = '<div class="woocommerce-error">' + r.data.message + '</div>';
                    } else if (r.message) {
                        errorMsg = '<div class="woocommerce-error">' + r.message + '</div>';
                    }
                }

                if (noticesContainer.length) {
                    noticesContainer.html(errorMsg.indexOf('<') !== -1 ? errorMsg : '<div class="woocommerce-error">' + errorMsg + '</div>').slideDown();
                    $('html, body').animate({
                        scrollTop: noticesContainer.offset().top - 80
                    }, 400);
                } else {
                    alert($('<div>').html(errorMsg).text() || errorMsg);
                }

                submitButton.prop('disabled', false).text(w3opcfe_ajax_object.place_order_text || 'Place Order');
            },
            complete: function() {
                w3opcfe_hideLoading(widget);
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
    
    // Quantity Selector
    $(document).on('click', '.on-page-checkout-widget .qty-increment', function(e) {
        e.preventDefault();
        var input = $(this).siblings('#w3opcfe_quantity');
        var value = parseInt(input.val(), 10) || 1;
        input.val(value + 1).trigger('change');
    });

    $(document).on('click', '.on-page-checkout-widget .qty-decrement', function(e) {
        e.preventDefault();
        var input = $(this).siblings('#w3opcfe_quantity');
        var value = parseInt(input.val(), 10) || 1;
        var min = parseInt(input.attr('min'), 10) || 1;
        if (value > min) {
            input.val(value - 1).trigger('change');
        }
    });

});
