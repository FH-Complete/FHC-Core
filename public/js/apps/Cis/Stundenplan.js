import FhcCalendar from "../../components/Calendar/Calendar.js";
import Phrasen from "../../plugin/Phrasen.js";
import CalendarDate from "../../composables/CalendarDate.js";
import LvModal from "../../components/Cis/Mylv/LvModal.js";


const app = Vue.createApp({
	name: 'StundenplanApp',
	data() {
		return {
			lv_id: null,
			events: null,
			calendarDate: new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			currentDay: new Date(),
			minimized: false,
		}
	},
	components: {
		FhcCalendar, LvModal
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
		getLvID: function () {
			this.lv_id = window.location.pathname
		},
		selectDay: function(day){
			this.currentDay = day;
		},
		showModal: function(event){
			this.currentlySelectedEvent = event;
			Vue.nextTick(() => {
				this.$refs.lvmodal.show();
			});
		},
		updateRange: function ({start,end}) {

			let checkDate = (date) => {
				return date.m != this.calendarDate.m || date.y != this.calendarDate.y;
			}

			// only load month data if the month or year has changed
			if (checkDate(new CalendarDate(start)) && checkDate(new CalendarDate(end))){
				// reset the events before querying the new events to activate the loading spinner
				this.events = null;
				this.calendarDate = new CalendarDate(end);
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
	created()
	{
		this.loadEvents();
		this.getLvID();
	},
	template:/*html*/`
	<h2>{{$p.t('lehre/stundenplan')}}</h2>
	<hr>
	<lv-modal v-if="currentlySelectedEvent" :event="currentlySelectedEvent" ref="lvmodal" />
	<fhc-calendar :initial-date="currentDay" @change:range="updateRange" :events="events" initial-mode="week" show-weeks @select:day="selectDay" v-model:minimized="minimized">
		<template #monthPage="{event,day,isSelected}">
			<span class="fhc-entry" :class="{'selectedEvent':isSelected}" style="color:white" :style="{'background-color': event.color}">
				{{event.topic}}
			</span>
		</template>
		<template #weekPage="{event,day,isSelected}">
			<div @click="showModal(event?.orig)" type="button" :class="{'selectedEvent':isSelected}" class="fhc-entry border border-secondary border d-flex flex-column align-items-center justify-content-evenly h-100">
				<span>{{event?.orig.topic}}</span>
				<span v-for="lektor in event?.orig.lektor">{{lektor.kurzbz}}</span>
				<span>{{event?.orig.ort_kurzbz}}</span>
			</div>
		</template>
		<template #dayPage="{event,day}">
			<div type="button" class="fhc-entry border border-secondary border row h-100 justify-content-center align-items-center text-center">
				<div class="col ">
					<p>Lehrveranstaltung:</p>
					<p class="m-0">{{event?.orig.topic}}</p>
				</div>
				<div class="col ">
					<p>Lektor:</p>
					<p class="m-0" v-for="lektor in event?.orig.lektor">{{lektor.kurzbz}}</p>
				</div>
				<div class="col ">
					<p>Ort: </p>
					<p class="m-0">{{event?.orig.ort_kurzbz}}</p>
				</div>
			</div>
		</template>
	</fhc-calendar>
	`
});
app.use(Phrasen, {reload: true});
app.mount('#content');