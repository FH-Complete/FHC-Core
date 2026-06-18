// DatesManual.js: implemented custom input handling for tabulator date header filter,
// since primevue3 calendar manual input seems to be broken in this case. Main difference
// to normal Dates.js headerfilter is that this one does not automatically open the calendar
// overlay, as it tries to aggressively steal the focus away from the input field. try it out!

// Custom evaluation logic so Tabulator knows how to filter the array of Date objects
Tabulator.extendModule('filter', 'filters', {
	"dates": (headerValue, rowValue) => {
		if (!headerValue) return true;

		let rowDate = new Date(rowValue);

		if (Array.isArray(headerValue)) {
			let startDate = new Date(headerValue[0]);
			if (headerValue[1]) {
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

export function dateFilter(cell, onRendered, success) {
	let div = document.createElement('div');

	let initialValue = null;
	let val = cell.getValue();
	if (Array.isArray(val)) {
		const start = val[0] ? new Date(val[0]) : null;
		const end   = val[1] ? new Date(val[1]) : null;
		initialValue = [start, end];
	}

	// Manual parser needed since we are bypassing PrimeVue's broken manualInput mode
	function parseDMY(str) {
		const m = str.trim().match(/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/);
		if (!m) return null;
		let year = parseInt(m[3]);
		if (year < 100) year += 2000;
		const d = new Date(year, parseInt(m[2]) - 1, parseInt(m[1]));
		return isNaN(d.getTime()) ? null : d;
	}

	// String formatter to sync the raw text field when dates are picked via the calendar UI
	function formatRange(dates) {
		if (!dates) return '';
		const fmt = d => d
			? `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()}`
			: '';
		if (Array.isArray(dates)) {
			return fmt(dates[0]) + (dates[1] ? ` - ${fmt(dates[1])}` : '');
		}
		return fmt(dates);
	}

	Vue.createApp({
		components: { PrimevueCalendar: primevue.calendar },
		data() {
			return {
				// Split state into a proper Date object (calendar) and a raw string (text input)
				calVal: initialValue,
				textVal: formatRange(initialValue)
			};
		},
		watch: {
			calVal(n) {
				// Centralized synchronization: updates text representation 
				// AND safely notifies Tabulator of the value change
				this.textVal = formatRange(n);
				success(n);
			}
		},
		methods: {
			// Translates the typed string back into Date objects for Tabulator's filter
			onTextChange() {
				if (!this.textVal) {
					this.calVal = null; // Triggers watcher -> success(null)
					return;
				}
				const parts = this.textVal.split(/\s*-\s*/);
				const start = parseDMY(parts[0]);
				const end   = parts[1] ? parseDMY(parts[1]) : null;

				if (start) {
					// Changing calVal automatically triggers the watcher,
					// which handles executing success() exactly once.
					this.calVal = [start, end];
				}
			}
		},
		// Native HTML input handles typing, PrimeVue calendar input is hidden (icon-only)
		// Placeholder removed to match the rest of the application's header filters
		template: `
        <div style="display:flex;align-items:center;width:100%">
            <input
                type="text"
                v-model="textVal"
                @change="onTextChange"
                @keydown.stop
                @keypress.stop
                @keyup.stop
                @mousedown.stop
                class="p-inputtext p-component"
                style="flex:1;min-width:0"
            />
            <primevue-calendar
                v-model="calVal"
                selection-mode="range"
                show-button-bar
                :showIcon="true"
                :input-style="{display:'none'}"
                dateFormat="dd.mm.yy">
            </primevue-calendar>
        </div>`
	}).use(primevue.config.default).mount(div);

	return div;
}