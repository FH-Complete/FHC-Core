if (!primevue) {
	console.error('PrimeVue not loaded!');
}

// NOTE(chris): Click on clear button gives an error. This is a bug in primevue => fixed in current version
Tabulator.extendModule('filter', 'filters', {
	"dates": (headerValue, rowValue) => {
		if (!headerValue)
			return true;
		let v = new Date(rowValue);
		if (Array.isArray(headerValue)) {
			if (headerValue[1]) {
				return v >= headerValue[0] && v <= headerValue[1].setHours(23, 59, 59, 999);
			}
			return v.toDateString() == headerValue[0].toDateString();
		}
		return v.toDateString() == headerValue.toDateString();
	}
});
function dateFilter(cell, onRendered, success) {
	let div = document.createElement('div');

	Vue.createApp({
		components: {
			PrimevueCalendar: primevue.calendar
		},
		data() {
			return {
				val: null
			}
		},
		watch: {
			val(n) {
				success(n);
			}
		},
		template: `<primevue-calendar v-model="val" selection-mode="range" :manual-input="false" show-button-bar></primevue-calendar>`
	}).use(primevue.config.default).mount(div);

	return div;
}

export { dateFilter as 'dateFilter' };
