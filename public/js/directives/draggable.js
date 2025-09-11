import { setTransferData, convertToValidDragObject } from '../helpers/DragAndDrop.js';

const EFFECTS = [
	'none',
	'copy',
	'copyLink',
	'copyMove',
	'link',
	'linkMove',
	'move',
	'all',
	'uninitialized'
];

export default {
	mounted(el, binding, vnode) {
		updateValue(el, binding.value);
		updateEffectAllowed(el, binding.arg);

		// if modifier capture is set we assume it's on a parent element
		// i.e: for dragging multiple elements
		// otherwise set draggable attribute
		if (!binding.modifiers.capture) {
			el.draggable = true;
		}

		const bcc = new BroadcastChannel('fhc-dnd');
		let blocked = false;

		function onStart(evt) {
			const value = el.dataset.fhcDraggableValue;
			if (value) {
				setTransferData(evt, JSON.parse(value), true);
				if (el.dataset.fhcEffectAllowed)
					evt.dataTransfer.effectAllowed = el.dataset.fhcEffectAllowed;

				bcc.onmessage = e => {
					if (e.data == 'block') {
						blocked = true;
					} else if (e.data == 'release') {
						let evt = null;
						if (blocked && blocked.evt) {
							evt = blocked.evt;
						}
						blocked = false;
						if (evt)
							el.dispatchEvent(evt);
					}
				};
			} else {
				evt.preventDefault();
			}
		}

		function onEnd(evt) {
			if (blocked) {
				blocked = {
					evt,
					dt: evt.dataTransfer
				};
				evt.stopPropagation();
				el.dispatchEvent(new DragEvent("beforedragend", evt));
			} else {
				bcc.onmessage = () => {};
			}
		}
		el.addEventListener('dragstart', onStart, binding.modifiers.capture);

		el.addEventListener('dragend', onEnd, true);

		el.fhcDraggableCleanup = () => {
			el.removeEventListener('dragstart', onStart, binding.modifiers.capture);
			el.removeEventListener('dragend', onEnd, true);
		};
	},
	updated(el, binding) {
		updateValue(el, binding.value);
		updateEffectAllowed(el, binding.arg);
	},
	beforeUnmount(el) {
		el.fhcDraggableCleanup();
		delete el.fhcDraggableCleanup;
	}
}

// Helper functions
function updateValue(el, value) {
	value = convertToValidDragObject(value);
	if (value) {
		el.dataset.fhcDraggableValue = JSON.stringify(value);
	} else if (el.dataset.fhcDraggableValue) {
		delete el.dataset.fhcDraggableValue;
	}
}
function updateEffectAllowed(el, effectAllowed) {
	if (effectAllowed && EFFECTS.includes(effectAllowed)) {
		el.dataset.fhcEffectAllowed = effectAllowed;
	} else if (el.dataset.fhcEffectAllowed) {
		delete el.dataset.fhcEffectAllowed;
	}
}
