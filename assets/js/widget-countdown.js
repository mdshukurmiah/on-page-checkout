/**
 * Scripts for Product Countdown Widget
 */
(function($) {
	'use strict';

	function formatDateTime(date) {
		const yyyy = date.getFullYear();
		const mm = String(date.getMonth() + 1).padStart(2, '0');
		const dd = String(date.getDate()).padStart(2, '0');
		const hh = String(date.getHours()).padStart(2, '0');
		const min = String(date.getMinutes()).padStart(2, '0');
		return `${yyyy}-${mm}-${dd} ${hh}:${min}`;
	}

	function parseDate(dateString) {
		const formatted = dateString.replace(/-/g, '/');
		return new Date(formatted);
	}

	function convertToBangla(str) {
		const banglaDigits = {
			'0': '০', '1': '১', '2': '২', '3': '৩', '4': '৪',
			'5': '৫', '6': '৬', '7': '৭', '8': '৮', '9': '৯'
		};
		return String(str).split('').map(function(char) {
			return banglaDigits[char] || char;
		}).join('');
	}

	const W3opcfeCountdownHandler = function($element) {
		const $wrapper = $element.find('.w3opcfe-countdown-wrapper, .singlegrid-pdp-countdown-wrapper');
		if (!$wrapper.length) {
			return;
		}

		const existingInterval = $wrapper.data('w3opcfe-interval-id') || $wrapper.data('singlegrid-pdp-interval-id');
		if (existingInterval) {
			clearInterval(existingInterval);
		}

		let dueDateString = $wrapper.attr('data-due-date');
		const autoReset = $wrapper.attr('data-auto-reset') === 'yes';
		const resetType = $wrapper.attr('data-reset-type') || 'interval';
		const resetInterval = parseInt($wrapper.attr('data-reset-interval'), 10) || 1;
		const resetTime = $wrapper.attr('data-reset-time') || '00:00';
		const useBangla = $wrapper.attr('data-bangla-numbers') === 'yes';

		if (!dueDateString) {
			return;
		}

		let targetDate = parseDate(dueDateString);
		const pad = function(num) {
			return String(Math.max(0, num)).padStart(2, '0');
		};

		const updateTimer = function() {
			const now = new Date().getTime();
			let distance = targetDate.getTime() - now;

			if (distance < 0) {
				if (autoReset) {
					if (resetType === 'interval') {
						while (targetDate.getTime() <= now) {
							targetDate.setDate(targetDate.getDate() + resetInterval);
						}
					} else if (resetType === 'daily') {
						const timeParts = resetTime.split(':');
						const hour = parseInt(timeParts[0], 10) || 0;
						const minute = parseInt(timeParts[1], 10) || 0;

						const nextTarget = new Date();
						nextTarget.setHours(hour, minute, 0, 0);

						if (nextTarget.getTime() <= now) {
							nextTarget.setDate(nextTarget.getDate() + 1);
						}
						targetDate = nextTarget;
					}

					$wrapper.attr('data-due-date', formatDateTime(targetDate));
					distance = targetDate.getTime() - now;
				} else {
					distance = 0;
					clearInterval(intervalId);
				}
			}

			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			let d = pad(days);
			let h = pad(hours);
			let m = pad(minutes);
			let s = pad(seconds);

			if (useBangla) {
				d = convertToBangla(d);
				h = convertToBangla(h);
				m = convertToBangla(m);
				s = convertToBangla(s);
			}

			$wrapper.find('.w3opcfe-days-digit, .singlegrid-pdp-days-digit').text(d);
			$wrapper.find('.w3opcfe-hours-digit, .singlegrid-pdp-hours-digit').text(h);
			$wrapper.find('.w3opcfe-minutes-digit, .singlegrid-pdp-minutes-digit').text(m);
			$wrapper.find('.w3opcfe-seconds-digit, .singlegrid-pdp-seconds-digit').text(s);
		};

		updateTimer();
		const intervalId = setInterval(updateTimer, 1000);
		$wrapper.data('w3opcfe-interval-id', intervalId);
	};

	$(window).on('elementor/frontend/init', function() {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction('frontend/element_ready/w3opcfe-countdown.default', W3opcfeCountdownHandler);
			elementorFrontend.hooks.addAction('frontend/element_ready/singlegrid-pdp-product-countdown.default', W3opcfeCountdownHandler);
		}
	});

	// Frontend ready fallback
	$(document).ready(function() {
		$('.w3opcfe-countdown-wrapper, .singlegrid-pdp-countdown-wrapper').each(function() {
			var $wrap = $(this).closest('.elementor-widget');
			if ($wrap.length) {
				W3opcfeCountdownHandler($wrap);
			} else {
				W3opcfeCountdownHandler($(this).parent());
			}
		});
	});

})(jQuery);
