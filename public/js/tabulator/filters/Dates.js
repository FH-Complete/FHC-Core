if (!primevue) {
	console.error('PrimeVue not loaded!');
}

// NOTE(chris): Click on clear button gives an error. This is a bug in primevue => fixed in current version
Tabulator.extendModule('filter', 'filters', {
	"dates": (headerValue, rowValue) => {
		if (!headerValue)
			return true;

		let rowDate = new Date(rowValue);

		if (Array.isArray(headerValue))
		{
			let startDate = new Date(headerValue[0]);
			if (headerValue[1])
			{
				let endDate = new Date(headerValue[1]);

				endDate.setHours(23, 59, 59, 999);

				return rowDate >= startDate && rowDate <= endDate;
			}

			return rowDate.toDateString() === startDate.toDateString();
		}
		let singleDate = new Date(headerValue);
		return rowDate.toDateString() === singleDate.toDateString();
	}
});

function dateFilter(cell, onRendered, success) {
	let div = document.createElement('div');

	let initialValue = null;

	let val = cell.getValue();

	if (Array.isArray(val))
	{
		const start = val[0] ? new Date(val[0]) : null;
		const end   = val[1] ? new Date(val[1]) : null;
		initialValue = [start, end];
	}

	Vue.createApp({
		components: {
			PrimevueCalendar: primevue.calendar
		},
		data() {
			return {
				val: initialValue
			}
		},
		watch: {
			val(n) {
				success(n);
			}
		},
		template: `<primevue-calendar 
					v-model="val" 
					selection-mode="range" 
					:manual-input="false" 
					show-button-bar 
					:showIcon="true"
					dateFormat="dd.mm.yy">
				   </primevue-calendar>`
	}).use(primevue.config.default).mount(div);

	return div;
}
export { dateFilter };