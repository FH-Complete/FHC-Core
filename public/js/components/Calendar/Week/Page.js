import CalendarDate from '../../../composables/CalendarDate.js';

function ggt(m,n) { return n==0 ? m : ggt(n, m%n); }
function kgv(m,n) { return (m*n) / ggt(m,n); }

export default {
	inject: [
		'date',
		'focusDate',
		'size',
		'events',
		'noMonthView'
	],
	props: {
		year: Number,
		week: Number
	},
	emits: [
		'update:mode',
		'page:back',
		'page:forward',
		'input'
	],
	data() {
		return {
			hours: [...Array(24).keys()]
		};
	},
	computed: {
		days() {
			let tmpDate = new CalendarDate(this.year, 1, 1); // NOTE(chris): somewhere in the middle of the year
			tmpDate.w = this.week;
			let startDay = tmpDate.firstDayOfWeek;
			let result = [];
			for (let i = 0; i < 7; i++) {
				result.push(new Date(startDay.getFullYear(), startDay.getMonth(), startDay.getDate() + i));
			}
			return result;
		},
		eventsPerDayAndHour() {
			const res = {};
			this.days.forEach(day => {
				let key = day.toDateString();
				
				let nextDay = new Date(day);
				nextDay.setDate(nextDay.getDate()+1);
				nextDay.setMilliseconds(nextDay.getMilliseconds()-1);
				let d = {events:[],lanes:1};
				if (this.events[key]) {
					this.events[key].forEach(evt => {
						let event = {orig:evt,lane:1,maxLane:1,start: evt.start < day ? day : evt.start, end: evt.end > nextDay ? nextDay : evt.end,shared:[],setSharedMaxRecursive(doneItems) {
							this.maxLane = Math.max(doneItems[0].maxLane, this.maxLane);
							doneItems.push(this);
							this.shared.filter(other => !doneItems.includes(other)).forEach(i => i.setSharedMaxRecursive(doneItems));
						}};
						event.shared = d.events.filter(other => other.start < event.end && other.end > event.start);
						event.shared.forEach(other => other.shared.push(event));
						let occupiedLanes = event.shared.map(other => other.lane);
						while (occupiedLanes.includes(event.lane))
							event.lane++;
						event.maxLane = Math.max(...[event.lane], ...occupiedLanes);
						if (event.maxLane > 1) {
							event.setSharedMaxRecursive([event]);
						}
						d.events.push(event);
					});
					d.lanes = d.events.map(e => e.maxLane).reduce((res, i) => kgv(res, i), 1);
				}
				res[key] = d;
			});
			return res;
		},
		smallestTimeFrame() {
			return [30,15,10,5][this.size];
		}
	},
	methods: {
		changeToMonth(day) {
			if (!this.noMonthView) {
				this.date.set(day);
				this.focusDate.set(day);
				this.$emit('update:mode', 'month');
			}
		},
		dateToMinutesOfDay(day) {
			return Math.floor((day.getHours() * 60 + day.getMinutes()) / this.smallestTimeFrame) + 1;
		}
	},
	mounted() {
		setTimeout(() => this.$refs.eventcontainer.scrollTop = this.$refs.eventcontainer.scrollHeight / 3 + 1, 0);
	},
	template: `
	<div class="fhc-calendar-week-page">
		<div class="d-flex flex-column border-top">
			<div class="fhc-calendar-week-page-header border-2 border-bottom text-center d-flex">
				<div v-for="day in days" :key="day" class="flex-grow-1" :title="day.toLocaleString(undefined, {dateStyle:'short'})">
					<div class="fw-bold">{{day.toLocaleString(undefined, {weekday: size < 2 ? 'narrow' : (size < 3 ? 'short' : 'long')})}}</div>
					<a href="#" class="small text-secondary text-decoration-none" @click.prevent="changeToMonth(day)">{{day.toLocaleString(undefined, [{day:'numeric',month:'numeric'},{day:'numeric',month:'numeric'},{day:'numeric',month:'numeric'},{dateStyle:'short'}][this.size])}}</a>
				</div>
			</div>
			<div ref="eventcontainer" class="flex-grow-1 overflow-auto">
				<div class="events">
					<div class="hours">
						<div v-for="hour in hours" :key="hour" class="text-muted text-end small" :ref="'hour' + hour">{{hour}}:00</div>
					</div>
					<div v-for="day in eventsPerDayAndHour" :key="day" class="day border-start" :style="{'grid-template-columns': 'repeat(' + day.lanes + ', 1fr)', 'grid-template-rows': 'repeat(' + (1440 / smallestTimeFrame) + ', 1fr)'}">
						<a href="#" v-for="event in day.events" :key="event" class="small rounded overflow-hidden text-decoration-none text-dark" :style="{'grid-column-start': 1+(event.lane-1)*day.lanes/event.maxLane, 'grid-column-end': 1+event.lane*day.lanes/event.maxLane, 'grid-row-start': dateToMinutesOfDay(event.start), 'grid-row-end': dateToMinutesOfDay(event.end), '--test': dateToMinutesOfDay(event.end), background: event.orig.color}" @click.prevent="$emit('input', event.orig)">
							{{event.orig.title}}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>`
}
