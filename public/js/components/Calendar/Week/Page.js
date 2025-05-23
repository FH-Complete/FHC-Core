import CalendarDate from '../../../composables/CalendarDate.js';

function ggt(m,n) { return n==0 ? m : ggt(n, m%n); }
function kgv(m,n) { return (m*n) / ggt(m,n); }

export default {
	name: 'WeekPage',
	data(){
		return{
			hourPosition:null,
			hourPositionTime:null,
			resizeObserver: null,
			width: 0
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
		'isSliding',
		'selectedEvent',
		'setSelectedEvent',
		'rowMinHeight'
	],
	props: {
		year: Number,
		week: Number
	},
	emits: [
		'updateMode',
		'page:back',
		'page:forward',
		'input',
	],
	computed: {
		getGridStyle() {
			return {
				'min-height': this.rowMinHeight,
				// this.size is the magic number anyway which directs font-size,
				// which in turn influences a lot of layout
				width: '42px'
			}
		},
		laneWidth() {
			return (this.width - 42) / this.days.length
		},
		curTime() {
			const now = new Date();
			return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
		},
		pageHeaderStyle(){
			return {
				'z-index': 4,
				'grid-template-columns': 'repeat(' + this.days.length + ', 1fr)',
				'grid-template-rows': 1,
				position: 'sticky',
				top: 0,
			}
		},
		overlayStyle() {
			return {
				'background-color': '#F5E9D7',
				'position': 'absolute',
				'pointer-events': 'none',
				'z-index': 2,
				height:  this.getDayTimePercent + '%',
				width: this.laneWidth + 'px',
				opacity: 0.5
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
		hours(){
			// returns an array with elements starting at 7 and ending at 24
			return [...Array(24).keys()].filter(hour => hour >= 7 && hour <= 24);
		},
		dayText() {
			if (!this.size || !this.days) return {};
			let dayTextMap ={};
			this.days.forEach((day)=>{
				dayTextMap[day] = {
					heading: day.toLocaleString(this.$p.user_locale.value, { dateStyle: 'short' }),
					tag: day.toLocaleString(this.$p.user_locale.value, { weekday: this.size < 2 ? 'narrow' : (this.size < 3 ? 'short' : 'long') }),
					datum: day.toLocaleString(this.$p.user_locale.value, [{ day: 'numeric', month: 'numeric' }, { day: 'numeric', month: 'numeric' }, { day: 'numeric', month: 'numeric' }, { dateStyle: 'short' }][this.size]),
				};
			});
			return dayTextMap;
		},
		days() {
			
			let tmpDate = new CalendarDate(this.year,1,1); // NOTE(chris): somewhere in the middle of the year
			tmpDate.w = this.week;
			let startDay = tmpDate.firstDayOfWeek;
			let result = [];
			for (let i = 0; i < 7; i++) {
				result.push(new Date(startDay.getFullYear(), startDay.getMonth(), startDay.getDate() + i));
			}
			return result;

		},
		eventsPerDayAndHour() {
			// return early if the calendar pane is sliding
			if (this.isSliding) return {};
			const res = {};
			this.days.forEach(day => {
				let key = day.toDateString();
				
				let nextDay = new Date(day);
				nextDay.setDate(nextDay.getDate()+1);
				nextDay.setMilliseconds(nextDay.getMilliseconds()-1);
				let d = {events:[],lanes:1, isPast: false};
				d.isPast = nextDay.getTime() < this.today
				d.isToday = nextDay.getFullYear() === this.todayDate.getFullYear() && nextDay.getMonth() === this.todayDate.getMonth() && nextDay.getDate() === this.todayDate.getDate()
				if (this.events[key]) {
					this.events[key].forEach(evt => {
						let event = {orig:evt,lane:1,maxLane:1,start: evt.start < day ? day : evt.start, end: evt.end > nextDay ? nextDay : evt.end,shared:[],setSharedMaxRecursive(doneItems) {
							this.maxLane = Math.max(doneItems[0].maxLane, this.maxLane);
							doneItems.push(this);
							this.shared.filter(other => !doneItems.includes(other)).forEach(i => i.setSharedMaxRecursive(doneItems));
						}};
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
			});
			return res;
		},
		smallestTimeFrame() {
			return [30,15,10,5][this.size];
		},
		lookingAtToday() {
			return this.days.some(d => 
				d.getFullYear() === this.todayDate.getFullYear() &&
				d.getMonth() === this.todayDate.getMonth() &&
				d.getDate() === this.todayDate.getDate()
			)
		},
		curIndicatorStyle() {

			return {
				'pointer-events': 'none',
				'padding-left': '1rem',
				'margin-top': '-1px',
				'z-index': 2,
				'border-color': '#00649C!important',
				top: this.getDayTimePercent + '%',
				width: this.laneWidth + 'px'
			}
		},
		getDayTimePercent() {
			const now = new Date(Date.now())
			const currentMinutes = now.getMinutes() + now.getHours() * 60
			let timePercentage = ((currentMinutes - (this.hours[0] * 60)) / (this.hours.length * 60)) * 100;

			return timePercentage
		}
	}, 
	methods: {
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
				'z-index': 0
			}
		},
		dayGridStyle(day) {
			const styleObj = {
				'grid-template-columns': 'repeat(' + day.lanes + ', 1fr)',
				'grid-template-rows': 'repeat(' + (this.hours.length * 60 / this.smallestTimeFrame) + ', 1fr)',
			}
			
			if(day.isPast) {
				styleObj['background-color'] = '#F5E9D7'
				styleObj['border-color'] = '#E8E8E8';
				styleObj.opacity = 0.5;
			} else if (day.isToday) {
				
				// styleObj['backgroundImage'] = 'linear-gradient(to bottom, #F5E9D7 '+this.getDayTimePercent+'%, #FFFFFF '+this.getDayTimePercent+'%)'
				// styleObj['border-color'] = '#E8E8E8';
				// styleObj.opacity = 0.5;
			}
			
			return styleObj
		},
		eventGridStyle(day, event) {
			return {
				'z-index': 1,
				'grid-column-start': 1 + (event.lane - 1) * day.lanes / event.maxLane,
				'grid-column-end': 1 + event.lane * day.lanes / event.maxLane,
				'grid-row-start': this.dateToMinutesOfDay(event.start),
				'grid-row-end': this.dateToMinutesOfDay(event.end),
				'background-color': event.orig.color,
				'max-height': '75px'				
			}
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
		getAbsolutePositionForHour(hour){
			// used for the absolute positioning of the gutters of hours
			return (100 / this.hours.length) * (hour - (24-this.hours.length)) + '%';
		},
		changeToMonth(day) {
			if (!this.noMonthView) {
				this.date.set(day);
				this.focusDate.set(day);
				this.$emit('updateMode', 'month');
			}
		},
		changeToDay(day) {
			this.date.set(day);
			this.focusDate.set(day);
			this.$emit('updateMode', 'day');
		},
		dateToMinutesOfDay(day) {
			// subtract 7 from the total hours because the hours range from 7 to 24
			return Math.floor(((day.getHours()-7) * 60 + day.getMinutes()) / this.smallestTimeFrame) + 1;
		},
		weekPageClick(event, day) {
			this.setSelectedEvent(event);
			this.focusDate.set(new CalendarDate(new Date(event.datum)));
			this.$emit('input', event)
		},
		initResizeObserver() {
			const events = this.$refs['eventsRef'+this.week];
			if (!events) return;

			this.resizeObserver = new ResizeObserver((entries) => {
				for (let entry of entries) {
					const { width, height } = entry.contentRect;
					if(width > 0) this.width = width
				}
			});

			this.resizeObserver.observe(events);
		},
		destroyResizeObserver() {
			if (this.resizeObserver) {
				this.resizeObserver.disconnect();
				this.resizeObserver = null;
			}
		},
	},
	mounted() {

		setTimeout(() => this.$refs.eventcontainer.scrollTop = this.$refs.eventcontainer.scrollHeight / 3 + 1, 0);

		const container = document.getElementById("calendarContainer")
		if(container) {
			container.style['overflow-y'] = 'scroll'
			container.style['overflow-x'] = 'auto'
		}

		this.initResizeObserver();
	},
	beforeUnmount() {
		this.destroyResizeObserver();
	},
	template: /*html*/`
	<div ref="page" class="fhc-calendar-week-page" style="min-width: 700px;">
		<div class="d-flex flex-column">
			<div class="fhc-calendar-week-page-header d-grid border-2 border-bottom text-center" :style="pageHeaderStyle" >
				<div type="button" v-for="day in days" :key="day" class="flex-grow-1" :title="dayText[day]?.heading" @click.prevent="changeToDay(day)">
					<div class="fw-bold">{{dayText[day]?.tag}}</div>
					<a href="#" class="small text-secondary text-decoration-none" >{{dayText[day]?.datum}}</a>
				</div>
			</div>
			<div ref="eventcontainer" class="position-relative flex-grow-1" @mousemove="calcHourPosition" @mouseleave="hourPosition = null" >
				<div :id="hourGridIdentifier(hour)" v-for="hour in hours" :key="hour"  class="position-absolute box-shadow-border-top" :style="hourGridStyle(hour)"></div>
				<Transition>
					<div v-if="hourPosition" class="position-absolute border-top small"  :style="indicatorStyle">
						<span class="border border-top-0 px-2 bg-white">{{hourPositionTime}}</span>
					</div>
				</Transition>
				
				<div class="events" :ref="'eventsRef'+week">
					<div class="hours">
						<div v-for="hour in hours" :style="getGridStyle" :key="hour" class="text-muted text-end small" :ref="'hour' + hour">{{hour}}:00</div>
					</div>
					<div v-for="day in eventsPerDayAndHour" :key="day" class=" day border-start" :style="dayGridStyle(day)">
						<Transition>
							<div v-if="day.isToday" class="position-absolute border-top small"  :style="curIndicatorStyle">
								<span class="border border-top-0 px-2 bg-white">{{curTime}}</span>
							</div>
						</Transition>
						<div v-if="day.isToday" :style="overlayStyle"></div>
						<div v-for="event in day.events" :key="event" @click.prevent="weekPageClick(event.orig, day)" 
						:selected="event.orig == selectedEvent"
						:style="eventGridStyle(day,event)"
						class="mx-2 small rounded overflow-hidden fhc-entry "
						v-contrast >
							<slot name="weekPage" :event="event" :day="day">
								<p>this is a placeholder which means that no template was passed to the Calendar Page slot</p>
							</slot>		
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>`
}
