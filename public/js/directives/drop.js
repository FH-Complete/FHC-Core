import { getValidTransferData, eventHasTypes, bindDragEnterLeave } from '../helpers/DragAndDrop.js';

const EFFECTS = [
	'move',
	'copy',
	'link',
	'none'
];

let id = 0;
		
export default {
	mounted(el, binding, vnode) {
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

		function onEnter(evt) {
			allowed = eventHasTypes(evt, allowedTypes, strict);
			if (allowed) {
				evt.preventDefault();
				bcc.postMessage('block');
			}
		}
		function onLeave(evt, wasDropped) {
			if (allowed && !wasDropped) {
				bcc.postMessage('release');
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
					bcc.postMessage('release');
					return r;
				});
			} else {
				bcc.postMessage('release');
			}
		}

		const cleanupEnterLeave = bindDragEnterLeave(el, onEnter, onLeave);
		el.addEventListener('dragover', onOver);
		el.addEventListener('drop', onDrop);
		el.fhcDropCleanup = () => {
			cleanupEnterLeave();
			el.removeEventListener('dragover', onOver);
			el.removeEventListener('drop', onDrop);
		};
	},
	beforeUnmount(el) {
		el.fhcDropCleanup();
		delete el.fhcDropCleanup;
	}
}
