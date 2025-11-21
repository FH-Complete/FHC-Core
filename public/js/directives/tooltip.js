/**
 * v-tooltip - This directive makes it easy to initialize Bootstrap tooltips in Vue.
 *
 * Features:
 * - Automatically initializes a Bootstrap tooltip on mount.
 * - Re-initializes only when the bound value changes.
 * - Cleans up the tooltip on unmount.

 * Usage examples:
 *
 * 1) Shortest way:
 *    <span v-tooltip title="Static tooltip">
 *        Hover me!
 *    </span>
 *
 * 2) With binding value. New value will trigger update features (destroy old + create new tooltip creation)
 *    <span v-tooltip="userInfo" :title="userInfo">
 *        Hover me!
 *    </span>
 *
 * 3) Allowing HTML inside tooltip:
 *    <span v-tooltip title="<b>Bold text</b>" data-bs-html="true">
 *        Hover me!
 *    </span>
 *
 */
export default {
	mounted(el, binding) {
		const opts = {
			title: binding.value ?? el.getAttribute('title'), // fallback if no binding
			html: el.getAttribute('data-bs-html') === 'true',
			customClass: el.getAttribute('data-bs-custom-class') || ''
		};

		// Create tooltip
		el._tooltip = new bootstrap.Tooltip(el, opts);
	},
	updated(el, binding) {
		// Only dispose and create new Tooltip if value (the title-string) has changed
		if (binding.value !== binding.oldValue){

			if (el._tooltip) {
				el._tooltip.dispose();
			}

			const opts = {
				title: binding.value ?? el.getAttribute('title'), // fallback if no binding
				html: el.getAttribute('data-bs-html') === 'true',
				customClass: el.getAttribute('data-bs-custom-class') || ''
			};

			el._tooltip = new bootstrap.Tooltip(el, opts);
		}
	},
	unmounted(el) {
		// Cleanup
		if (el._tooltip) {
			el._tooltip.dispose();
			delete el._tooltip;
		}
	}
}
