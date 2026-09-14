const clickListeners = [];
const containerListeners = new WeakMap();

function removeClickListener(el) {
	const index = clickListeners.findIndex(data => data.el == el);
	if (index < 0)
		return;

	el.removeEventListener('click', clickListeners[index].listener);
	clickListeners.splice(index, 1);
}

function removeContainerListener(el) {
	const listener = containerListeners.get(el);
	if (!listener)
		return;

	el.removeEventListener('cal-click', listener);
	containerListeners.delete(el);
}

function saveAddClickListener(el, source, value) {
	removeClickListener(el);

	const listener = evt => {
		evt.preventDefault();
		evt.stopPropagation();
		const customEvent = new CustomEvent('cal-click', {
			cancelable: true,
			bubbles: true,
			detail: { source, value }
		});
		evt.target.dispatchEvent(customEvent);
	}
	clickListeners.push({el, listener});
	el.addEventListener('click', listener);
}

export default {
	mounted(el, binding, vnode) {
		if (binding.arg == 'container') {
			removeContainerListener(el);
			const listener = evt => {
				const customEvent = new Event('click:' + evt.detail.source, {
					cancelable: true
				});
				binding.instance.$emit('click:' + evt.detail.source, customEvent, evt.detail.value);
				if (!customEvent.defaultPrevented) {
					const finalEvent = new CustomEvent('cal-click-default', {
						cancelable: true,
						bubbles: true,
						detail: evt.detail
					});
					evt.target.dispatchEvent(finalEvent);
				}
			};
			containerListeners.set(el, listener);
			el.addEventListener('cal-click', listener);
		} else {
			saveAddClickListener(el, binding.arg, binding.value);
		}
	},
	updated(el, binding, vnode, prevVnode) {
		if (binding.arg != 'container') {
			saveAddClickListener(el, binding.arg, binding.value);
		}
	},
	beforeUnmount(el, binding) {
		if (binding.arg == 'container')
			removeContainerListener(el);
		else
		if (binding.arg != 'container')
			removeClickListener(el);
	}
}
