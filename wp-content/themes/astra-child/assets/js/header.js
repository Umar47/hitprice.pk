document.addEventListener('DOMContentLoaded', function () {
	var toggle = document.querySelector('.hitprice-mobile-toggle');
	var panel = document.getElementById('hitprice-mobile-panel');

	if (!toggle || !panel) {
		return;
	}

	toggle.addEventListener('click', function () {
		var isOpen = toggle.getAttribute('aria-expanded') === 'true';

		toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
		panel.hidden = isOpen;
	});
});
