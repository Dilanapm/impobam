import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function enableTapToDismissKeyboard() {
	let isEnabled = false;

	function isEditableElement(element) {
		if (!(element instanceof HTMLElement)) {
			return false;
		}

		const tag = element.tagName;
		if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
			return !(element.disabled || element.readOnly);
		}

		return element.isContentEditable;
	}

	function shouldBlurOnTarget(target) {
		if (!(target instanceof Element)) {
			return false;
		}

		return target.closest('input, textarea, select, [contenteditable="true"], [contenteditable=""], [contenteditable="plaintext-only"]') === null;
	}

	function handler(event) {
		const activeElement = document.activeElement;
		if (!isEditableElement(activeElement)) {
			return;
		}

		if (!shouldBlurOnTarget(event.target)) {
			return;
		}

		if (typeof activeElement.blur === 'function') {
			activeElement.blur();
		}
	}

	function init() {
		if (isEnabled) {
			return;
		}

		isEnabled = true;
		document.addEventListener('pointerdown', handler, { passive: true });
		document.addEventListener('touchstart', handler, { passive: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}

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

enableTapToDismissKeyboard();
