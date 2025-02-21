import FhcCalendar from "../../Calendar/Calendar.js";
import CalendarDate from "../../../composables/CalendarDate.js";
import LvModal from "../Mylv/LvModal.js";
import LvInfo from "../Mylv/LvInfo.js"
import LvMenu from "../Mylv/LvMenu.js"
import moodleSvg from "../../../helpers/moodleSVG.js"

export const Stundenplan = {
	name: 'Stundenplan',
	data() {
		return {
			events: null,
			calendarDate: new CalendarDate(new Date()),
			eventCalendarDate: new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			currentDay: new Date(),
			minimized: false,
			studiensemester_kurzbz:null,
			studiensemester_start:null,
			studiensemester_ende:null,
			uid:null,
		}
	},
	props: [
		"viewData",
	],
	watch: {
		weekFirstDay: {
			handler: async function (newValue) {
				let data = await this.fetchStudiensemesterDetails(newValue);
				let { studiensemester_kurzbz, start, ende } = data.data;
				this.studiensemester_kurzbz = studiensemester_kurzbz;
				this.studiensemester_start = start;
				this.studiensemester_ende = ende;
			},
			immediate: true,
		}
	},
	components: {
		FhcCalendar, LvModal, LvMenu, LvInfo, moodleSvg
	},
	computed:{
		downloadLinks: function(){
			if(!this.studiensemester_start || !this.studiensemester_ende || !this.uid )return;
			let start = new Date(this.studiensemester_start);
			start = Math.floor(start.getTime()/1000);
			let ende = new Date(this.studiensemester_ende);
			ende = Math.floor(ende.getTime() / 1000);

			let download_link = (format, version = "", target = "") => `${FHC_JS_DATA_STORAGE_OBJECT.app_root}cis/private/lvplan/stpl_kalender.php?type=student&pers_uid=${this.uid}&begin=${start}&ende=${ende}&format=${format}${version ? '&version=' + version : ''}${target ? '&target=' + target : ''}`;
			return [{ title: "excel", icon: 'fa-solid fa-file-excel', link: download_link('excel') }, { title: "csv", icon: 'fa-solid fa-file-csv', link: download_link('csv') }, { title: "ical1", icon: 'fa-regular fa-calendar', link: download_link('ical', '1', 'ical') }, { title: "ical2", icon: 'fa-regular fa-calendar', link: download_link('ical', '2', 'ical') }];
		},
		lv_id() { // computed so we can theoretically change path/lva selection and reload without page refresh
			const pathParts = window.location.pathname.split('/').filter(Boolean);
			const id = pathParts[pathParts.length - 1];
			return id && !isNaN(Number(id)) ? id : null; // only return id if it is a number string since the path might contain invalid elements
		},
		weekFirstDay: function () {
			return this.calendarDateToString(this.calendarDate.cdFirstDayOfWeek);
		},
		weekLastDay: function () {
			return this.calendarDateToString(this.calendarDate.cdLastDayOfWeek);
		},
		monthFirstDay: function () {
			return this.calendarDateToString(this.eventCalendarDate.cdFirstDayOfCalendarMonth);
		},
		monthLastDay: function () {
			return this.calendarDateToString(this.eventCalendarDate.cdLastDayOfCalendarMonth);
		},

	},
	methods:{
		fetchStudiensemesterDetails: async function (date) {
			return this.$fhcApi.factory.stundenplan.studiensemesterDateInterval(date);
		},
		convertTime: function([hour,minute]){
			let date = new Date();
			date.setHours(hour);
			date.setMinutes(minute);
			// returns date string as hh:mm
			return date.toLocaleTimeString(this.$p.user_locale, { hour: '2-digit', minute: '2-digit', hour12:false}); 

		},
		setSelectedEvent: function (event) {
			this.currentlySelectedEvent = event;
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
				return date.m != this.eventCalendarDate.m || date.y != this.eventCalendarDate.y;
			}
			this.calendarDate = new CalendarDate(end);

			// only load month data if the month or year has changed
			if (checkDate(new CalendarDate(start)) && checkDate(new CalendarDate(end))){
				// reset the events before querying the new events to activate the loading spinner
				this.events = null;
				this.eventCalendarDate = new CalendarDate(end);
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
				this.$fhcApi.factory.stundenplan.getStundenplan(this.monthFirstDay, this.monthLastDay, this.viewData.lv_id),
				this.$fhcApi.factory.stundenplan.getStundenplanReservierungen(this.monthFirstDay, this.monthLastDay),
				this.loadMoodleEvents(this.monthFirstDay, this.monthLastDay)
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
		loadMoodleEvents: function(start_date, end_date){
			
			let date_start = Math.floor(new Date(start_date).getTime() / 1000);
			let date_end = Math.floor(new Date(end_date).getTime() / 1000);
			return this.$fhcApi.factory.stundenplan.getMoodleEventsByUserid('io23m005', date_start, date_end).then((response) => response.events).then(events => {
				let data =events.map(event =>{
					const event_start_date = new Date(Number(event.timestart) * 1000);
					const event_end_date = new Date(((Number(event.timestart) + Number(event.timeduration)) * 1000));
					const formatted_date = `${event_start_date.getFullYear()}-${event_start_date.getMonth()+1}-${event_start_date.getDate()}`;
					// to get the same date and time as in moodle, we use the default UTC time zone 
					const formatted_start_time = event_start_date.toLocaleTimeString(this.$p.user_locale, {hour:'2-digit',minute:'2-digit', second:'2-digit',hour12:false, timeZone:'UTC'});
					const formatted_end_time = event_end_date.toLocaleTimeString(this.$p.user_locale, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: 'UTC' });
					
					return {
						type:'moodle',
						beginn: formatted_start_time,
						ende: formatted_end_time,
						allDayEvent: true,
						datum: formatted_date,
						purpose: event.purpose,
						assignment: event.activityname,
						topic: event.activitystr,
						lektor:[],
						gruppe:[],
						ort_kurzbz: event.location,
						//moodle idnumber entspricht der course id number die man den Kurs in Moodle vergeben kann
						lehreinheit_id:event.lehreinheitsNummber??null,
						titel: event.course.fullname,
						lehrfach:'',
						lehrform:'',
						lehrfach_bez:'',
						organisationseinheit:'',
						farbe:'00689E',
						lehrveranstaltung_id:0,
						ort_content_id:0,
					}
				});
				return {
					data: data,
					meta: { status: 'success' }
				};
			})
			
		},
	},
	created()
	{
		
		this.$fhcApi.factory.authinfo.getAuthUID().then((res) => res.data)
		.then(data=>{
			this.uid = data.uid;
		})
		this.loadEvents();
	},
	beforeUnmount() {
		if(this.$refs.lvmodal) this.$refs.lvmodal.hide()	
	},
	template:/*html*/`
	<h2>{{$p.t('lehre/stundenplan')}}</h2>
	<hr>
	<lv-modal v-if="currentlySelectedEvent" :event="currentlySelectedEvent" ref="lvmodal" />
	<fhc-calendar @selectedEvent="setSelectedEvent" :initial-date="currentDay" @change:range="updateRange" :events="events" initial-mode="week" show-weeks @select:day="selectDay" v-model:minimized="minimized">
		<template #calendarDownloads>
			<div v-for="{title,icon,link} in downloadLinks">
				<a :href="link" :title="title" class="py-1 px-2 m-1 btn btn-outline-secondary">
					<div class="d-flex flex-column">
						<i :class="icon"></i>
						<span class="small">{{title}}</span>
					</div>
				</a>
			</div>
		</template>
		<template #monthPage="{event,day}">
			<div class="p-1" v-if="event.type=='moodle'">
				<div class="d-flex small w-100" >
					<moodle-svg></moodle-svg>
					<span class="flex-grow-1 text-center ">{{event.topic}}</span>
				</div>
			</div>
			<div class="p-1" v-else>
				<span>{{event.topic}}</span>
			</div>
		</template>
		<template #weekPage="{event,day}">
			<div @click="showModal(event); " type="button"
			class=" position-relative border border-secondary border d-flex flex-col align-items-center justify-content-evenly h-100"
			:class="{'p-1':event.allDayEvent}"
			style="overflow: auto;">
				<div v-if="!event.allDayEvent && event?.beginn && event?.ende" class="d-none d-xl-block" >
					<div class="d-flex flex-column p-4 p-xl-2 border-end border-secondary">
						<span class="small">{{convertTime(event.beginn.split(":"))}}</span>
						<span class="small">{{convertTime(event.ende.split(":"))}}</span>
					</div>
				</div>
				<div v-if="event.type=='moodle'" class="d-flex small w-100" >
					<moodle-svg></moodle-svg>
					<span class="flex-grow-1 text-center">{{event.topic}}</span>
				</div>
				<div v-else class="d-flex flex-column flex-grow-1 align-items-center small">
					<span>{{event.topic}}</span>
					<span v-for="lektor in event.lektor">{{lektor.kurzbz}}</span>
					<span>{{event.ort_kurzbz}}</span>
				</div>
			</div>
		</template>
		<template #dayPage="{event,day,mobile}">
			<div @click="mobile? showModal(event):null" type="button" class="fhc-entry border border-secondary border m-0 h-100  text-center">
				<template v-if="event.type=='moodle'">
					<div class="d-flex small align-items-center w-100 p-1" >
						<moodle-svg></moodle-svg>
						<span class="flex-grow-1 text-center">{{event.topic}}</span>
					</div>
				</template>
				<template v-else>
					<div class="row justify-content-center align-items-center">
						<div class="col-auto" v-if="!event.allDayEvent && event?.beginn && event?.ende" >
							<div class="d-flex flex-column p-4 border-end border-secondary">
								<span class="small">{{convertTime(event.beginn.split(":"))}}</span>
								<span class="small">{{convertTime(event.ende.split(":"))}}</span>
							</div>
						</div>
						<div class="col ">
							<p>Lehrveranstaltung:</p>
							<p class="m-0">{{event?.topic}}</p>
						</div>
						<div class="col ">
							<p>Lektor:</p>
							<p class="m-0" v-for="lektor in event?.lektor">{{lektor.kurzbz}}</p>
						</div>
						<div class="col ">
							<p>Ort: </p>
							<p class="m-0">{{event?.ort_kurzbz}}</p>
						</div>
					</div>
				</template>
			</div>
		</template>
		<template #pageMobilContent="{lvMenu, event}">
			<h3 >{{event.type=='moodle'?$p.t('lvinfo','Moodleinformationen'):$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
			<div class="w-100">
				<lv-info  :event="event" />
			</div>
			<template v-if="event.type != 'moodle'">
				<h3 >Lehrveranstaltungs Menu</h3>
				<lv-menu :containerStyles="['p-0']" :rowStyles="['m-0']" v-show="lvMenu" :menu="lvMenu" />
			</template>
		</template>
	</fhc-calendar>
	`
}

export default Stundenplan