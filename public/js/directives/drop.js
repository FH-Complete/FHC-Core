import { getValidTransferData, eventHasTypes, dragendWorker } from '../helpers/DragAndDrop.js';

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

		let allowed = false;

		el.addEventListener('dragenter', evt => {
			allowed = eventHasTypes(evt, allowedTypes, strict);
			if (allowed)
				evt.preventDefault();
		});
		el.addEventListener('dragover', evt => {
			if (allowed) {
				evt.preventDefault();
				if (effect)
					evt.dataTransfer.dropEffect = effect;
			}
		});
		el.addEventListener('drop', evt => {
			let result = getValidTransferData(evt, allowedTypes, strict);
			if (!Array.isArray(result) && !binding.modifiers[result.type] && allowedTypes.includes(result.type + '-collection'))
				result = [result];

			const res = binding.value(evt, result);
			if (res instanceof Promise) {
				const localId = id++;
				dragendWorker.port.postMessage(['block', localId]);
				res.then(r => {
					dragendWorker.port.postMessage(['unblock', localId]);
					return r;
				});
			}
		});
	}
}
