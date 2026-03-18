document.addEventListener('DOMContentLoaded', function () {
	var toggle = document.querySelector('.hitprice-mobile-toggle');
	var panel = document.getElementById('hitprice-mobile-panel');
	var cartTriggers = document.querySelectorAll('.hitprice-header-cart');
	var cartDrawer = document.getElementById('astra-mobile-cart-drawer') || document.querySelector('.astra-cart-drawer');
	var cartClose = document.querySelector('.astra-cart-drawer-close');

	if (toggle && panel) {
		toggle.addEventListener('click', function () {
			var isOpen = toggle.getAttribute('aria-expanded') === 'true';

			toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
			panel.hidden = isOpen;
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth >= 992) {
				toggle.setAttribute('aria-expanded', 'false');
				panel.hidden = true;
			}
		});
	}

	if (cartTriggers.length && cartDrawer) {
		cartTriggers.forEach(function (trigger) {
			trigger.addEventListener('click', function (event) {
				var cartLink = trigger.querySelector('a.cart-container');

				if (!cartLink) {
					return;
				}

				event.preventDefault();
				cartDrawer.classList.add('active');
				document.documentElement.classList.add('ast-mobile-cart-active');
			});
		});
	}

	if (cartClose && cartDrawer) {
		cartClose.addEventListener('click', function () {
			cartDrawer.classList.remove('active');
			document.documentElement.classList.remove('ast-mobile-cart-active');
		});
	}
});
