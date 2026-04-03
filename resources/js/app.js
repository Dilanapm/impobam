import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function scrollToFirstValidationError() {
	const errorElements = Array.from(document.querySelectorAll('form [data-validation-error]'));
	const firstVisibleError = errorElements.find((element) => element.getClientRects().length > 0);
	const firstError = firstVisibleError ?? errorElements[0];
	if (!firstError) {
		return;
	}

	firstError.scrollIntoView({ block: 'center' });

	const relatedField = firstError.parentElement?.querySelector('input, select, textarea');
	if (relatedField && typeof relatedField.focus === 'function') {
		relatedField.focus({ preventScroll: true });
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', scrollToFirstValidationError);
} else {
	scrollToFirstValidationError();
}
