import CalendarDate from '../../../composables/CalendarDate.js';
import LvModal from "../../../components/Cis/Mylv/LvModal.js";

import ApiStundenplan from '../../../api/factory/stundenplan.js';
import ApiAddons from '../../../api/factory/addons.js';

function ggt(m, n) {
	return n == 0 ? m : ggt(n, m % n);
}

function kgv(m, n) {
	return (m * n) / ggt(m, n);
}

export default {
	name: 'DayPage',
	components: {
		LvModal,
	},
	data() {
		return {
			hourPosition: null,
			curHourPosition: null,
			hourPositionTime: null,
			lvMenu: null,
		}
	},
	inject: [
		'today',
		'todayDate',
		'date',
		'focusDate',
		'size',
		'events',
		'noMonthView',
		'filteredEvents',
		'isSliding',
		'calendarScrollTop',
		'calendarClientHeight',
		'setSelectedEvent',
		'selectedEvent',
		'rowMinHeight'
	],
	props: {
		year: Number,
		week: Number,
		active: Boolean,
	},
	emits: [
		'updateMode',
		'page:back',
		'page:forward',
		'input'
	],
	watch: {
		//TODO: on first render non of the day-page components are active and the watcher on selectedEvent does not fetch the lvMenu
		//TODO: workaround is to watch the active state and refetch in case the lvMenu is empty
		active: {
			handler(value) {
				if (value) {
					if (!this.lvMenu) {
						this.fetchLvMenu(this.selectedEvent);
					}
				}
			},
			immediate: true,
		},
		eventsPerDayAndHour: {
			handler(newEvents) {
				// if no event is selected, select the first event of the day
				if (this.selectedEvent == null && newEvents[this.day.toDateString()]?.events.length > 0) {
					let events = newEvents[this.day.toDateString()]?.events;
					if (Array.isArray(events) && events.length > 0) {
						this.setSelectedEvent(events[0].orig);
					}
				}
			},
			immediate: true
		},
		selectedEvent: {
			handler(event) {
				// return early if the day-page component is not the active carousel item
				if (!this.active) {
					return;
				}
				this.lvMenu = null;
				this.fetchLvMenu(event);
			},
			immediate: true,
		},
		isSliding: {
			handler(value) {
				if (value) {
					this.setSelectedEvent(null);
				}
			}
		}
	},
	computed: {
		overlayStyle() {
			return {
				'background-color': '#F5E9D7',
				'position': 'absolute',
				'pointer-events': 'none',
				'z-index': 2,
				height:  this.getDayTimePercent + '%',
				opacity: 0.5,
				overflow: 'hidden'
			}
		},
		pageHeaderStyle() {
			return {
				'z-index': 4,
				'grid-template-columns': 'repeat(' + this.day.length + ', 1fr)',
				'grid-template-rows': 1,
				position: 'sticky',
				top: 0,
			}
		},
		dayText(){
			if(!this.day)return {};
			return {
				heading: this.day.toLocaleString(this.$p.user_locale.value, { dateStyle: 'short' }),
				tag: this.day.toLocaleString(this.$p.user_locale.value, { weekday: this.size < 2 ? 'narrow' : (this.size < 3 ? 'short' : 'long') }),
				datum: this.day.toLocaleString(this.$p.user_locale.value, [{ day: 'numeric', month: 'numeric' }, { day: 'numeric', month: 'numeric' }, { day: 'numeric', month: 'numeric' }, { dateStyle: 'short' }][this.size]),
			}
		},
		noLvStyle() {
			return {
				top: (this.calendarScrollTop + 100) + 'px',
				position: 'absolute',
				left: 0,
				'text-align': 'center',
				width: '100%',
				'z-index': 1,
			}
		},
		indicatorStyle() {
			return {
				'pointer-events': 'none',
				'padding-left': '3.5rem',
				'margin-top': '-1px',
				'z-index': 2,
				'border-color': '#00649C!important',
				top: this.hourPosition + 'px',
				left: 0,
				right: 0,
			}
		},
		curTime() {
			const now = new Date();
			return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
		},
		curIndicatorStyle() {
			return {
				'pointer-events': 'none',
				'padding-left': '7rem',
				'margin-top': '-1px',
				'z-index': 2,
				'border-color': '#00649C!important',
				top:  this.getDayTimePercent + '%',
				left: 0,
				right: 0,
			}
		},
		noEventsCondition() {
			return !this.isSliding && (this.filteredEvents?.length === 0 || !this.filteredEvents);
		},
		hours() {
			// returns an array with elements starting at 7 and ending at 24
			return [...Array(24).keys()].filter(hour => hour >= 7 && hour <= 24);
		},
		day() {
			return new Date(this.focusDate.y, this.focusDate.m, this.focusDate.d);
		},
		eventsPerDayAndHour() {
			// return early if the calendar pane is sliding
			if (this.isSliding) return {};

			const res = {};

			let key = this.day.toDateString();

			let nextDay = new Date(this.day);
			nextDay.setDate(nextDay.getDate() + 1);
			nextDay.setMilliseconds(nextDay.getMilliseconds() - 1);
			let d = {events: [], lanes: 1};
			if (this.events[key]) {
				this.events[key].forEach(evt => {
					let event = {
						orig: evt,
						lane: 1,
						maxLane: 1,
						start: evt.start < this.day ? this.day : evt.start,
						end: evt.end > nextDay ? nextDay : evt.end,
						shared: [],
						setSharedMaxRecursive(doneItems) {
							this.maxLane = Math.max(doneItems[0].maxLane, this.maxLane);
							doneItems.push(this);
							this.shared.filter(other => !doneItems.includes(other)).forEach(i => i.setSharedMaxRecursive(doneItems));
						}
					};
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

			return res;
		},
		smallestTimeFrame() {
			return [30, 15, 10, 5][this.size];
		},
		lookingAtToday() {
			return this.date.compare(this.todayDate)
		},
		getDayTimePercent() {
			const now = new Date(Date.now())
			const currentMinutes = now.getMinutes() + now.getHours() * 60
			let timePercentage = ((currentMinutes - (this.hours[0] * 60)) / (this.hours.length * 60)) * 100;

			return timePercentage
		}
	},
	methods: {
		dayGridStyle(day) {
			const styleObj = {
				'grid-template-columns': '1 1fr',
				'grid-template-rows': 'repeat(' + (this.hours.length * 60 / this.smallestTimeFrame) + ', 1fr)',
			}

			if(this.date.compare(this.todayDate)) {
				styleObj['backgroundImage'] = 'linear-gradient(to bottom, #F5E9D7 '+this.getDayTimePercent+'%, #FFFFFF '+this.getDayTimePercent+'%)'
				styleObj['border-color'] = '#E8E8E8';
				// styleObj.opacity = 0.5; // would opaque the whole column
			}

			return styleObj
		},
		fetchLvMenu(event) {
			if (event && event.type == 'lehreinheit') {
				this.$api
					.call(ApiStundenplan.getLehreinheitStudiensemester(event.lehreinheit_id[0]))
					.then(res => res.data)
					.then(studiensemester_kurzbz => this.$api.call(
						ApiAddons.getLvMenu(
							event.lehreinheit_id,
							studiensemester_kurzbz
						)
					))
					.then(res => {
						if (res.data) {
							this.lvMenu = res.data;
						}
					});
			}
		},
		hourGridIdentifier(hour) {
			// this is the id attribute that is responsible to scroll the calender to the first event
			return 'scroll' + hour + this.focusDate.d + this.week;
		},
		hourGridStyle(hour) {
			return {
				'pointer-events': 'none',
				top: this.getAbsolutePositionForHour(hour),
				left: 0,
				right: 0,
				'z-index': 0,
			}
		},
		eventGridStyle(day, event) {
			return {
				'z-index': 1,
				'grid-column-start': 1 + (event.lane - 1) * day.lanes / event.maxLane,
				'grid-column-end': 1 + event.lane * day.lanes / event.maxLane,
				'grid-row-start': this.dateToMinutesOfDay(event.start),
				'grid-row-end': this.dateToMinutesOfDay(event.end),
				'background-color': event.orig.color,
				'--test': this.dateToMinutesOfDay(event.end),
			}
		},
		eventClick(evt) {
			let event = evt.orig;
			this.setSelectedEvent(event);
			this.$emit('input', event);
		},
		calcHourPosition(event) {
			let height = this.$refs.eventcontainer.getBoundingClientRect().height;
			let top = this.$refs.eventcontainer.getBoundingClientRect().top;
			let position = event.clientY - top;
			// position percentage of total height
			let timePercentage = (position / height) * 100;
			// minute percentage of total minutes
			let result = (this.hours.length * 60) * (timePercentage / 100);
			// calculate time in float
			let currentMinutes = ((result + (this.hours[0] * 60)) / 60);
			// get hour part of time
			let currentHour = Math.floor(currentMinutes);
			// get float part of time
			let minutePercentage = currentMinutes % currentHour;
			// calculate minutes from float part of time
			let minute = Math.round(60 * minutePercentage);
			// convert minutes to 5 minute interval
			if (minute % 5 != 0) {
				minute = Math.round(minute / 5) * 5;
			}
			// in case the rounding made the minutes 60, increase the hour and reset the minutes
			if (minute == 60) {
				currentHour++;
				minute = 0;
			}

			// ## after rounding the time to the nearest 5 Minute interval, we have to convert the time back to the relative position
			// convert current time in minutes
			currentMinutes = currentHour * 60 + minute;
			// calculate the minutes percentage of the total minutes 
			timePercentage = ((currentMinutes - (this.hours[0] * 60)) / (this.hours.length * 60)) * 100;
			// calculate the relative position of the time percentage
			position = height * (timePercentage / 100);
			this.hourPosition = position;

			// add padding to minutes that consist of only one digit
			minute.toString().length == 1 ? minute = "0" + minute : minute;
			this.hourPositionTime = currentHour + ":" + minute;
		},
		getAbsolutePositionForHour(hour) {
			// used for the absolute positioning of the gutters of hours
			return (100 / this.hours.length) * (hour - (24 - this.hours.length)) + '%';
		},
		changeToMonth(day) {
			if (!this.noMonthView) {
				this.date.set(day);
				this.focusDate.set(day);
				this.$emit('updateMode', 'month');
			}
		},
		dateToMinutesOfDay(day) {
			return Math.floor(((day.getHours() - 7) * 60 + day.getMinutes()) / this.smallestTimeFrame) + 1;
		}

	},
	template: /*html*/`
	<div class="fhc-calendar-day-page h-100">
		<div class="row m-0 h-100">
			<div class="col-12 col-xl-6 p-0 h-100">
				<div class="d-flex flex-column h-100">
					<div ref="header" class="fhc-calendar-week-page-header d-grid border-2 border-bottom text-center" :style="pageHeaderStyle">
						<div type="button" class="flex-grow-1" :title="dayText.heading" @click.prevent="changeToMonth(day)">
							<div class="fw-bold">{{dayText.tag}}</div>
							<a href="#" class="small text-secondary text-decoration-none" >{{dayText.datum}}</a>
						</div>
					</div>
					<div id="scroll g-0" style="height: 100%; overflow-y: scroll;">

						<div ref="eventcontainer" class="position-relative flex-grow-1" @mousemove="calcHourPosition" @mouseleave="hourPosition = null" >
							<div :id="hourGridIdentifier(hour)" v-for="hour in hours" :key="hour"  class="position-absolute box-shadow-border-top" :style="hourGridStyle(hour)"></div>

							<Transition>
								<div v-if="hourPosition && !noEventsCondition" class="position-absolute border-top small"  :style="indicatorStyle">
									<span class="border border-top-0 px-2 bg-white">{{hourPositionTime}}</span>
								</div>
							</Transition>
							<Transition>
								<div v-if="lookingAtToday && !noEventsCondition" class="position-absolute border-top small"  :style="curIndicatorStyle">
									<span class="border border-top-0 px-2 bg-white">{{curTime}}</span>
								</div>
							</Transition>
							<div>
								<h1 v-if="noEventsCondition" class="m-0 text-secondary" ref="noEventsText" :style="noLvStyle">{{ $p.t('lehre/noLvFound') }}</h1>
								<div :class="{'fhc-calendar-no-events-overlay':noEventsCondition, 'events':true}">

									<div class="hours">
										<div v-for="hour in hours" :style="'min-height:' + rowMinHeight " :key="hour" class="text-muted text-end small" :ref="'hour' + hour">{{hour}}:00</div>
									</div>
									<div v-for="day in eventsPerDayAndHour" :key="day" class=" day border-start" :style="dayGridStyle(day)">
										<div v-if="lookingAtToday && !noEventsCondition" :style="overlayStyle"></div>
										<div v-for="event in day.events" :key="event" :style="eventGridStyle(day,event)" v-contrast 
											:selected="event.orig == selectedEvent" class="fhc-entry mx-2 small rounded overflow-hidden" >
											<!-- desktop version of the page template, parent receives slotProp mobile = false -->
											<div class="d-none d-xl-block h-100 "  @click.prevent="eventClick(event)">
												<slot  name="dayPage" :event="event" :day="day" :mobile="false">
													<p>this is a slot placeholder</p>
												</slot>
											</div>
											<!-- mobile version of the page template, parent receives slotProp mobile = true -->
											<div class="d-block d-xl-none h-100" @click.prevent="eventClick(event)">
												<slot  name="dayPage" :event="event" :day="day" :mobile="true">
													<p>this is a slot placeholder</p>
												</slot>
											</div>
		
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-xl-block col-xl-6 p-4 d-none" style="max-height: 100%">
				<div style="z-index:0; max-height: 100%" class="sticky-top d-flex justify-content-center align-items-center flex-column">
					<div style="max-height: 100%; overflow-y:auto;" class="w-100">
						<template v-if="selectedEvent && lvMenu">
							<slot name="pageMobilContent" :lvMenu="lvMenu" >
								<p>this is a slot placeholder</p>
							</slot>
						</template>
						<template v-else-if="noEventsCondition">
							<slot name="pageMobilContentEmpty" >
								<h3>This is an slot placeholder</h3>
							</slot>
						</template>
						<template v-else>
							<div class="p-4 d-flex w-100 justify-content-center align-items-center">
								<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>	
	</div>

`
}
