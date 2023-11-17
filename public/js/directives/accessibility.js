export default {
	created(el, binding) {
		switch (binding.arg) {
			case 'tab':
				const [prev, next] = binding.modifiers.vertical ? ['ArrowUp', 'ArrowDown'] : ['ArrowLeft', 'ArrowRight'];
				el.addEventListener('click', () => {
					let act = el.parentNode.querySelector('[tabindex="0"]');
					if (act)
						act.setAttribute('tabindex', -1);
					el.setAttribute('tabindex', 0);
				});
				el.addEventListener('focus', () => {
					el.setAttribute('aria-selected', true);
				});
				el.addEventListener('blur', () => {
					el.setAttribute('aria-selected', false);
				});
				el.addEventListener('keydown', e => {
					switch (e.code) {
						case prev:
							if (el.previousSibling?.setAttribute) {
								el.previousSibling.setAttribute('tabindex', 0);
								el.setAttribute('tabindex', -1);
								el.previousSibling.focus();
							}
							break;
						case next:
							if (el.nextSibling?.setAttribute) {
								el.nextSibling.setAttribute('tabindex', 0);
								el.setAttribute('tabindex', -1);
								el.nextSibling.focus();
							}
							break;
						case 'Enter':
							el.click();
							break;
					}
				});
				break;
		}
	},
	mounted(el, binding) {
		switch (binding.arg) {
			case 'tab':
				let activetab = -1;
				Array.from(el.parentNode.children).forEach((node, index) => {
					node.setAttribute('aria-setsize', el.parentNode.children.length);
					node.setAttribute('aria-posinset', index);
					if (node.getAttribute('tabindex') == '0')
						activetab = index;
				});
				if (activetab == -1) {
					el.setAttribute('tabindex', 0);
				} else if (el.classList.contains('active')) {
					el.parentNode.children[activetab].setAttribute('tabindex', -1);
					el.setAttribute('tabindex', 0);
				} else {
					el.setAttribute('tabindex', -1);
				}
				break;
		}
	},
	beforeUnmount(el, binding) {
		switch (binding.arg) {
			case 'tab':
				if (el.getAttribute('tabindex') == '0') {
					if (el.previousSibling?.setAttribute)
						el.previousSibling.setAttribute('tabindex', 0);
					else if (el.nextSibling?.setAttribute)
						el.nextSibling.setAttribute('tabindex', 0);
				}
				const pos = parseInt(el.getAttribute('aria-posinset'));
				Array.from(el.parentNode.children).forEach((node, index) => {
					node.setAttribute('aria-setsize', el.parentNode.children.length-1);
					if (index > pos)
						node.setAttribute('aria-posinset', index-1);
				});
			break;
		}
	},
	unmounted(el, binding) {
		switch (binding.arg) {
			case 'tab':
				console.log(el.parentNode);
			break;
		}
	}
}