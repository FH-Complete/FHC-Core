import Phrasen from '../../mixins/Phrasen.js';
import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Calendar.js';
import LvModal from '../Cis/Mylv/LvModal.js';
import ContentModal from '../Cis/Cms/ContentModal.js'
import CalendarDate from '../../composables/CalendarDate.js';
import moodleSvg from "../../helpers/moodleSVG.js"

import ApiLvPlan from '../../api/factory/lvPlan.js';
import ApiOrt from '../../api/factory/ort.js';

export default {
	name: "LvPlanWidget",
	mixins: [
		Phrasen,
		AbstractWidget
	],
	components: {
		FhcCalendar,
		LvModal,
		ContentModal,
		moodleSvg
	},
	
	data() {
		return {
			stunden: [],
			minimized: true,
			events: null,
			currentDay: this.getCurrentDate(),
			calendarDate: new CalendarDate(new Date()),
			roomInfoContentID: null,
			ort_kurzbz: null,
			selectedEvent: null,
		}
	},
	computed: {
		allEventsGrouped() {
			// groups all events of the next 7 days together
			const currentCalendarDate = new CalendarDate(this.currentDay)
			const mapArr = currentCalendarDate.nextSevenDays.map((d) => [new CalendarDate(d), []])

			// return map of key => calendar date of next 7 days & values the respective events on that date
			return new Map((this.events || []).filter(evt => evt.start >= this.currentDay).reduce((acc, cur) => {
				const date = new CalendarDate(new Date(cur.datum))
				const arr = acc.find(el => el[0].compare(date))
				if(!arr) return acc
				arr[1].push(cur)
				
				return acc
			}, mapArr))
		},
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
			return this.calendarDateToString(this.calendarDate.cdLastDayOfNextCalendarMonth);
		},
	},
	methods: {
		getEventStyle: function(evt) {
			const styles = {'background-color': evt.color};
			if(evt.start.getTime() < Date.now()) styles.opacity = 0.5;

			return styles;
		},
		getCurrentDate: function() {
			const today = new Date()
			today.setHours(0,0,0)
			return today
		},
		calendarDateToString: function (calendarDate) {

			return calendarDate instanceof CalendarDate ?
				[calendarDate.y, calendarDate.m + 1, calendarDate.d].join('-') :
				null;

		},
		showRoomInfoModal: function(ort_kurzbz){
			// getting the content_id of the ort_kurzbz
			this.$api
				.call(ApiOrt.getContentID(ort_kurzbz))
				.then(res => {
					this.roomInfoContentID = res.data;
					this.ort_kurzbz = ort_kurzbz;

					// only showing the modal after vue was able to set the reactive data
					Vue.nextTick(() => { this.$refs.contentModal.show(); });
				})
				.catch(err => {
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
				this.$api.call(ApiLvPlan.LvPlanEvents(this.monthFirstDay, this.monthLastDay)),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(this.monthFirstDay, this.monthLastDay))
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

		setCalendarMaximized() {
			this.minimized = false
		}
	

	},
	created() {
		this.$emit('setConfig', false);
		this.loadEvents();
	},
	template: /*html*/`
	<div class="dashboard-widget-lvplan d-flex flex-column h-100">
		<lv-modal v-if="selectedEvent" ref="lvmodal" :event="selectedEvent"  />
		<content-modal :content_id="roomInfoContentID" dialogClass="modal-lg" ref="contentModal"/>
		<fhc-calendar @change:range="updateRange" :initial-date="currentDay" class="border-0" class-header="p-0" @select:day="selectDay" :widget="true" v-model:minimized="minimized" :events="events" no-week-view :show-weeks="false" >
			<template #monthPage="{event,day}">
				<div  v-if="event.type=='moodle'">
					<div class="d-flex small w-100" >
						<moodle-svg></moodle-svg>
						<span v-contrast class="flex-grow-1 text-center "><strong v-html="event.titel"></strong> - {{event.topic}}</span>
					</div>
				</div>
				<span v-else class="small" >
					{{event.topic}}
				</span>
			</template>
			<template #minimizedPage >
				<div class="flex-grow-1" style="overflow-y: auto; overflow-x: hidden">
					<div v-if="events === null" class="d-flex h-100 justify-content-center align-items-center">
						<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
					</div>
					<template v-else-if="allEventsGrouped.size" v-for="([key, value], index) in allEventsGrouped" :key="index" style="margin-top: 8px;">
						<div class=" card-header d-grid p-0">
							<button class="btn fhc-tertiary text-decoration-none" @click="setCalendarMaximized">{{ key.format({dateStyle: "full"}, $p.user_locale.value)}}</button>
						</div>
						<div role="button" @click="showLvUebersicht(evt)" v-for="evt in value" :key="evt.id" class="list-group-item small" :style="getEventStyle(evt)">
							<template v-if="evt.type=='moodle'">
								<div class="d-flex align-items-center ">
									<moodle-svg></moodle-svg>
									<b v-contrast class="flex-grow-1 text-center"><strong v-html="evt.titel"></strong> - {{evt.topic}}</b>
								</div>
							</template>
							<template v-else>
								<b>{{evt.topic}}</b>
								<br>
								<small v-if="evt.ort_kurzbz" class="d-flex w-100 justify-content-between">
									<!-- event modifier stop to prevent opening the modal for the lv Uebersicht when clicking on the ort_kurzbz -->
									<span @click.stop="showRoomInfoModal(evt.ort_kurzbz)" style="text-decoration:underline" type="button">{{evt.ort_kurzbz}}</span>
									<span>{{evt.start.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}-{{evt.end.toLocaleTimeString(undefined, {hour:'numeric',minute:'numeric'})}}</span>
								</small>
							</template>
							
						</div>
						<div v-if="!value.length" class="list-group-item small text-center">
							{{ $p.t('lehre/noLvFound') }}
						</div>
					</template>
					<div v-else class="d-flex h-100 justify-content-center align-items-center fst-italic text-center">
						{{ $p.t('lehre/noLvFound') }}
					</div>
				</div>
			</template>
		</fhc-calendar>
	</div>
`
}