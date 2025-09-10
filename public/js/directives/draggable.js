import { setTransferData, convertToValidDragObject, dragendWorker } from '../helpers/DragAndDrop.js';

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

		el.addEventListener('dragstart', evt => {
			const value = el.dataset.fhcDraggableValue;
			if (value) {
				setTransferData(evt, JSON.parse(value), true);
				if (el.dataset.fhcEffectAllowed)
					evt.dataTransfer.effectAllowed = el.dataset.fhcEffectAllowed;
				blockDragend();
			} else {
				evt.preventDefault();
			}
		}, binding.modifiers.capture);

		let id;
		let evt = null;
		let dataTransfer = null;
		function blockDragend() {
			id = el.dataset.fhcDraggableValue;
			dragendWorker.port.postMessage(['init', id]);
			window.addEventListener('dragend', blockHandler, true);
		}
		function unblockDragend(e) {
			if (e) {
				evt = e;
				dataTransfer = e.dataTransfer;
			}
			window.removeEventListener('dragend', blockHandler, true);
		}

		function blockHandler(evt) {
			if (evt.dataTransfer.dropEffect == 'none')
				return unblockDragend();
			unblockDragend(evt);
			evt.stopPropagation();
			dragendWorker.port.postMessage(['request']);
		}

		dragendWorker.port.onmessage = e => {
			const [ func, ...args ] = e.data;
			if (func != 'fire')
				return;
			const [ targetId ] = args;
			if (targetId != id)
				return;
			if (evt === null)
				unblockDragend();
			else
				el.dispatchEvent(evt);
		}
	},
	updated(el, binding) {
		updateValue(el, binding.value);
		updateEffectAllowed(el, binding.arg);
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
