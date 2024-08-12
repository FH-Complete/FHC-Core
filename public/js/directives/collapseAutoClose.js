const elementDataMap = new WeakMap();

export default {
	mounted(el, binding) {
		let open = false;
		elementDataMap.set(el, evt => {
			if (!open)
				return;

			if (el.contains(evt.target))
				return;

			const collapse = bootstrap.Collapse.getInstance(el)
			if (collapse)
				collapse.hide();
		});
		el.addEventListener('shown.bs.collapse', () => {
			open = true;
		});
		el.addEventListener('hide.bs.collapse', () => {
			open = false;
		});
		document.addEventListener('click', elementDataMap.get(el), true);
	},
	beforeUnmount(el, binding) {
		document.removeEventListener('click', elementDataMap.get(el));
		delete el.collapsibleAutoHideFunc;
	}
}