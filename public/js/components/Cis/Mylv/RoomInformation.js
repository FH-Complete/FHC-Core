import FhcCalendar from "../../Calendar/Calendar.js";
import CalendarDate from "../../../composables/CalendarDate.js";
import LvModal from "../../../components/Cis/Mylv/LvModal.js";
import LvInfo from "../../../components/Cis/Mylv/LvInfo.js"

export const DEFAULT_MODE_RAUMINFO = 'Week'

const RoomInformation = {
	name: "RoomInformation",
    props:{
		propsViewData: {
			type: Object
		},
		rowMinHeight: {
			type: String,
			default: '100px'
		},
		eventMaxHeight: {
			type: String,
			default: '125px'
		}
    },
	components: {
		FhcCalendar,
		LvModal,
		LvInfo,
	},
	provide() {
		return {
			rowMinHeight: this.rowMinHeight,
			eventMaxHeight: this.eventMaxHeight
		}
	},
	data() {
		return {
			events: null,
			calendarMode: DEFAULT_MODE_RAUMINFO,
			calendarDate: new CalendarDate(new Date()),
			currentlySelectedEvent: null,
			currentDay: this.propsViewData?.focus_date ? new Date(this.propsViewData.focus_date) : new Date(),
			minimized: false,
            
        }
	},
    computed:{
        currentDate: function(){
            return new Date(this.calendarWeek.y, this.calendarWeek.m, this.calendarWeek.d);
        },
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
	watch: {
		'propsViewData.ort_kurzbz'(newVal) {
			// relevant if ort_kurzbz can be changed from within this component
		},
		'propsViewData.mode'(newVal) {
			if(this.$refs.calendar) this.$refs.calendar.setMode(newVal)
		},
		'propsViewData.focus_date'(newVal) {
			this.currentDate = new Date(newVal)
		}	
	},
    methods:{
		setSelectedEvent: function(event){
			this.currentlySelectedEvent = event;
		},
		getLvID: function () {
			this.lv_id = window.location.pathname
		},
		selectDay: function(day){
			const date = day.getFullYear() + "-" +
				String(day.getMonth() + 1).padStart(2, "0") + "-" +
				String(day.getDate()).padStart(2, "0");

			this.$router.push({
				name: "RoomInformation",
				params: {
					mode: this.calendarMode,
					focus_date: date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
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
			const date = this.currentDay.getFullYear() + "-" +
				String(this.currentDay.getMonth() + 1).padStart(2, "0") + "-" +
				String(this.currentDay.getDate()).padStart(2, "0");
			
			this.$router.push({
				name: "RoomInformation",
				params: {
					mode: modeCapitalized,
					focus_date: date,
					ort_kurzbz: this.propsViewData.ort_kurzbz
				}
			})

			this.calendarMode = mode
		},
		showModal: function (event) {
			this.currentlySelectedEvent = event;
			Vue.nextTick(() => {
				this.$refs.lvmodal.show();
			});
			
		},
		updateRange: function ({ start, end }) {

			let checkDate = (date) => {
				return date.m != this.calendarDate.m || date.y != this.calendarDate.y;
			}

			// only load month data if the month or year has changed
			if (checkDate(new CalendarDate(start)) && checkDate(new CalendarDate(end))) {
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

			// bundles the room_events and the reservierungen together into the this.events array
			Promise.allSettled([
				this.$fhcApi.factory.stundenplan.getRoomInfo(this.propsViewData.ort_kurzbz, this.monthFirstDay, this.monthLastDay),
				this.$fhcApi.factory.stundenplan.getOrtReservierungen(this.propsViewData.ort_kurzbz, this.monthFirstDay, this.monthLastDay)
			]).then((result) => {
				let promise_events = [];
				result.forEach((promise_result) => {
					if(promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success"){
						
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
			})
		},
    },
	created() {
		this.loadEvents();
	},
    template: /*html*/`
		<h2>{{ $p.t('rauminfo/rauminfo') }} {{ propsViewData.ort_kurzbz }}</h2>
		<hr>
		<lv-modal v-if="currentlySelectedEvent" :showMenu="false" :event="currentlySelectedEvent" ref="lvmodal" />
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
            <template #monthPage="{event,day}">
				<span >
					{{event.topic}}
				</span>
			</template>
			<template #weekPage="{event,day}">
				<div @click="showModal(event?.orig)" type="button" class=" border border-secondary border d-flex flex-column align-items-center justify-content-evenly h-100">
					<span>{{event?.orig.topic}}</span>
					<span v-for="lektor in event?.orig.lektor">{{lektor.kurzbz}}</span>
					<span>{{event?.orig.ort_kurzbz}}</span>
				</div>
			</template>
			<template #dayPage="{event,day,mobile}">
				<div @click="mobile? showModal(event?.orig):null" type="button" class="fhc-entry border border-secondary border row h-100 justify-content-center align-items-center text-center">
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
			<template #pageMobilContent>
				<h3 >{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
				<div class="w-100">
					<lv-info :event="currentlySelectedEvent" />
				</div>
			</template>
			<template #pageMobilContentEmpty >
				<h3>{{$p.t('rauminfo','keineRaumReservierung')}}</h3>
			</template>
        </fhc-calendar>
    `,
};

export default RoomInformation