/**
 * size returns the key of smallest threshold array (integer converts to
 * { compact: threshold }) entry that is bigger than the current width of
 * the element or 'full' if none is found.
 * compact is true if the smallest threshold array entry is bigger than the
 * current width or false otherwise.
 *
 * @param DOMElement|VueTemplateRef		element
 * @param object|array|integer			threshold
 * @return object						{ compact:Boolean, size:String }
 */
export function useResizeObserver(element, threshold) {
	/* Result Vars */
	const compact = Vue.ref(false);
	const size = Vue.ref(false);

	/* Helper Vars */
	const mounted = Vue.ref(false);
	const elementRef = Vue.computed(() => {
		if (!Vue.isRef(element))
			return element;
		
		if (!element.value)
			return element.value;
		
		if (element.value.$el) // Maybe there is a better test
			return element.value.$el;

		return element.value;
	});
	const compareArray = Vue.computed(() => {
		const input = Vue.isRef(threshold) ? threshold.value : threshold;
		if (Number.isInteger(input))
			return [['compact', input]];
		if (Array.isArray(input))
			return input.map((value, key) => [key, value]).sort((a, b) => a[1]-b[1]);
		return Object.entries(input).sort((a, b) => a[1]-b[1]);
	});

	/* Helper Functions */
	function updateResultVars() {
		const compare = threshold.value || threshold;
		if (elementRef.value.offsetWidth === undefined)
			return;

		const found = compareArray.value.find(compare => compare[1] > elementRef.value.offsetWidth);

		size.value = found ? found[0] : 'full';
		compact.value = (size.value == compareArray.value[0][0]);
	}

	/* Observer */
	const observer = new ResizeObserver(() => {
		if (elementRef.value) {
			updateResultVars();
		}
	});
	/* Observer Helper Functions */
	function addObserver() {
		if (!elementRef.value)
			return;

		updateResultVars();
		observer.observe(elementRef.value);
		mounted.value = true;
	}
	function removeObserver() {
		if (mounted.value) {
			observer.disconnect()
		}
	}

	/* Main Logic */
	Vue.onMounted(addObserver);
	Vue.onUnmounted(removeObserver);
	
	Vue.watchEffect(() => {
		if (elementRef.value) {
			removeObserver();
			addObserver();
		}
	});

	return { compact, size };
}