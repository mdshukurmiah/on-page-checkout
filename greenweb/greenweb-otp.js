/**
 * Greenweb SMS WooCommerce OTP Bridge for On-Page Checkout
 *
 * Provides seamless integration between On-Page Checkout's AJAX form
 * and Greenweb SMS Pro's OTP verification system, transforming it into a modal.
 */
jQuery(document).ready(function ($) {
    'use strict';

    /**
     * Helper to get active checkout phone number
     */
    function getCheckoutPhone(form) {
        var phone = form.find('input[name="billing_phone"]').val() || '';
        phone = phone.replace(/[^0-9+]/g, '');
        if (phone.startsWith('+88')) phone = phone.slice(3);
        if (phone.startsWith('88')) phone = phone.slice(2);
        if (phone.startsWith('1')) phone = '0' + phone;
        return phone;
    }

    /**
     * Synchronize modal backdrop and body state with OTP container visibility
     */
    function syncModalState() {
        var container = $('#gw-otp-inline');
        var backdrop = $('#gw-otp-modal-backdrop');

        if (!backdrop.length) {
            backdrop = $('<div id="gw-otp-modal-backdrop" class="gw-otp-modal-backdrop"></div>').appendTo('body');
            backdrop.on('click', function () {
                var cancelBtn = $('#gw-otp-cancel-link');
                if (cancelBtn.length) {
                    cancelBtn.trigger('click');
                } else {
                    $('#gw-otp-inline').hide();
                    syncModalState();
                    $('#place_order').prop('disabled', false);
                }
            });
        }

        if (container.length && container.is(':visible')) {
            backdrop.addClass('is-active');
            container.addClass('gw-modal-visible');
            $('body').addClass('gw-otp-modal-open');
        } else {
            backdrop.removeClass('is-active');
            container.removeClass('gw-modal-visible');
            $('body').removeClass('gw-otp-modal-open');
        }
    }

    /**
     * Escape key dismisses OTP modal
     */
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            var container = $('#gw-otp-inline');
            if (container.length && container.is(':visible')) {
                var cancelBtn = $('#gw-otp-cancel-link');
                if (cancelBtn.length) {
                    cancelBtn.trigger('click');
                } else {
                    container.hide();
                    syncModalState();
                    $('#place_order').prop('disabled', false);
                }
            }
        }
    });

    /**
     * Keep #gw-otp-number-input synchronized with billing_phone
     */
    $(document).on('input change', '.opc-checkout-form input[name="billing_phone"]', function () {
        var form = $(this).closest('.opc-checkout-form');
        var normalized = getCheckoutPhone(form);
        var otpPhoneInput = form.find('#gw-otp-number-input');

        if (otpPhoneInput.length && normalized) {
            otpPhoneInput.val(normalized);
        }

        // If phone changed after being marked verified, reset verification flag
        var verifiedInput = form.find('input[name="wps_user_verified"]');
        var verifiedPhone = form.find('input[name="wps_user_mobile_number"]').val();
        if (verifiedInput.length && verifiedInput.val() === '1' && verifiedPhone && verifiedPhone !== normalized) {
            verifiedInput.val('0');
        }
    });

    /**
     * Intercept checkout_place_order trigger from on-page-checkout.js
     * Returning false halts on-page-checkout's AJAX submission.
     */
    $(document).on('checkout_place_order', '.opc-checkout-form', function (e) {
        var form = $(this);
        var verifiedInput = form.find('input[name="wps_user_verified"]');

        // If Greenweb OTP fields are not present in this form, allow standard checkout
        if (!verifiedInput.length) {
            return true;
        }

        var phone = getCheckoutPhone(form);
        var verifiedPhone = form.find('input[name="wps_user_mobile_number"]').val();
        var isVerified = (verifiedInput.val() === '1');

        // Check if filter requires OTP (e.g. COD only)
        if (typeof wp_sms_woocommerce_otp !== 'undefined' && wp_sms_woocommerce_otp.checkoutotp_filter === 'gweb_otp_cod') {
            var paymentMethod = form.find('input[name="payment_method"]:checked').val() || form.find('input[name="payment_method"]').val();
            if (paymentMethod && paymentMethod !== 'cod') {
                return true; // Non-COD payment does not require OTP
            }
        }

        // If already verified for this exact phone number, allow checkout to proceed
        if (isVerified && verifiedPhone && verifiedPhone === phone) {
            return true;
        }

        // If phone is missing, let standard WooCommerce validation display error
        if (!phone) {
            return true;
        }

        // Unverified: Ensure OTP modal is shown and stop order placement
        var otpContainer = form.find('#gw-otp-inline');
        if (otpContainer.length) {
            var otpPhoneInput = form.find('#gw-otp-number-input');
            if (otpPhoneInput.length && (!otpPhoneInput.val() || otpPhoneInput.val() !== phone)) {
                otpPhoneInput.val(phone);
            }

            if (!otpContainer.is(':visible')) {
                // Show OTP modal
                otpContainer.fadeIn(200, function () {
                    syncModalState();

                    // Auto-click send if valid Bangladeshi mobile
                    if (phone.length === 11 && phone.startsWith('01')) {
                        form.find('#gw-otp-send-btn').click();
                    }
                });
            } else {
                syncModalState();

                // If modal is already visible, focus the relevant input
                var stepCode = form.find('#gw-otp-step-code');
                if (stepCode.length && stepCode.is(':visible')) {
                    var emptyDigit = stepCode.find('.gw-otp-digit').filter(function () {
                        return !$(this).val();
                    }).first();

                    if (emptyDigit.length) {
                        emptyDigit.focus();
                    } else {
                        stepCode.find('.gw-otp-digit:first').focus();
                    }
                } else {
                    form.find('#gw-otp-send-btn').click();
                }
            }

            return false; // Halt on-page checkout AJAX
        }

        return true;
    });

    /**
     * Observers to monitor modal visibility and digit auto-focus
     */
    var modalContainerObserver = new MutationObserver(function () {
        syncModalState();
    });

    var digitFocusObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                var stepCode = $('#gw-otp-step-code');
                if (stepCode.length && stepCode.is(':visible')) {
                    setTimeout(function () {
                        stepCode.find('.gw-otp-digit:first').focus();
                    }, 100);
                }
            }
        });
    });

    function attachObservers() {
        var container = document.getElementById('gw-otp-inline');
        if (container) {
            modalContainerObserver.disconnect();
            modalContainerObserver.observe(container, { attributes: true, attributeFilter: ['style', 'class'] });
            syncModalState();
        }

        var stepCode = document.getElementById('gw-otp-step-code');
        if (stepCode) {
            digitFocusObserver.disconnect();
            digitFocusObserver.observe(stepCode, { attributes: true, attributeFilter: ['style'] });
        }
    }

    attachObservers();

    // Re-bind observers when checkout fields are reloaded via AJAX (On-Page Checkout or standard WooCommerce checkout)
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings && settings.data && typeof settings.data === 'string' && settings.data.indexOf('w3opcfe_load_checkout_fields') !== -1) {
            setTimeout(function () {
                attachObservers();
            }, 50);
        }
    });

    $(document.body).on('updated_checkout', function () {
        setTimeout(function () {
            attachObservers();
        }, 50);
    });

    // Also observe cancel button click to ensure backdrop sync
    $(document).on('click', '#gw-otp-cancel-link', function () {
        setTimeout(function () {
            syncModalState();
        }, 50);
    });
});
