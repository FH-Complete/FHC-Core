export function capitalize(string) {
	if (!string) return '';
	
	if (Vue.isRef(string)) {
		console.log('Vue.isRef(string)', string)

		return Vue.computed(() => {
			const val = Vue.unref(string);
			return (val && typeof val === 'string') ? val.charAt(0).toUpperCase() + val.slice(1) : '';
		});
	}

	// just a plain string, return a plain string
	if (typeof string === 'string') {
		console.log('if (typeof string === \'string\') {', string)
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	return '';
	
}