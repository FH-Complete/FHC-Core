import { bindDragEnterLeave } from '../helpers/DragAndDrop.js';

export default {
	mounted(el, binding) {
		const delay = parseInt(binding.arg) || 300;

		let timeout = null;
		function startCountdown() {
			timeout = window.setTimeout(binding.value, delay);
		}
		function stopCountdown() {
			if (timeout)
				window.clearTimeout(timeout);
			timeout = null;
		}

		let lastTarget;
		let lastX;
		let lastY;

		function onDragOver(evt) {
			if (lastX != evt.offsetX || lastY != evt.offsetY || lastTarget != evt.target) {
				// moved
				lastTarget = evt.target;
				lastX = evt.offsetX;
				lastY = evt.offsetY;

				stopCountdown();
				startCountdown();
			}
		}

		function onEnter(evt) {
			lastTarget = evt.target;
			lastX = evt.offsetX;
			lastY = evt.offsetY;

			el.addEventListener('dragover', onDragOver);

			startCountdown();
		}
		function onLeave() {
			stopCountdown();
			el.removeEventListener('dragover', onDragOver);
		}

		el.fhcDragClickCleanup = bindDragEnterLeave(el, onEnter, onLeave);
	},
	beforeUnmount(el) {
		el.fhcDragClickCleanup();
		delete el.fhcDragClickCleanup;
	}
}
