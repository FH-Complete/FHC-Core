/**
 * TODO(chris): This needs serious rework!!!
 */
export default {
	mounted(el, binding, vnode) {
		if (binding.arg == 'draggable') {
			el.addEventListener('update-my-value', evt => {
				evt.preventDefault();
				binding.value = evt.detail.item;
			});
			el.addEventListener('dragstart', evt => {
				el.dispatchEvent(new CustomEvent('calendar-dragstart', {
					cancelable: true,
					bubbles: true,
					detail: {
						item: binding.value,
						x: evt.offsetX / el.offsetWidth,
						y: evt.offsetY / el.offsetHeight,
						originalEvent: evt
					}
				}));
			});
			el.addEventListener('dragend', evt => {
				el.dispatchEvent(new CustomEvent('calendar-dragend', {
					cancelable: true,
					bubbles: true,
					detail: {
						item: binding.value,
						originalEvent: evt
					}
				}));
			});
		} else if (binding.arg == 'dropcage') {
			let hitbox = null;
			el.addEventListener('dragover', evt => {
				if (hitbox)
					return;
				hitbox = el.getBoundingClientRect();
				return el.dispatchEvent(new CustomEvent('calendar-dragenter', {
					detail: { originalEvent: evt }
				}));
			});
			window.addEventListener('dragleave', evt => {
				if (!hitbox)
					return;
				let pos;
				if (typeof evt.clientX === 'undefined')
					pos = {
						x: evt.pageX + document.documentElement.scrollLeft,
						y: evt.pageY + document.documentElement.scrollTop
					};
				else
					pos = {
						x: evt.clientX + document.body.scrollLeft + document.documentElement.scrollLeft,
						y: evt.clientY + document.body.scrollTop + document.documentElement.scrollTop
					};
				if (pos.x > hitbox.left + hitbox.width - 1 || pos.x < hitbox.left || pos.y > hitbox.top + hitbox.height - 1 || pos.y < hitbox.top) {
					hitbox = null;
					return el.dispatchEvent(new CustomEvent('calendar-dragleave', {
						detail: { originalEvent: evt }
					}));
				}
			});
			window.addEventListener('drop', evt => {
				if (!hitbox)
					return;
				
				hitbox = null;
				return el.dispatchEvent(new CustomEvent('calendar-dragleave', {
					detail: { originalEvent: evt }
				}));
			});
		} else if (binding.arg == 'dropzone') {
			el.addEventListener(
				binding.modifiers.once ? 'dragenter' : 'dragover',
				evt => {
					const timestamp = binding.value instanceof Function
						? binding.value(evt)
						: binding.value;
					const detail = timestamp.timestamp ? timestamp : { timestamp };
					el.dispatchEvent(new CustomEvent('calendar-dragchange', {
						cancelable: true,
						bubbles: true,
						detail
					}));
				}
			);
		}
	},
	updated(el, binding, vnode, prevVnode) {
		if (binding.arg == 'draggable') {
			el.dispatchEvent(new CustomEvent('update-my-value', {
				cancelable: true,
				detail: {
					item: binding.value
				}
			}));
		}
	}
}