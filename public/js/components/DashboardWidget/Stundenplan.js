import Phrasen from '../../mixins/Phrasen.js';
import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Calendar.js';

export default {
	mixins: [
		Phrasen,
		AbstractWidget
	],
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			minimized: true,
			events: null,
			currentDay: new Date()
		}
	},
	computed: {
		currentEvents() {
			return (this.events || []).filter(evt => evt.end < this.dayAfterCurrentDay && evt.start >= this.currentDay);
		},
		dayAfterCurrentDay() {
			let currentDay = new Date(this.currentDay);
			currentDay.setDate(currentDay.getDate() + 1);
			return currentDay;
		}
	},
	methods: {
		selectDay(day) {
			this.currentDay = day;
			this.minimized = true;
		}
	},
	created() {
		this.$emit('setConfig', false);
		axios
			.get(this.apiurl + '/components/Cis/Stundenplan/Stunden').then(res => {
				res.data.retval.forEach(std => {
					this.stunden[std.stunde] = std; // TODO(chris): geht besser
				});
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
					.catch(err => { console.log(err);console.error('ERROR: ', err.response.data) });
			})
			.catch(err => { console.error('ERROR: ', err.response.data) });
	},
	template: `
	<div class="dashboard-widget-stundenplan d-flex flex-column h-100">
		<fhc-calendar :initial-date="currentDay" class="border-0" class-header="p-0" @select:day="selectDay" v-model:minimized="minimized" :events="events" no-week-view :show-weeks="false" />
		<div v-show="minimized" class="flex-grow-1 overflow-scroll">
			<div v-if="events === null" class="d-flex h-100 justify-content-center align-items-center">
				<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
			</div>
			<div v-else-if="currentEvents.length" class="list-group list-group-flush">
				<div class="" v-for="evt in currentEvents" :key="evt.id" class="list-group-item small" :style="{'background-color':evt.color}">
					<b>{{evt.title}}</b>
					<br>
					<small class="d-flex w-100 justify-content-between">
						<span>{{evt.ort_kurzbz}}</span>
						<span>{{evt.start.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}-{{evt.end.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}</span>
					</small>
				</div>
			</div>
			<div v-else class="d-flex h-100 justify-content-center align-items-center fst-italic text-center">
				{{ p.t('lehre/noLvFound') }}
			</div>
		</div>
	</div>`
}