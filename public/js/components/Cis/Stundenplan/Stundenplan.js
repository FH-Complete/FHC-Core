import FhcCalendar from "../../Calendar/Calendar.js";
import CalendarDate from "../../../composables/CalendarDate.js";
import LvModal from "../Mylv/LvModal.js";
import LvInfo from "../Mylv/LvInfo.js"
import LvMenu from "../Mylv/LvMenu.js"

export const DEFAULT_MODE_STUNDENPLAN = 'Week'

const Stundenplan = {
	name: 'Stundenplan',
	data() {
		return {
			events: null,
			calendarMode: DEFAULT_MODE_STUNDENPLAN,
			calendarDate: new CalendarDate(new Date()),
			eventCalendarDate: new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			currentDay: this.propsViewData?.focus_date ? new Date(this.propsViewData.focus_date) : new Date(),
			minimized: false,
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null
		}
	},
	props: {
		propsViewData: Object
	},
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
		},
		// forward/backward on history entries happening in stundenplan
		'propsViewData.lv_id'(newVal) {
			// relevant if lv_id can be changed from within this component
		},
		'propsViewData.mode'(newVal) {
			if(this.$refs.calendar) this.$refs.calendar.setMode(newVal)
		},
		'propsViewData.focus_date'(newVal) {
			this.currentDate = new Date(newVal)
		}
	},
	components: {
		FhcCalendar, LvModal, LvMenu, LvInfo
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
			const date = day.getFullYear() + "-" +
				String(day.getMonth() + 1).padStart(2, "0") + "-" +
				String(day.getDate()).padStart(2, "0");
			
			this.$router.push({
				name: "Stundenplan",
				params: {
					mode: this.calendarMode,
					focus_date: date,
					lv_id: this.propsViewData?.lv_id || null
				}
			})
			
			this.currentDay = day;
		},
		handleOffset: function(offset)  {
			this.currentDay = new Date(
				this.currentDay.getFullYear() + offset.y, 
				this.currentDay.getMonth() + offset.m,
				this.currentDay.getDate() + offset.d
			)

			const date = this.currentDay.getFullYear() + "-" +
				String(this.currentDay.getMonth() + 1).padStart(2, "0") + "-" +
				String(this.currentDay.getDate()).padStart(2, "0");

			this.$router.push({
				name: "Stundenplan",
				params: {
					mode: this.calendarMode,
					focus_date: date,
					lv_id: this.propsViewData?.lv_id || null
				}
			})
		},
		handleChangeMode(mode) {
			const modeCapitalized = mode.charAt(0).toUpperCase() + mode.slice(1)

			this.$router.push({
				name: "Stundenplan",
				params: {
					mode: modeCapitalized,
					focus_date: this.currentDay.toISOString().split("T")[0],
					lv_id: this.propsViewData?.lv_id ?? null
				}
			})
		
			this.calendarMode = mode
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
				this.$fhcApi.factory.stundenplan.getStundenplan(this.monthFirstDay, this.monthLastDay, this.propsViewData.lv_id),
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
	<fhc-calendar 
		ref="calendar"
		@selectedEvent="setSelectedEvent"
		:initial-date="currentDay"
		@change:range="updateRange"
		@change:offset="handleOffset"
		:events="events"
		:initial-mode="propsViewData.mode"
		show-weeks
		@select:day="selectDay"
		@change:mode="handleChangeMode"
		v-model:minimized="minimized"
	>     
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
			<span class="fhc-entry" >
				{{event.topic}}
			</span>
		</template>
		<template #weekPage="{event,day}">
			<div @click="showModal(event?.orig); " type="button"
			class=" position-relative border border-secondary border d-flex flex-col align-items-center
			justify-content-evenly h-100" style="overflow: auto;">

				<div v-if="event?.orig?.beginn && event?.orig?.ende" class="d-none d-xl-block" >
					<div class="d-flex flex-column p-4 p-xl-2 border-end border-secondary">
						<span class="small">{{convertTime(event.orig.beginn.split(":"))}}</span>
						<span class="small">{{convertTime(event.orig.ende.split(":"))}}</span>
					</div>
				</div>
				<div class="d-flex flex-column flex-grow-1 align-items-center" style="font-size: 0.75rem">
					<span>{{event?.orig.topic}}</span>
					<span v-for="lektor in event?.orig.lektor">{{lektor.kurzbz}}</span>
					<span>{{event?.orig.ort_kurzbz}}</span>
				</div>
			</div>
		</template>
		<template #dayPage="{event,day,mobile}">
			<div @click="mobile? showModal(event?.orig):null" type="button" class="fhc-entry border border-secondary border row m-0 h-100 justify-content-center align-items-center text-center">
				<div class="col-auto" v-if="event?.orig?.beginn && event?.orig?.ende" >
					<div class="d-flex flex-column p-4 border-end border-secondary">
						<span class="small">{{convertTime(event.orig.beginn.split(":"))}}</span>
						<span class="small">{{convertTime(event.orig.ende.split(":"))}}</span>
					</div>
				</div>
				<div class="col ">
					<p>{{ $p.t('lehre/lehrveranstaltung') }}:</p>
					<p class="m-0">{{event?.orig.topic}}</p>
				</div>
				<div class="col ">
					<p>{{ $p.t('lehre/lektor') }}:</p>
					<p class="m-0" v-for="lektor in event?.orig.lektor">{{lektor.kurzbz}}</p>
				</div>
				<div class="col ">
					<p>{{ $p.t('profil/Ort') }}: </p>
					<p class="m-0">{{event?.orig.ort_kurzbz}}</p>
				</div>
			</div>
		</template>
		<template #pageMobilContent="{lvMenu}">
			<h3 >{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
			<div class="w-100">
				<lv-info  :event="currentlySelectedEvent" />
			</div>
			<h3 >{{$p.t('lehre','lehrveranstaltungsmenue')}}</h3>
			<lv-menu :containerStyles="['p-0']" :rowStyles="['m-0']" v-show="lvMenu" :menu="lvMenu" />
		</template>
		<template #pageMobilContentEmpty >
			<h3>{{ $p.t('lehre/noLvFound') }}</h3>
		</template>
	</fhc-calendar>`
}

export default Stundenplan