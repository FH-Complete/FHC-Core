import AbstractWidget from './Abstract';
import FhcCalendar from '../Calendar/Calendar';

export default {
	mixins: [
		AbstractWidget
	],
	components: {
		FhcCalendar
	},
	data() {
		return {
			minimized: true,
			events: null,
			currentDay: new Date()
		}
	},
	computed: {
		currentEvents() {
			return (this.events || []).filter(evt => evt.end > this.currentDay && evt.start <= this.currentDay);
		}
	},
	methods: {
		selectDay(day) {
			this.minimized = true;
		}
	},
	created() {
		axios
			.get(this.apiurl + '/components/Cis/Stundenplan')
			.then(res => {
				res.data.retval.forEach((el, i) => {
					el.id = i;
					el.color = '#' + (el.farbe || 'CCCCCC');
					el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
					el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
					el.title = el.lehrfach;
					if (el.lehrform)
						el.title += '-' + el.lehrform;
				});
				this.events = res.data.retval || [];
			})
			.catch(err => { console.error('ERROR: ', err.response.data) });
	},
	template: `
	<div class="dashboard-widget-stundenplan" v-if="configMode">
		config
	</div>
	<div class="dashboard-widget-stundenplan" v-else>
		<fhc-calendar class="border-0" @select:day="selectDay" v-model:minimized="minimized" :events="events" no-week-view :show-weeks="false" />
		<div v-show="minimized">
			{{currentEvents}}
		</div>
	</div>`
}