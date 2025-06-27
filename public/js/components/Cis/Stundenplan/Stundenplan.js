import FhcCalendar from "../../Calendar/Base.js";
import CalendarViewDay from "../../Calendar/Mode/Day.js";
import CalendarViewWeek from "../../Calendar/Mode/Week.js";
import CalendarViewMonth from "../../Calendar/Mode/Month.js";
import EventEvent from "./Event/Event.js";

import CalendarDate from "../../../helpers/Calendar/Date.js";

import CalendarDateObj from "../../../composables/CalendarDate.js";
import LvModal from "../Mylv/LvModal.js";

export const DEFAULT_MODE_STUNDENPLAN = 'Week'

const Stundenplan = {
	name: 'CisStundenplan',
	components: {
		FhcCalendar,
		LvModal,
		EventEvent
	},
	provide() {
		return {
			rowMinHeight: this.rowMinHeight,
			eventMaxHeight: this.eventMaxHeight
		};
	},
	props: {
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
	data() {
		return {
			calendarViews: {
				week: Vue.markRaw(CalendarViewWeek),
				month: Vue.markRaw(CalendarViewMonth),
				day: Vue.markRaw(CalendarViewDay)
			},
			events: null,
			calendarMode: DEFAULT_MODE_STUNDENPLAN,
			calendarDate: new CalendarDateObj(new Date()),
			eventCalendarDate: new CalendarDateObj(new Date()),
			currentlySelectedEvent: null,
			currentDay: this.propsViewData?.focus_date ? new Date(this.propsViewData.focus_date) : new Date(),
			lv: null,
			minimized: false,
			studiensemester_kurzbz: null,
			studiensemester_start: null,
			studiensemester_ende: null,
			uid: null
		}
	},
	computed:{
		downloadLinks() {
			if (!this.studiensemester_start || !this.studiensemester_ende || !this.uid )
				return;
			let start = new Date(this.studiensemester_start);
			start = Math.floor(start.getTime() / 1000);
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
		'propsViewData.focus_date'(newVal) {
			this.currentDate = new Date(newVal)
		}
	},
	methods:{
		makeRGB(hex) {
			const r = Number('0x' + hex.substr(0, 2));
			const g = Number('0x' + hex.substr(2, 2));
			const b = Number('0x' + hex.substr(4, 2));
			return r + ', ' + g + ', ' + b;
		},
		sendRouterParams(day, mode) {
			// TODO(chris): move into a CalendarDate.format... function
			const focus_date = day.getFullYear() +
				'-' +
				CalendarDate.format(day, { month: '2-digit' }, this.$p.user_locale) +
				'-' +
				CalendarDate.format(day, { day: '2-digit' }, this.$p.user_locale);

			this.$router.push({
				name: "Stundenplan",
				params: {
					mode,
					focus_date,
					lv_id: this.propsViewData?.lv_id || null
				}
			});
		},
		selectDay(day) {
			this.sendRouterParams(day, this.calendarMode);
			this.currentDay = day;
		},
		handleChangeMode(mode) {
			const modeCapitalized = mode.charAt(0).toUpperCase() + mode.slice(1);
			this.sendRouterParams(this.currentDay, modeCapitalized);
			this.calendarMode = modeCapitalized;
		},

		updateRange({ first, last }) {
			// TODO(chris): remove CalendarObj
			const checkDate = date => {
				console.log(date);
				return date.getMonth() + 1 != this.eventCalendarDate.m || date.getFullYear() != this.eventCalendarDate.y;
			}
			this.calendarDate = new CalendarDateObj(last);

			// only load month data if the month or year has changed
			if (checkDate(first) && checkDate(last)){
				// reset the events before querying the new events to activate the loading spinner
				this.events = null;
				this.eventCalendarDate = new CalendarDateObj(last);
				Vue.nextTick(() => {
					this.loadEvents();
				});
			}
		},
		showModal(e, event) {
			this.currentlySelectedEvent = event;
			Vue.nextTick(() => {
				this.$refs.lvmodal.show();
			});
		},

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
		calendarDateToString: function (calendarDate) {
			return calendarDate instanceof CalendarDateObj ?
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

								el.start = new Date(el.isobeginn);
								el.end = new Date(el.isoende);

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
		this.$fhcApi
			.factory.authinfo.getAuthUID()
			.then(data => this.uid = data.data.uid);
		this.loadEvents();
	},
	beforeUnmount() {
		if (this.$refs.lvmodal)
			this.$refs.lvmodal.hide();
	},
	// TODO(chris): update:current-date on next/prev
	template:/*html*/`
	<div class="cis-stundenplan h-100 d-flex flex-column">
		<h2>
			{{ $p.t('lehre/stundenplan') }}
			<span v-show="studiensemester_kurzbz" style="padding-left: 0.4em;">
				{{ studiensemester_kurzbz }}
			</span>
			<span v-show="propsViewData?.lv_id && lv" style="padding-left: 0.5em;">
				{{ $p.user_language.value === 'German' ? lv?.bezeichnung : lv?.bezeichnung_english }}
			</span>
		</h2>
		<hr>
		<lv-modal
			v-if="currentlySelectedEvent"
			ref="lvmodal"
			:event="currentlySelectedEvent"
		/>
		<fhc-calendar 
			ref="calendar"
			:locale="$p.user_locale.value"
			:views="calendarViews"
			:view="propsViewData.mode.toLowerCase()"
			:current-date="currentDay"
			:events="events || []"
			show-btns
			@update:range="updateRange"
			@update:view="handleChangeMode"
			@update:current-date="selectDay"
		>
			<template #actions>
				<div class="d-flex justify-content-center justify-content-md-start align-items-center">
					<div v-for="{ title, icon, link } in downloadLinks">
						<a
							:href="link"
							:title="title"
							class="py-1 px-2 m-1 btn btn-outline-secondary"
						>
							<div class="d-flex flex-column">
								<i :class="icon"></i>
								<span class="small">{{ title }}</span>
							</div>
						</a>
					</div>
				</div>
			</template>
			<template v-slot="{ event, mode }">
				<template v-if="mode == 'month'">
					<div
						class="event-colored"
		 				:style="'--event-color-rgb:' + makeRGB(event.farbe || 'cccccc')"
						@click.stop="showModal($event, event)"
					>
						<span class="fhc-entry">
							{{ event.topic }}
						</span>
					</div>
				</template>
				<template v-if="mode == 'week'">
					<div
						@click="showModal($event, event)"
						type="button"
						class="border border-secondary d-flex justify-content-evenly event-colored h-100 overflow-hidden"
						style="overflow:auto"
		 				:style="'--event-color-rgb:' + makeRGB(event.farbe || 'cccccc')"
					>
						<div
							v-if="event.beginn && event.ende"
							class="align-self-center d-none d-xl-flex flex-column px-4 px-xl-2 border-end border-secondary"
						>
							<span class="small">
								{{ convertTime(event.beginn.split(":")) }}
							</span>
							<span class="small">
								{{ convertTime(event.ende.split(":")) }}
							</span>
						</div>
						<div
							class="d-flex flex-column flex-grow-1 align-items-center overflow-auto"
							style="font-size:0.75rem"
						>
							<span>{{ event.topic }}</span>
							<span v-for="lektor in event.lektor">
								{{ lektor.kurzbz }}
							</span>
							<span>{{ event.ort_kurzbz }}</span>
						</div>
					</div>
				</template>
				<template v-if="mode == 'day'">
					<div
						@click="mobile ? showModal($event, event) :null"
						type="button"
						class="fhc-entry border border-secondary d-flex justify-content-evenly event-colored h-100 overflow-hidden"
		 				:style="'--event-color-rgb:' + makeRGB(event.farbe || 'cccccc')"
					>
						<div
							v-if="event.beginn && event.ende"
							class="align-self-center d-flex flex-column px-4 px-xl-2 border-end border-secondary"
						>
							<div class="d-flex flex-column border-end border-secondary">
								<span class="small">
									{{ convertTime(event.beginn.split(":")) }}
								</span>
								<span class="small">
									{{ convertTime(event.ende.split(":")) }}
								</span>
							</div>
						</div>
						<div class="flex-grow-1 row overflow-auto">
							<div class="col">
								<p>{{ $p.t('lehre/lehrveranstaltung') }}:</p>
								<p class="m-0">{{ event.topic }}</p>
							</div>
							<div
								class="col"
								:style="'max-height:' + eventMaxHeight"
							>
								<p>{{ $p.t('lehre/lektor') }}:</p>
								<p
									class="m-0"
									v-for="lektor in event.lektor"
								>
									{{ lektor.kurzbz }}
								</p>
							</div>
							<div class="col">
								<p>{{ $p.t('profil/Ort') }}: </p>
								<p class="m-0">{{ event.ort_kurzbz }}</p>
							</div>
						</div>
					</div>
				</template>
				<event-event v-if="mode == 'event'" :event="event" />
			</template>
		</fhc-calendar>
	</div>
	`
}
/*
*/
export default Stundenplan