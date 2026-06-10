export default {
	created(el, binding) {
		switch (binding.arg) {
			case 'tab':
				el.style.cursor = 'pointer';
				const [prev, next] = binding.modifiers.vertical ? ['ArrowUp', 'ArrowDown'] : ['ArrowLeft', 'ArrowRight'];
				el.addEventListener('click', () => {
					let act = el.parentNode.querySelector('[tabindex="0"]');
					if (act)
						act.tabIndex = -1;
					el.tabIndex = 0;
				});
				el.addEventListener('focus', () => {
					el.ariaSelected = "true";
				});
				el.addEventListener('blur', () => {
					el.ariaSelected = "false";
				});
				el.addEventListener('keydown', e => {
					switch (e.code) {
						case prev:
							if (el.previousSibling?.tabIndex !== undefined) {
								el.previousSibling.tabIndex = 0;
								el.tabIndex = -1;
								el.previousSibling.focus();
							}
							break;
						case next:
							if (el.nextSibling?.tabIndex !== undefined) {
								el.nextSibling.tabIndex = 0;
								el.tabIndex = -1;
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
					node.ariaSetSize = el.parentNode.children.length;
					node.ariaPosInSet = index;
					if (node.tabIndex === 0)
						activetab = index;
				});
				if (activetab == -1) {
					el.tabIndex = 0;
				} else if (el.classList.contains('active')) {
					el.parentNode.children[activetab].tabIndex = -1;
					el.tabIndex = 0;
				} else {
					el.tabIndex = -1;
				}
				break;
		}
	},
	beforeUnmount(el, binding) {
		switch (binding.arg) {
			case 'tab':
				if (el.tabIndex === 0) {
					if (el.previousSibling?.tabIndex !== undefined)
						el.previousSibling.tabIndex = 0;
					else if (el.nextSibling?.tabIndex !== undefined)
						el.nextSibling.tabIndex = 0;
				}
				const pos = parseInt(el.ariaPosInSet);
				Array.from(el.parentNode.children).forEach((node, index) => {
					node.ariaSetSize = el.parentNode.children.length;
					if (index > pos)
						node.ariaPosInSet = index-1;
				});
			break;
		}
	}
}