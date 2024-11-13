import Phrasen from '../../mixins/Phrasen.js';
import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Calendar.js';
import LvModal from '../Cis/Mylv/LvModal.js';
import ContentModal from '../Cis/Cms/ContentModal.js'
import CalendarDate from '../../composables/CalendarDate.js';

export default {
	mixins: [
		Phrasen,
		AbstractWidget
	],
	components: {
		FhcCalendar,
		LvModal,
		ContentModal,
	},
	
	data() {
		return {
			stunden: [],
			minimized: true,
			events: null,
			currentDay: new Date(),
			calendarDate: new CalendarDate(new Date()),
			roomInfoContentID: null,
			ort_kurzbz: null,
			selectedEvent: null,
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
		},
		monthFirstDay: function () {
			return this.calendarDateToString(this.calendarDate.cdFirstDayOfCalendarMonth);
		},
		monthLastDay: function () {
			return this.calendarDateToString(this.calendarDate.cdLastDayOfCalendarMonth);
		},
	},
	methods: {
		calendarDateToString: function (calendarDate) {

			return calendarDate instanceof CalendarDate ?
				[calendarDate.y, calendarDate.m + 1, calendarDate.d].join('-') :
				null;

		},
		showRoomInfoModal: function(ort_kurzbz){
			// getting the content_id of the ort_kurzbz
			this.$fhcApi.factory.ort.getContentID(ort_kurzbz).then(res =>{
				this.roomInfoContentID = res.data;
				this.ort_kurzbz = ort_kurzbz;

				// only showing the modal after vue was able to set the reactive data
				Vue.nextTick(()=>{this.$refs.contentModal.show();});
				
				
			}).catch(err =>{
				console.err(err);
				this.ort_kurzbz = null;
				this.roomInfoContentID = null;
			});
			
		},
		showLvUebersicht: function (event){
			this.selectedEvent= event;
			Vue.nextTick(()=>{
				this.$refs.lvmodal.show();
			});
		},
		
		selectDay(day) {
			this.currentDay = day;
			this.minimized = true;
		},

		updateRange: function (data) {
			
			let tmp_date = new CalendarDate(data.start);
			// only load month data if the month or year has changed
			if (tmp_date.m != this.calendarDate.m || tmp_date.y != this.calendarDate.y) {
				this.calendarDate = tmp_date;
				Vue.nextTick(() => {
					this.loadEvents();
				});
			}
		},
		

		loadEvents: function () {
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
	created() {
		this.$emit('setConfig', false);
		this.loadEvents();
		/* axios
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
			.catch(err => { console.error('ERROR: ', err.response.data) }); */
	},
	template: /*html*/`
	<div class="dashboard-widget-stundenplan d-flex flex-column h-100">
		<lv-modal v-if="selectedEvent" ref="lvmodal" :event="selectedEvent"  />
		<content-modal :contentID="roomInfoContentID" :ort_kurzbz="" dialogClass="modal-lg" ref="contentModal"/>
		<fhc-calendar @change:range="updateRange" :initial-date="currentDay" class="border-0" class-header="p-0" @select:day="selectDay" v-model:minimized="minimized" :events="events" no-week-view :show-weeks="false" >
			<template #minimizedPage >
				<div class="flex-grow-1 overflow-scroll">
					<div v-if="events === null" class="d-flex h-100 justify-content-center align-items-center">
						<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
					</div>
					<div v-else-if="currentEvents.length" class="list-group list-group-flush">
						<div role="button" @click="showLvUebersicht(evt)" class="" v-for="evt in currentEvents" :key="evt.id" class="list-group-item small" :style="{'background-color':evt.color}">
							<b>{{evt.topic}}</b>
							<br>
							<small class="d-flex w-100 justify-content-between">
								<!-- event modifier stop to prevent opening the modal for the lv Uebersicht when clicking on the ort_kurzbz -->
								<span @click.stop="showRoomInfoModal(evt.ort_kurzbz)" style="text-decoration:underline" type="button">{{evt.ort_kurzbz}}</span>
								<span>{{evt.start.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}-{{evt.end.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}</span>
							</small>
						</div>
					</div>
					<div v-else class="d-flex h-100 justify-content-center align-items-center fst-italic text-center">
						{{ p.t('lehre/noLvFound') }}
					</div>
				</div>
			</template>
		</fhc-calendar>
		
	</div>`
}