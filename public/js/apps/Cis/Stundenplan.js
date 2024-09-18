import FhcCalendar from "../../components/Calendar/Calendar.js";
import Phrasen from "../../plugin/Phrasen.js";
import CalendarDate from "../../composables/CalendarDate.js";


const app = Vue.createApp({
	data() {
		return {
			stunden: [],
			events: null,
			calendarDate: new CalendarDate(new Date()),

		}
	},
	components: {
		FhcCalendar
	},
	computed:{
		weekFirstDay: function () {
			return this.calendarDateToString(this.calendarDate.cdFirstDayOfWeek);
		},
		weekLastDay: function () {
			return this.calendarDateToString(this.calendarDate.cdLastDayOfWeek);
		},
		monthFirstDay: function () {
			return this.calendarDateToString(this.calendarDate.cdFirstDayOfCalendarMonth);
		},
		monthLastDay: function () {
			return this.calendarDateToString(this.calendarDate.cdLastDayOfCalendarMonth);
		},
	},
	methods:{

		updateRange: function (data) {
			let tmp_date = new CalendarDate(data.start);
			// only load month data if the month or year has changed
			if(tmp_date.m != this.calendarDate.m || tmp_date.y != this.calendarDate.y){
				this.calendarDate = tmp_date;
				Vue.nextTick(() => {
					this.loadEvents();
				});
			}
		},

		calendarDateToString: function (calendarDate) {

			return calendarDate instanceof CalendarDate ?
				[calendarDate.y, calendarDate.m + 1, calendarDate.d].join('-') :
				null;

		},

		loadEvents: function(){
			Promise.allSettled([
				this.$fhcApi.factory.stundenplan.getStundenplan(this.monthFirstDay, this.monthLastDay),
				this.$fhcApi.factory.stundenplan.getStundenplanReservierungen(this.monthFirstDay, this.monthLastDay)
			]).then((result) => {
				let promise_events = [];
				result.forEach((promise_result) => {
					if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success") {

						let data = promise_result.value.data;
						// adding additional information to the events 
						if (data && data.forEach) {

							data.forEach((el, i) => {
								el.id = i;
								if (el.type === 'reservierung') {
									el.color = '#' + (el.farbe || 'FFFFFF');
								} else {
									el.color = '#' + (el.farbe || 'CCCCCC');
								}

								el.start = new Date(el.datum + ' ' + el.beginn);
								el.end = new Date(el.datum + ' ' + el.ende);

							});
						}
						promise_events = promise_events.concat(data);
					}
				})
				this.events = promise_events;
			});
		},
	},
	created(){
		this.loadEvents();
	},
	template:/*html*/`
	<h2>Stundenplan</h2>
	<hr>
	<fhc-calendar @change:range="updateRange" v-slot="{event, day}" :events="events" initial-mode="week" show-weeks>
		<div type="button" class="d-flex flex-column align-items-center justify-content-evenly h-100">
			<span>{{event.orig.topic}}</span>
			<span v-for="lektor in event.orig.lektor">{{lektor.kurzbz}}</span>
			<span>{{event.orig.ort_kurzbz}}</span>
		</div>
	</fhc-calendar>
	`
});
app.config.unwrapInjectedRef = true;
app.use(Phrasen);
app.mount('#content');