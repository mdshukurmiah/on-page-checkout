/**
 * Scripts for Product Scarcity Widget
 */
(function($) {
	'use strict';

	function convertToBangla(str) {
		const banglaDigits = {
			'0': '০', '1': '১', '2': '২', '3': '৩', '4': '৪',
			'5': '৫', '6': '৬', '7': '৭', '8': '৮', '9': '৯'
		};
		return String(str).split('').map(function(char) {
			return banglaDigits[char] || char;
		}).join('');
	}

	const W3opcfeScarcityHandler = function($element) {
		const $wrapper = $element.find('.w3opcfe-scarcity-wrapper, .singlegrid-pdp-scarcity-wrapper');
		if (!$wrapper.length) {
			return;
		}

		const minVal        = parseInt($wrapper.attr('data-min'), 10) || 0;
		const maxVal        = parseInt($wrapper.attr('data-max'), 10) || 10;
		const decrementMode = $wrapper.attr('data-decrement-mode') || 'random';
		const decrementStep = parseInt($wrapper.attr('data-decrement-step'), 10) || 1;
		const resetAtMin    = $wrapper.attr('data-reset-at-min') === 'yes';
		const useBangla     = $wrapper.attr('data-bangla-numbers') === 'yes';
		const countUp       = $wrapper.attr('data-count-up') === 'yes';
		const widgetId      = $wrapper.attr('data-widget-id') || 'preview';

		const storageKey = 'w3opcfe_scarcity_' + widgetId;
		const isEditMode = (typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode());

		let count;

		if (isEditMode) {
			count = countUp ? minVal : maxVal;
		} else {
			const storedValue = localStorage.getItem(storageKey);

			if (storedValue === null) {
				count = countUp ? minVal : maxVal;
			} else {
				count = parseInt(storedValue, 10) || maxVal;

				let step = 1;
				if (decrementMode === 'random') {
					step = Math.floor(Math.random() * 3) + 1;
				} else {
					step = decrementStep;
				}

				if (countUp) {
					count = count + step;
					if (count > maxVal) {
						count = resetAtMin ? minVal : maxVal;
					}
				} else {
					count = count - step;
					if (count < minVal) {
						count = resetAtMin ? maxVal : minVal;
					}
				}
			}

			localStorage.setItem(storageKey, count);
		}

		let outputCount = count;
		if (useBangla) {
			outputCount = convertToBangla(outputCount);
		}

		$wrapper.find('.w3opcfe-scarcity-number, .singlegrid-pdp-scarcity-number').text(outputCount);

		// Handle Scarcity Progress Bar
		const layout  = $wrapper.attr('data-layout') || ($wrapper.hasClass('w3opcfe-scarcity-has-bar') ? 'bar' : 'inline');
		const barMax  = parseInt($wrapper.attr('data-bar-max'), 10) || maxVal || 100;
		const barCalc = $wrapper.attr('data-bar-calc') || 'remaining';

		if (layout === 'bar' || $wrapper.find('.w3opcfe-scarcity-progress-bar, .singlegrid-pdp-scarcity-progress-bar').length) {
			let percent = 50;
			if (barCalc === 'direct_percent') {
				percent = Math.max(0, Math.min(100, count));
			} else if (barCalc === 'depleted') {
				percent = Math.max(0, Math.min(100, Math.round(((barMax - count) / barMax) * 100)));
			} else {
				percent = Math.max(0, Math.min(100, Math.round((count / barMax) * 100)));
			}

			const $bar  = $wrapper.find('.w3opcfe-scarcity-progress-bar, .singlegrid-pdp-scarcity-progress-bar');
			const $fill = $wrapper.find('.w3opcfe-scarcity-progress-fill, .singlegrid-pdp-scarcity-progress-fill');

			$bar.attr('aria-valuenow', percent);
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(function() {
					$fill.css('width', percent + '%');
				});
			} else {
				$fill.css('width', percent + '%');
			}
		}
	};

	$(window).on('elementor/frontend/init', function() {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction('frontend/element_ready/w3opcfe-scarcity.default', W3opcfeScarcityHandler);
			elementorFrontend.hooks.addAction('frontend/element_ready/singlegrid-pdp-product-scarcity.default', W3opcfeScarcityHandler);
		}
	});

	// Frontend ready fallback
	$(document).ready(function() {
		$('.w3opcfe-scarcity-wrapper, .singlegrid-pdp-scarcity-wrapper').each(function() {
			var $wrap = $(this).closest('.elementor-widget');
			if ($wrap.length) {
				W3opcfeScarcityHandler($wrap);
			} else {
				W3opcfeScarcityHandler($(this).parent());
			}
		});
	});

})(jQuery);
