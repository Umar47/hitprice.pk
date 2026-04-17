/**
 * Checkout — section headings, email toggle, UX enhancements.
 *
 * Section headings are injected via JS to avoid DOM nesting issues
 * with WooCommerce's field-wrapper markup.
 *
 * @package HitPrice
 */
(function () {
	'use strict';

	function init() {
		injectSectionHeadings();
		initEmailToggle();
	}

	/**
	 * Inject "Contact" and "Delivery Address" section headings
	 * before the relevant field groups inside the billing wrapper.
	 */
	function injectSectionHeadings() {
		// "Contact" heading before the full name field.
		var nameField = document.getElementById('billing_full_name_field');
		if (nameField) {
			var contactHeading = document.createElement('h3');
			contactHeading.className = 'hp-co-heading';
			contactHeading.textContent = 'Contact';
			nameField.parentNode.insertBefore(contactHeading, nameField);
		}

		// "Delivery Address" heading before the address field.
		var addressField = document.getElementById('billing_address_1_field');
		if (addressField) {
			var addressHeading = document.createElement('h3');
			addressHeading.className = 'hp-co-heading';
			addressHeading.textContent = 'Delivery Address';
			addressField.parentNode.insertBefore(addressHeading, addressField);
		}

		// Hide the default WooCommerce "Billing details" heading.
		var billingSection = document.querySelector('.woocommerce-billing-fields');
		if (billingSection) {
			var defaultHeading = billingSection.querySelector(':scope > h3');
			if (defaultHeading) {
				defaultHeading.style.display = 'none';
			}
		}
	}

	/**
	 * Email toggle — hides email field by default, shows on click.
	 *
	 * Inserts an "Add email (optional)" link after the phone field.
	 * Clicking reveals the email field; clicking "Hide" collapses it.
	 */
	function initEmailToggle() {
		var emailWrap = document.querySelector('.hp-co-email-wrap');
		if (!emailWrap) return;

		// Hide the email field row initially.
		emailWrap.style.display = 'none';

		// Find the phone field to place the toggle after it.
		var phoneWrap = document.getElementById('billing_phone_field');
		if (!phoneWrap) return;

		// Create toggle link.
		var toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'hp-co-email-toggle';
		toggle.textContent = '+ Add email (optional)';
		toggle.setAttribute('aria-expanded', 'false');

		var isVisible = false;

		toggle.addEventListener('click', function () {
			isVisible = !isVisible;
			emailWrap.style.display = isVisible ? '' : 'none';
			toggle.textContent = isVisible
				? '\u2212 Hide email'
				: '+ Add email (optional)';
			toggle.setAttribute('aria-expanded', String(isVisible));

			if (isVisible) {
				var input = emailWrap.querySelector('input');
				if (input) input.focus();
			}
		});

		// Insert toggle after phone field.
		phoneWrap.parentNode.insertBefore(toggle, phoneWrap.nextSibling);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
