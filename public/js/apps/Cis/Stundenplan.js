import FhcCalendar from "../../components/Calendar/Calendar.js";

const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null
		}
	},
	created() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/Stunden').then(res => {
			res.data.retval.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});
			axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan').then(res => {
				let events;
				if (res.data.retval && res.data.retval.forEach) {
					res.data.retval.forEach((el, i) => {
						el.id = i;
						el.color = '#' + (el.farbe || 'CCCCCC');
						el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
						el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
						el.title = el.lehrfach;
						if (el.lehrform)
							el.title += '-' + el.lehrform;
					});
					events = res.data.retval;
				}
				// TODO(chris): do we need that
				axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/Reservierungen').then(res => {
					if (res.data.retval && res.data.retval.forEach) {
						res.data.retval.forEach((el, i) => {
							el.id = i + events.length;
							el.color = '#CCCCCC';
							el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
							el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
							el.title = el.lehrfach;
							if (el.lehrform)
								el.title += '-' + el.lehrform;
						});
						events = [...events, ...res.data.retval];
					}
					this.events = events;
				});
			});
		});
	}
});
app.config.unwrapInjectedRef = true;
app.mount('#content');