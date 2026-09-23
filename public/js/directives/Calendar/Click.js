const clickListeners = [];

function saveAddClickListener(el, source, value) {
	const index = clickListeners.findIndex(data => data.el == el);
	if (index >= 0) {
		el.removeEventListener('click', clickListeners[index].listener);
		clickListeners.splice(index, 1);
	}
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
			el.addEventListener('cal-click', evt => {
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
			});
		} else {
			saveAddClickListener(el, binding.arg, binding.value);
		}
	},
	updated(el, binding, vnode, prevVnode) {
		if (binding.arg != 'container') {
			saveAddClickListener(el, binding.arg, binding.value);
		}
	}
}