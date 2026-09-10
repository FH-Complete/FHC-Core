import { getValidTransferData, eventHasTypes, bindDragEnterLeave } from '../helpers/DragAndDrop.js';

import { enableDragDropTouch } from "../../../vendor/drag-drop-touch-js/dragdroptouch/dist/drag-drop-touch.esm.min.js";

if (!document.dragDropTouchActive) {
	enableDragDropTouch();
	document.dragDropTouchActive = true;
}

const EFFECTS = [
	'move',
	'copy',
	'link',
	'none'
];

export default {
	mounted(el, binding) {
		// A DOM node can be reused while Vue replaces directive bindings. Tear
		// down a stale binding before installing the new one.
		if (typeof el.fhcDropCleanup === 'function')
			el.fhcDropCleanup();

		if (!binding.arg) {
			binding.arg = 'none';
		} else if (typeof binding.arg === 'object' && !Array.isArray(binding.arg)) {
			// NOTE(chris): allow object as arg and map it to arg and
			// modifiers to allow dynamic modifiers.
			if (binding.arg.allowed) {
				binding.modifiers = binding.arg.allowed.reduce((a, c) => {
					a[c] = true;
					return a;
				}, {});
			}
			if (!binding.arg.effect)
				binding.arg.effect = 'none';
			
			if (binding.arg.strict)
				binding.arg = binding.arg.effect + '-strict';
			else 
				binding.arg = binding.arg.effect;
		}
		
		const allowedTypes = Object.keys(binding.modifiers);
		allowedTypes.forEach(type => {
			if (type.substr(-11) == '-collection') {
				const singleType = type.substr(0, type.length-11);
				if (!allowedTypes.includes(singleType))
					allowedTypes.push(singleType);
			}
		});

		const strict = binding.arg.match(/(strict-|-strict)/);
		const arg = binding.arg.replace(/(strict-|-strict)/, '');
		const effect = EFFECTS.includes(arg) ? arg : null;

		const bcc = new BroadcastChannel('fhc-dnd');
		let allowed = false;
		let cleanedUp = false;
		const release = () => {
			if (!cleanedUp)
				bcc.postMessage('release');
		};

		function onEnter(evt) {
			allowed = eventHasTypes(evt, allowedTypes, strict);
			if (allowed) {
				evt.preventDefault();
				bcc.postMessage('block');
			}
		}
		function onLeave(evt, wasDropped) {
			if (allowed && !wasDropped) {
				release();
			}
		}
		function onOver(evt) {
			if (allowed) {
				evt.preventDefault();
				if (effect)
					evt.dataTransfer.dropEffect = effect;
			}
		}
		function onDrop(evt) {
			let result = getValidTransferData(evt, allowedTypes, strict);
			if (!Array.isArray(result) && !binding.modifiers[result.type] && allowedTypes.includes(result.type + '-collection'))
				result = [result];

			const res = binding.value(evt, result);

			if (res instanceof Promise) {
				res.then(r => {
					release();
					return r;
				});
			} else {
				release();
			}
		}

		const cleanupEnterLeave = bindDragEnterLeave(el, onEnter, onLeave);
		el.addEventListener('dragover', onOver);
		el.addEventListener('drop', onDrop);
		el.fhcDropCleanup = () => {
			if (cleanedUp)
				return;
			if (allowed)
				bcc.postMessage('release');
			cleanedUp = true;
			allowed = false;

			cleanupEnterLeave();
			el.removeEventListener('dragover', onOver);
			el.removeEventListener('drop', onDrop);
			bcc.onmessage = null;
			bcc.close?.();
		};
	},
	beforeUnmount(el) {
		if (typeof el.fhcDropCleanup === 'function')
			el.fhcDropCleanup();
		delete el.fhcDropCleanup;
	}
}
