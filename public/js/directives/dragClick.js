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

		function onEnter(evt) {
			let lastTarget = evt.target;
			let lastX = evt.offsetX;
			let lastY = evt.offsetY;

			el.addEventListener('dragover', evt => {
				if (lastX != evt.offsetX || lastY != evt.offsetY || lastTarget != evt.target) {
					// moved
					lastTarget = evt.target;
					lastX = evt.offsetX;
					lastY = evt.offsetY;

					stopCountdown();
					startCountdown();
				}
			});

			startCountdown();
		}
		function onLeave() {
			stopCountdown();
		}

		// NOTE(chris): add save dragenter and dragleave events
		// that won't fire when hovering over child elements

		let skipLeave = false;
		let skipLeaveParent = true;

		function init(evt) {
			skipLeave = false;
			skipLeaveParent = true;
			// add global listeners
			window.addEventListener('dragenter', globalDragenter, true);
			window.addEventListener('dragleave', globalDragleave, true);
			window.addEventListener('drop', globalDrop, true);
			// call enter
			onEnter(evt);
			// remove self
			el.removeEventListener('dragenter', init);
		}

		function cleanup() {
			// remove global listeners
			window.removeEventListener('dragenter', globalDragenter, true);
			window.removeEventListener('dragleave', globalDragleave, true);
			window.removeEventListener('drop', globalDrop, true);
			// call leave
			onLeave();
			// add init
			el.addEventListener('dragenter', init);
		}

		function globalDragenter(evt) {
			skipLeaveParent = false;
			if (el != evt.target && !el.contains(evt.target)) {
				cleanup();
			} else {
				skipLeave = true;
			}
		}
		function globalDragleave(evt) {
			if (el != evt.target && !el.contains(evt.target)) {
				if (skipLeaveParent) {
					skipLeaveParent = false;
					return;
				}
			} else {
				if (skipLeave) {
					skipLeave = false;
					return;
				}
			}
			cleanup();
		}
		function globalDrop(evt) {
			cleanup();
		}

		el.addEventListener('dragenter', init);
		el.initFunc = init;
	},
	beforeUnmount(el) {
		el.removeEventListener('dragenter', el.initFunc);
		delete el.initFunc;
	}
}
