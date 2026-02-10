export function capitalize(string) {
	if (!string) return '';
	
	// ref unwrap if we receive such
	if (Vue.isRef(string)) {
		return Vue.computed(() => {
			const val = Vue.unref(string);
			return (val && typeof val === 'string') ? val.charAt(0).toUpperCase() + val.slice(1) : '';
		});
	}

	// just a plain string, return a plain string
	if (typeof string === 'string') {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	return '';
	
}