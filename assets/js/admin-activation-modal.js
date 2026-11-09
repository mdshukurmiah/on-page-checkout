/**
 * On-Page Checkout - Activation Confirmation Modal
 *
 * Shared by the Free and Pro editions. Intercepts the "Activate" link of the other edition on the
 * Plugins screen and asks to switch, since only one edition may be active at a time.
 * Configuration comes from the localized `w3opcfeActivationModal` object.
 */
(function() {
	'use strict';

	var config = window.w3opcfeActivationModal || {};
	var targetActivateUrl = '';

	function getActivateLink(el) {
		if (!el || !config.targetPlugin) {
			return false;
		}

		var link = el.closest ? el.closest('a') : null;
		if (!link) {
			return false;
		}

		// 1. Plugins table row of the target edition
		var row = link.closest ? link.closest('tr') : null;
		if (row && row.getAttribute('data-plugin') === config.targetPlugin && link.closest('.activate')) {
			return link;
		}

		// 2. Activation URL of the target edition (e.g. links outside the table)
		var href = link.getAttribute('href') || '';
		if (href.indexOf('action=activate') !== -1 &&
			(href.indexOf('plugin=' + encodeURIComponent(config.targetPlugin)) !== -1 || href.indexOf('plugin=' + config.targetPlugin) !== -1)) {
			return link;
		}

		return false;
	}

	function getModal() {
		return document.getElementById('w3opcfe-activation-modal');
	}

	function setLoading(isLoading) {
		var modal = getModal();
		if (!modal) {
			return;
		}
		var btnContinue = modal.querySelector('.w3opcfe-modal-btn-continue');
		var btnCancel   = modal.querySelector('.w3opcfe-modal-btn-cancel');
		var btnClose    = modal.querySelector('.w3opcfe-modal-close');

		if (btnContinue) {
			btnContinue.classList.toggle('is-loading', isLoading);
			btnContinue.disabled = isLoading;
		}
		if (btnCancel) {
			btnCancel.disabled = isLoading;
		}
		if (btnClose) {
			btnClose.disabled = isLoading;
		}
	}

	function openModal() {
		var modal = getModal();
		if (modal) {
			modal.classList.add('is-active');
			document.body.classList.add('w3opcfe-modal-open');
			var cancelBtn = modal.querySelector('.w3opcfe-modal-btn-cancel');
			if (cancelBtn) {
				cancelBtn.focus();
			}
		}
	}

	function closeModal() {
		var modal = getModal();
		if (modal) {
			modal.classList.remove('is-active');
		}
		document.body.classList.remove('w3opcfe-modal-open');
		setLoading(false);
	}

	function handleContinue() {
		setLoading(true);

		var formData = new FormData();
		formData.append('action', config.ajaxAction || '');
		formData.append('nonce', config.nonce || '');

		fetch(config.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function(res) {
			return res.json();
		})
		.then(function(response) {
			if (response && response.success && response.data && response.data.redirect_url) {
				window.location.href = response.data.redirect_url;
				return;
			}
			alert((response && response.data && response.data.message) ? response.data.message : (config.i18n && config.i18n.failed) || 'Switching failed. Please reload the page.');
			closeModal();
		})
		.catch(function() {
			// Fall back to the original activation link; the plugin's activation hook still deactivates the other edition
			if (targetActivateUrl) {
				window.location.href = targetActivateUrl;
			} else {
				closeModal();
			}
		});
	}

	function bindModalEvents() {
		var modal = getModal();
		if (!modal || modal.getAttribute('data-events-bound') === 'true') {
			return;
		}
		modal.setAttribute('data-events-bound', 'true');

		modal.querySelectorAll('.w3opcfe-modal-btn-cancel, .w3opcfe-modal-close').forEach(function(btn) {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				closeModal();
			});
		});

		modal.addEventListener('click', function(e) {
			var dialog = modal.querySelector('.w3opcfe-modal-dialog');
			if (dialog && !dialog.contains(e.target)) {
				closeModal();
			}
		});

		var btnContinue = modal.querySelector('.w3opcfe-modal-btn-continue');
		if (btnContinue) {
			btnContinue.addEventListener('click', function(e) {
				e.preventDefault();
				handleContinue();
			});
		}
	}

	// Capturing listener intercepts the click before the browser follows the activation link
	document.addEventListener('click', function(e) {
		var link = getActivateLink(e.target);
		if (link && getModal()) {
			e.preventDefault();
			e.stopPropagation();
			if (e.stopImmediatePropagation) {
				e.stopImmediatePropagation();
			}
			targetActivateUrl = link.getAttribute('href') || '';
			openModal();
		}
	}, true);

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			var modal = getModal();
			if (modal && modal.classList.contains('is-active')) {
				closeModal();
			}
		}
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bindModalEvents);
	} else {
		bindModalEvents();
	}
})();
