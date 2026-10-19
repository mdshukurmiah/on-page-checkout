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

    // Function to get selected products array (supports single and multi-selection)
    function w3opcfe_getSelectedProducts(form) {
        var selectedProducts = [];
        var widget = form.closest('.on-page-checkout-widget');
        var isMulti = widget.attr('data-multi-select') === 'yes';

        var checkedInputs = form.find('.opc-products-wrapper input[name^="w3opcfe_product_id"]:checked');
        if (!checkedInputs.length && !isMulti) {
            var firstInput = form.find('.opc-products-wrapper input[name^="w3opcfe_product_id"]:not(:disabled)').first();
            if (firstInput.length) {
                firstInput.prop('checked', true);
                checkedInputs = firstInput;
            }
        }

        var globalQty = parseInt(form.find('input[name="w3opcfe_quantity"]').val(), 10) || 1;

        checkedInputs.each(function() {
            var $input = $(this);
            var $item = $input.closest('.opc-product-item');
            if ($input.is(':disabled') || $item.hasClass('opc-out-of-stock')) {
                return;
            }
            var pId = parseInt($input.val(), 10) || parseInt($item.attr('data-product-id'), 10) || 0;
            var vId = parseInt($input.attr('data-variation-id'), 10) || parseInt($item.attr('data-variation-id'), 10) || parseInt($item.find('select[name="variation_id"]').val(), 10) || 0;

            // Separate card quantity if present in multi-select mode
            var $cardQtyInput = $item.find('.opc-card-qty-input');
            var itemQty = ($cardQtyInput.length && isMulti) ? (parseInt($cardQtyInput.val(), 10) || 1) : globalQty;

            if (pId > 0) {
                selectedProducts.push({
                    product_id: pId,
                    variation_id: vId,
                    quantity: itemQty
                });
            }
        });

        return selectedProducts;
    }

    // Function to get selected product ID (backward compatibility)
    function w3opcfe_getSelectedProductId(form) {
        var products = w3opcfe_getSelectedProducts(form);
        return products.length > 0 ? products[0].product_id : '';
    }

    // Function to load checkout fields via AJAX
    function w3opcfe_loadCheckoutFields(widget) {
        if (!w3opcfe_ajax_object.ajax_url) {
            console.error('w3opcfe_ajax_object.ajax_url is missing. Check wp_localize_script and caching/minify.');
            return;
        }

        var form = widget.find('.opc-checkout-form');
        var selectedProducts = w3opcfe_getSelectedProducts(form);
        var checkoutFieldsContainer = widget.find('[id^="opc-checkout-fields-container-"]');

        if (!selectedProducts.length) {
            checkoutFieldsContainer.html('<p class="opc-info">' + (w3opcfe_ajax_object.no_product_selected_text || 'Please select at least one product.') + '</p>');
            return;
        }

        var productId = selectedProducts[0].product_id;
        var variation_id = selectedProducts[0].variation_id;
        var quantity = selectedProducts[0].quantity;
        form.find('input[name="variation_id"].opc-selected-variation-id').val(variation_id);

        var widgetparent = widget.closest('.on-page-checkout-widget');
        var widgetSettings = {}
        widgetSettings['shippingTitle'] = widgetparent.attr('data-shipping-title');
        widgetSettings['shippingMethodTitle'] = widgetparent.attr('data-shipping-method-title') || 'Shipping Method';
        widgetSettings['billingTitle'] = widgetparent.attr('data-billing-title');
        widgetSettings['additionalInfoTitle'] = widgetparent.attr('data-additional-info-title');
        widgetSettings['orderSummaryTitle'] = widgetparent.attr('data-order-summery-title');
        widgetSettings['paymentMethodTitle'] = widgetparent.attr('data-payment-method-title');
        widgetSettings['orderButtonName'] = widgetparent.attr('data-order-button-name');
        widgetSettings['mobileSummaryLabel'] = widgetparent.attr('data-mobile-summary-label') || 'Order summary';
        widgetSettings['mobileSummaryCollapsed'] = widgetparent.attr('data-mobile-summary-collapsed') || 'yes';

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
        formDataObj.selected_products = JSON.stringify(selectedProducts);
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

                    var trackingIds = selectedProducts.map(function(p) {
                        return String(p.variation_id || p.product_id);
                    });
                    var totalQuantity = selectedProducts.reduce(function(sum, p) {
                        return sum + (parseInt(p.quantity, 10) || 1);
                    }, 0);

                    var trackingPayload = response.data.tracking || {
                        content_ids: trackingIds,
                        content_type: 'product',
                        num_items: totalQuantity,
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

    // Helper function to initialize widget instance(s)
    function w3opcfe_initWidget(container) {
        var $target = $(container);
        var $widgets = $target.hasClass('on-page-checkout-widget') ? $target : $target.find('.on-page-checkout-widget');
        $widgets.each(function() {
            w3opcfe_loadCheckoutFields($(this));
        });
    }

    // Initial load of checkout fields on page ready
    w3opcfe_initWidget(document);

    // Elementor editor preview & frontend lifecycle hooks
    var w3opcfe_bindElementorHooks = function() {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction('frontend/element_ready/w3opcfe-on-page-checkout.default', function($scope) {
                w3opcfe_initWidget($scope);
            });
        }
    };

    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        w3opcfe_bindElementorHooks();
    } else {
        $(window).on('elementor/frontend/init', w3opcfe_bindElementorHooks);
    }

    // Prevent selecting out of stock cards
    $(document).on('click', '.opc-product-item.opc-out-of-stock label', function(e) {
        if ($(e.target).closest('.opc-combined-variants').length) {
            return; // Allow interacting with variation dropdown to switch to an in-stock variant
        }
        e.preventDefault();
        return false;
    });

    // Event handler for product selection change
    $(document).on('change', '.opc-products-wrapper input[name^="w3opcfe_product_id"], .opc-product-selection input[name^="w3opcfe_product_id"]', function(e) {
        if ($(this).is(':disabled') || $(this).closest('.opc-product-item').hasClass('opc-out-of-stock')) {
            e.preventDefault();
            $(this).prop('checked', false);
            return false;
        }
        var widget = $(this).closest('.on-page-checkout-widget');
        var isMulti = widget.attr('data-multi-select') === 'yes';
        if (!isMulti) {
            var variationId = $(this).attr('data-variation-id') || 0;
            widget.find('input[name="variation_id"].opc-selected-variation-id').val(variationId);
        }
        w3opcfe_loadCheckoutFields(widget);
    });

    // Event handler for variation selection with CustomizeProduct tracking and stock status check
    $(document).on('change', '.opc-products-wrapper select[name="variation_id"]', function() {
        var variationId = $(this).val();
        var widget = $(this).closest('.on-page-checkout-widget');
        var $item = $(this).closest('.opc-product-item');
        var $input = $item.find('input[name^="w3opcfe_product_id"]');

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

        // Check if selected variation is in stock
        var inStock = true;
        if (matchedVar) {
            inStock = (matchedVar.is_in_stock === true || matchedVar.is_in_stock === '1' || matchedVar.is_in_stock === 1);
        } else {
            var selectedOpt = $(this).find('option:selected');
            if (selectedOpt.length && selectedOpt.attr('data-in-stock') === '0') {
                inStock = false;
            }
        }

        var $badge = $item.find('.opc-stock-badge');
        if (!inStock) {
            $item.addClass('opc-out-of-stock');
            $input.prop('disabled', true);
            $input.prop('checked', false);
            if ($badge.length) {
                $badge.show();
            } else {
                $item.find('> label').prepend('<span class="opc-stock-badge opc-out-of-stock-badge">' + (w3opcfe_ajax_object.out_of_stock_text || 'Out of Stock') + '</span>');
            }
        } else {
            $item.removeClass('opc-out-of-stock');
            $input.prop('disabled', false);
            $badge.hide();
            // Automatically ensure the item is checked if it's a checkbox or radio in single-select mode
            var isMulti = widget.attr('data-multi-select') === 'yes';
            if (!isMulti || !widget.find('.opc-product-input:checked').length) {
                $input.prop('checked', true);
            }
        }

        if (variationId && variationId !== '0') {
            var eventId = (window.PixelBeaconTracker && typeof window.PixelBeaconTracker.guidv4 === 'function')
                ? window.PixelBeaconTracker.guidv4()
                : (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : 'pb-' + Date.now());

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

    // Prevent clicking variation dropdown or card quantity from toggling the outer product label
    $(document).on('click mousedown touchstart', '.opc-combined-variants select, .opc-card-quantity', function(e) {
        e.stopPropagation();
    });

    // Card Quantity Increment / Decrement
    $(document).on('click', '.opc-card-qty-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var $item = $btn.closest('.opc-product-item');
        var $input = $btn.siblings('.opc-card-qty-input');
        var val = parseInt($input.val(), 10) || 1;
        var min = parseInt($input.attr('min'), 10) || 1;

        if ($btn.hasClass('qty-increment')) {
            $input.val(val + 1);
        } else if ($btn.hasClass('qty-decrement')) {
            if (val > min) {
                $input.val(val - 1);
            }
        }

        // Auto-check product card checkbox if unchecked
        var $checkbox = $item.find('input[name^="w3opcfe_product_id"]');
        if ($checkbox.length && !$checkbox.is(':checked')) {
            $checkbox.prop('checked', true);
        }

        var widget = $btn.closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Card Quantity manual input change
    $(document).on('change', '.opc-card-qty-input', function(e) {
        e.stopPropagation();
        var $input = $(this);
        var $item = $input.closest('.opc-product-item');
        var val = parseInt($input.val(), 10) || 1;
        var min = parseInt($input.attr('min'), 10) || 1;
        if (val < min) {
            $input.val(min);
        }

        // Auto-check product card checkbox if unchecked
        var $checkbox = $item.find('input[name^="w3opcfe_product_id"]');
        if ($checkbox.length && !$checkbox.is(':checked')) {
            $checkbox.prop('checked', true);
        }

        var widget = $input.closest('.on-page-checkout-widget');
        w3opcfe_loadCheckoutFields(widget);
    });

    // Event handler for mobile order summary accordion toggle
    $(document).on('click', '.opc-mobile-order-summary-toggle', function(e) {
        e.preventDefault();
        var toggleBtn = $(this);
        var wrapper = toggleBtn.closest('.order-details-wrapper');
        var reviewTable = wrapper.find('.opc-order-review');
        var isExpanded = toggleBtn.attr('aria-expanded') === 'true';

        if (isExpanded) {
            toggleBtn.attr('aria-expanded', 'false');
            reviewTable.slideUp(200, function() {
                wrapper.removeClass('opc-summary-open');
                reviewTable.css('display', '');
            });
        } else {
            toggleBtn.attr('aria-expanded', 'true');
            wrapper.addClass('opc-summary-open');
            reviewTable.hide().slideDown(200);
        }
    });

    // Event handler for form submission
    $(document).on('submit', '.opc-checkout-form', function(e) {
        e.preventDefault();

        var form = $(this);
        var widget = form.closest('.on-page-checkout-widget');
        var submitButton = form.find('.opc-place-order-button');
        var selectedProducts = w3opcfe_getSelectedProducts(form);
        var noticesContainer = widget.find('.opc-checkout-notices');

        // Clear previous notices
        if (noticesContainer.length) {
            noticesContainer.empty().hide();
        }

        if (!selectedProducts.length) {
            var noProductMsg = w3opcfe_ajax_object.no_product_selected_text || 'Please select at least one product.';
            if (noticesContainer.length) {
                noticesContainer.html('<div class="woocommerce-error">' + noProductMsg + '</div>').slideDown();
            } else {
                alert(noProductMsg);
            }
            return;
        }

        var productId = selectedProducts[0].product_id;
        var variationId = selectedProducts[0].variation_id;
        var quantity = selectedProducts[0].quantity;
        form.find('input[name="variation_id"].opc-selected-variation-id').val(variationId);

        // Check if payment method is selected
        var paymentMethod = form.find('input[name="payment_method"]:checked').val() || form.find('input[name="payment_method"]').val();
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
            data: formData + '&action=w3opcfe_process_checkout&nonce=' + encodeURIComponent(w3opcfe_ajax_object.nonce) + '&product_id=' + encodeURIComponent(productId) + '&variation_id=' + encodeURIComponent(variationId) + '&quantity=' + encodeURIComponent(quantity) + '&selected_products=' + encodeURIComponent(JSON.stringify(selectedProducts)),
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
    
    // Global Quantity Selector
    $(document).on('click', '.opc-quantity-selector .qty-increment', function(e) {
        e.preventDefault();
        var input = $(this).siblings('#w3opcfe_quantity');
        var value = parseInt(input.val(), 10) || 1;
        input.val(value + 1).trigger('change');
    });

    $(document).on('click', '.opc-quantity-selector .qty-decrement', function(e) {
        e.preventDefault();
        var input = $(this).siblings('#w3opcfe_quantity');
        var value = parseInt(input.val(), 10) || 1;
        var min = parseInt(input.attr('min'), 10) || 1;
        if (value > min) {
            input.val(value - 1).trigger('change');
        }
    });

});
