import FhcCalendar from "../../components/Calendar/Calendar.js";
import Phrasen from "../../plugin/Phrasen.js";

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
	},
	template:/*html*/`
	<h2>Stundenplan</h2>
	<hr>
	<fhc-calendar v-slot="{event, day}" :events="events" initial-mode="week" show-weeks>
		<div class="d-flex flex-column align-items-center justify-content-evenly h-100">
			<span>{{event.orig.title}}</span>
			<span v-for="lektor in event.orig.lektor">{{lektor.kurzbz}}</span>
			<span>{{event.orig.ort_kurzbz}}</span>
		</div>
	</fhc-calendar>
	`
});
app.config.unwrapInjectedRef = true;
app.use(Phrasen);
app.mount('#content');