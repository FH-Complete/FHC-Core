import FhcCalendar from "../../Calendar/Calendar.js";
import CalendarDate from "../../../composables/CalendarDate.js";
import LvModal from "../Mylv/LvModal.js";
import LvMenu from "../Mylv/LvMenu.js"
import ApiLvPlan from '../../../api/factory/lvPlan.js';
import ApiAuthinfo from '../../../api/factory/authinfo.js';

export const DEFAULT_MODE_LVPLAN = 'Week'

const LvPlan = {
	name: 'LvPlan',
	data() {
		return {
			events: null,
			calendarMode: this.propsViewData?.mode ?? DEFAULT_MODE_LVPLAN,
			calendarDate: new CalendarDate(new Date()),
			eventCalendarDate: new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			currentDay: this.propsViewData?.focus_date ? new Date(this.propsViewData.focus_date) : new Date(),
			lv: null,
			minimized: false,
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null,
			isModalContentResolved: false,
			isModalTitleResolved: false,
			isShowModal: false,
		}
	},
	props: {
		viewData: Object, // NOTE(chris): this is inherited from router-view
		propsViewData: Object,
		rowMinHeight: {
			type: String,
			default: '100px'
		},
		eventMaxHeight: {
			type: String,
			default: '125px'
		}
	},
	provide() {
		return {
			rowMinHeight: this.rowMinHeight,
			eventMaxHeight: this.eventMaxHeight
		}	
	},
	inject:["renderers"],
	watch: {
		/* events:{
			handler: function(newValue){
				if(newValue == null)
					setTimeout(()=>{
						if(this.events == null){
							this.loadEvents();
						}
					},500);
			},
			immediate: true,
		}, */
		modalLoaded:{
			handler: function (newValue) {
				if (this.isShowModal && newValue.isModalContentResolved && newValue.isModalTitleResolved) {
					this.$nextTick(() => {
						if(this.$refs.lvmodal) this.$refs.lvmodal.show();
						this.isShowModal = false;
					});
				}
			}, 
			immediate: true
		},
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
		// forward/backward on history entries happening in lvplan
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
		FhcCalendar, LvModal, LvMenu
	},
	computed:{
		modalLoaded: function(){
			return { isModalContentResolved: this.isModalContentResolved, isModalTitleResolved:this.isModalTitleResolved};
		},
		
		downloadLinks: function(){
			if(!this.studiensemester_start || !this.studiensemester_ende || !this.uid )return;
			let start = new Date(this.studiensemester_start);
			start = Math.floor(start.getTime()/1000);
			let ende = new Date(this.studiensemester_ende);
			ende = Math.floor(ende.getTime() / 1000);

			let download_link = 
				(format, version = "", target = "") => 
					`${FHC_JS_DATA_STORAGE_OBJECT.app_root}cis/private/lvplan/stpl_kalender.php?type=student&pers_uid=
					${this.uid}&begin=${start}&ende=${ende}&format=${format}
					${version ? '&version=' + version : ''}${target ? '&target=' + target : ''}`;
			return [
				{ title: "excel", icon: 'fa-solid fa-file-excel', link: download_link('excel') },
				{ title: "csv", icon: 'fa-solid fa-file-csv', link: download_link('csv') },
				{ title: "ical1", icon: 'fa-regular fa-calendar', link: download_link('ical', '1', 'ical') },
				{ title: "ical2", icon: 'fa-regular fa-calendar', link: download_link('ical', '2', 'ical') }
			];
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
		modalTitleResolved: function () {
			this.isModalTitleResolved = true;
			
		},
		modalContentResolved: function () {
			this.isModalContentResolved = true;
			
		},
		// component renderers fetches from different addons
		modalTitleComponent(type){
			return this.renderers[type]?.modalTitle;
		},
		modalContentComponent(type) {
			return this.renderers[type]?.modalContent;
		},
		calendarEventComponent(type){
			return this.renderers[type]?.calendarEvent;
		},
		

		fetchStudiensemesterDetails: async function (date) {
			return this.$api.call(ApiLvPlan.studiensemesterDateInterval(date));
		},
		
		setSelectedEvent: function (event) {
			this.currentlySelectedEvent = event;
		},
		selectDay: function(day){
			const date = day.getFullYear() + "-" +
				String(day.getMonth() + 1).padStart(2, "0") + "-" +
				String(day.getDate()).padStart(2, "0");
			const capitalizedMode = this.calendarMode[0].toUpperCase() + this.calendarMode.slice(1);

			this.$router.push({
				name: "LvPlan",
				params: {
					mode: capitalizedMode,
					focus_date: date,
					lv_id: this.propsViewData?.lv_id || null
				}
			})
			
			this.currentDay = day;
		},
		handleChangeMode(mode) {
			let m = mode[0].toUpperCase() + mode.slice(1)
			if(m === this.calendarMode) return; // TODO(chris): check for date and lv_id too!
			const date = this.currentDay.getFullYear() + "-" +
				String(this.currentDay.getMonth() + 1).padStart(2, "0") + "-" +
				String(this.currentDay.getDate()).padStart(2, "0");
			
			if (m == 'Weeks' || m == 'Years' || m == 'Months') return;
			
			this.$router.push({
				name: "LvPlan",
				params: {
					mode: m,
					focus_date: date,
					lv_id: this.propsViewData?.lv_id ?? null
				}
			})
			this.calendarMode = m
		},
		showModal: function(event){
			this.currentlySelectedEvent = event;
			Vue.nextTick(()=>{
				if(this.isModalContentResolved && this.isModalTitleResolved){
					if(this.$refs.lvmodal) this.$refs.lvmodal.show();
				} 
				else{
					this.isShowModal = true;
				}
			})
		},
		updateRange: function ({start,end, mounted}) {
			let checkDate = (date) => {
				return date.m != this.eventCalendarDate.m || date.y != this.eventCalendarDate.y;
			}
			this.calendarDate = new CalendarDate(end);

			// only load month data if the month or year has changed
			// or we receive a reload flag from the mounted routine of the components
			// or this handler is being called from the mounted lifecycle of a component
			if (mounted || (checkDate(new CalendarDate(start)) && checkDate(new CalendarDate(end)))){
				// reset the events before querying the new events to activate the loading spinner
				this.events = null;
				this.eventCalendarDate = new CalendarDate(end);
				this.loadEvents();
			}
		},
		calendarDateToString: function (calendarDate) {
			return calendarDate instanceof CalendarDate ?
				[calendarDate.y, calendarDate.m + 1, calendarDate.d].join('-') :
				null;

		},
		loadEvents: function(){
			Promise.allSettled([
				this.$api.call(ApiLvPlan.LvPlanEvents(this.monthFirstDay, this.monthLastDay, this.propsViewData.lv_id)),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(this.monthFirstDay, this.monthLastDay))
			]).then((result) => {
				let promise_events = [];
				result.forEach((promise_result) => {
					if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success") {
						
						if(promise_result.value.meta?.lv) this.lv = promise_result.value.meta.lv
						
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
		

		this.$api
			.call(ApiAuthinfo.getAuthUID())
			.then(res => res.data)
			.then(data => {
				this.uid = data.uid;
			});
		
		// this.loadEvents();
	},
	beforeUnmount() {
		if(this.$refs.lvmodal) this.$refs.lvmodal.hide()	
	},
	template:/*html*/`
	<template v-if="renderers">
	<h2>
		{{$p.t('lehre/stundenplan')}}
		<span style="padding-left: 0.4em;" v-show="studiensemester_kurzbz">{{studiensemester_kurzbz}}</span>
		<span style="padding-left: 0.5em;" v-show="propsViewData?.lv_id && lv"> {{ $p.user_language.value === 'German' ? lv?.bezeichnung : lv?.bezeichnung_english}}</span>
	</h2>
	<hr>
	<lv-modal v-if="currentlySelectedEvent" :event="currentlySelectedEvent" ref="lvmodal" >
		<template #modalTitle>
		<Suspense @pending="isModalTitleResolved=false" @resolve="modalTitleResolved">
			<component :is="modalTitleComponent(currentlySelectedEvent.type)" v-if="currentlySelectedEvent" :event="currentlySelectedEvent" ></component>
		</Suspense>
		</template>
		<template #modalContent>
		<Suspense @pending="isModalContentResolved=false" @resolve="modalContentResolved">
			<component :is="modalContentComponent(currentlySelectedEvent.type)" v-if="currentlySelectedEvent" :event="currentlySelectedEvent" ></component>
		</Suspense>
		</template>
	</lv-modal>

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
				<a :href="link" :aria-label="title" class="py-1 px-2 m-1 btn btn-outline-secondary card">
					<div class="d-flex flex-column">
						<i aria-hidden="true" :class="icon"></i>
						<span class="small">{{title}}</span>
					</div>
				</a>
			</div>
		</template>
		<template #monthPage="{event,day}">
			<div @click="showModal(event)" class="monthPageContainer " >
				<component :is="calendarEventComponent(event.type)" :event="event" ></component>
			</div>
		</template>
		<template #weekPage="{event,day}">
			<div @click="showModal(event)" type = "button"
				class="weekPageContainer position-relative h-100"
				:class="{'p-1':event.allDayEvent}"
				style = "overflow: auto;" >
				<component :is="calendarEventComponent(event.type)" :event="event" ></component>
			</div>
		</template>
		<template #dayPage="{event,day,mobile}">
			<div @click="mobile? showModal(event):null" type="button" class="dayPageContainer fhc-entry m-0 h-100  text-center">
				<component :is="calendarEventComponent(event.type)" :event="event"></component>
			</div>
		</template>
		<template #pageMobilContent="{lvMenu, event}">
			<component :is="modalContentComponent(currentlySelectedEvent.type)" v-if="event" :event="event" :lvMenu="lvMenu" ></component>
		</template>
		<template #pageMobilContentEmpty >
			<h3>{{ $p.t('lehre/noLvFound') }}</h3>
		</template>
	</fhc-calendar>
	</template>`
}

export default LvPlan;
