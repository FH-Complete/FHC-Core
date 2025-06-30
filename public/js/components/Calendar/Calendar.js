import CalendarMonth from './Month.js';
import CalendarMonths from './Months.js';
import CalendarYears from './Years.js';
import CalendarWeek from './Week.js';
import CalendarWeeks from './Weeks.js';
import CalendarDay from './Day.js';
import CalendarMinimized from './Minimized.js';
import CalendarDate from '../../composables/CalendarDate.js';
import CalendarDates from '../../composables/CalendarDates.js';

const todayDate = new Date(new Date().setHours(0, 0, 0, 0));
const today = todayDate.getTime()

export default {
	components: {
		CalendarMonth,
		CalendarMonths,
		CalendarYears,
		CalendarWeek,
		CalendarWeeks,
		CalendarDay,
		CalendarMinimized,
	},
	provide() {
		return {
			today,
			todayDate,
			date: this.date,
			focusDate: this.focusDate,
			size: Vue.computed({ get: () => this.size }),
			containerHeight: Vue.computed({ get: () => this.containerHeight }),
			containerWidth: Vue.computed({ get: () => this.containerWidth }),
			
			events: Vue.computed(() => this.eventsPerDay),
			filteredEvents: Vue.computed(() => this.filteredEvents),
			minimized: Vue.computed({ get: () => this.minimized, set: v => this.$emit('update:minimized', v) }),
			showWeeks: this.showWeeks,
			noMonthView: this.noMonthView,
			noWeekView: this.noWeekView,
			eventsAreNull: Vue.computed(() => this.events === null),
			mode: Vue.computed(()=>this.mode),
			selectedEvent: Vue.computed(() => this.selectedEvent),
			setSelectedEvent: (event)=>{this.selectedEvent = event;},
		};
	},
	props: {
		events: Array,
		initialDate: {
			type: [Date, String],
			default: new Date()
		},
		showWeeks: {
			type: Boolean,
			default: true
		},
		initialMode: {
			type: String,
			default: 'month'
		},
		minimized: Boolean,
		noWeekView: Boolean,
		noMonthView: Boolean
	},
	watch:{
		mode(newVal) {
			this.$emit('change:mode', newVal)
		},
		selectedEvent:{
			handler(newSelectedEvent) {
				this.$emit('selectedEvent', newSelectedEvent);
			},
			immediate: true,
		},
		// scroll to the first event if the html element was found
		scrollTime(newValue,oldValue){
			// return early if the scrollTime is not set
			if (!newValue.scrollTime || !newValue?.doScroll) return;
			if (newValue?.scrollTime == oldValue?.scrollTime && newValue?.focusDate.d==oldValue?.focusDate.d) {
				return;
			}
			// scroll the LvPlan to the closest event
			let previousScrollAnchor = document.getElementById('scroll' + (newValue.scrollTime - 1) + this.focusDate.d + this.focusDate.w)
			let scrollAnchor = document.getElementById('scroll' + newValue.scrollTime + this.focusDate.d + this.focusDate.w);
			if (previousScrollAnchor) {
				previousScrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
			else {
				if (scrollAnchor) {
					scrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			}
			
		},
	},
	emits: [
		'select:day',
		'select:event',
		'change:range',
		'change:mode',
		'update:minimized',
		'selectedEvent',
		'change:offset'
	],
	data() {
		return {
			header: '',
			prevMode: null,
			currMode: null,
			date: new CalendarDate(),
			focusDate: new CalendarDate(),
			size: 0,
			containerWidth: 0,
			containerHeight: 0,
			selectedEvent:null,
		}
	},
	computed: {
		sizeClass() { 
			// mainly determines calendar font-size
			return 'fhc-calendar-' + ['xs', 'sm', 'md', 'lg'][this.size];
		},
		mode: {
			get() { return this.minimized ? 'minimized' : this.currMode; },
			set(v) {
				if (!v && this.prevMode) {
					this.currMode = this.prevMode;
					this.prevMode = null;
				} else {
					this.prevMode = this.currMode;
					this.currMode = v;
				}
			}
		},
		eventsPerDay() {
			if (!this.events)
				return {};
			return this.events.reduce((result, event) => {
				let days = Math.ceil((event.end - event.start) / 86400000) || 1;
				while (days-- > 0) {
					let day = (new Date(event.start.getFullYear(), event.start.getMonth(), event.start.getDate() + days)).toDateString();
					if (!result[day])
						result[day] = [];
					result[day].push(event);
				}
				return result;
			}, {});
		},
		// returns the hour of the earliest event, used to scroll to the events in the calendar (week / day view)
		scrollTime() {
				let doScroll = true;	
				// return the first beginning time of the filtered events
				if(this.filteredEvents && Array.isArray(this.filteredEvents) && this.filteredEvents.length > 0)
				{
					let scrollTimeEvents = this.filteredEvents.filter(event=>{
						return event.type !== 'moodle';
					});
					// do not compute a new scroll time if there are no other events than moodle events
					if(!(scrollTimeEvents.length >0)){
						doScroll = false;
					}
					let scrollTime = parseInt(scrollTimeEvents.sort((a, b) => parseInt(a.beginn) - parseInt(b.beginn))[0]?.beginn);
					// to ensure that the scrollTime watcher triggers even if the scrollTime doesn't change, it returns both the scrollTime and the focusDate
					return { focusDate: { d: this.focusDate.d, w: this.focusDate.w}, doScroll, scrollTime };	
				}
				// there is no event that matches the current view mode constraints
				else 
				{
					return { focusDate: { d: this.focusDate.d, w: this.focusDate.w }, doScroll,scrollTime: null };
				}
			},
		// filters the events based on the current calendar view mode
		// week view - filter events based on their week
		// day view - filter events based on their day and week
		// month view - does not filter the events
		filteredEvents: function(){
			if (this.events && Array.isArray(this.events) && this.events.length > 0) {
				let filteredEvents = this.events.filter(event => {
					let eventDate = new CalendarDate(new Date(event.datum));
					if (this.mode == 'week' || this.mode == 'Week')
					{
						// week view filters the elements only for the same week
						return this.focusDate.w == eventDate.w;
					}
					else if (this.mode == 'day' || this.mode == 'Day')
					{
						// day view filters the elements for the same day and the same week
						return this.focusDate.d == eventDate.d && this.focusDate.w == eventDate.w;
					}
					else
					{
						// returns all the events, does not filter the events
						return true;
					}
				})
				
				return filteredEvents;
			}
			else
			{
				return null;
			}
		},
	},
	methods: {
		handleRangeOffset(event){
			
			let date = new Date(
				this.focusDate.y,
				this.focusDate.m,
				this.focusDate.d
			);
			
			date = date.getFullYear() + "-" +
				String(date.getMonth() + 1).padStart(2, "0") + "-" +
				String(date.getDate()).padStart(2, "0");

			
			this.$router.push({
				name: "LvPlan",
				params: {
					mode: this.mode[0].toUpperCase() + this.mode.slice(1),
					focus_date: date,
					lv_id: this.$route.params.lv_id || null
				}
			});

			this.$emit('change:range',event);
		},
		setMode(mode) {
			this.mode = mode
		},
		handleInput(day) {
			this.$emit(day[0], day[1]);
		},
	},
	created() {
		const initMode = this.initialMode.toLowerCase()
		const allowedInitialModes = ['day'];
		if (!this.noWeekView)
			allowedInitialModes.push('week');
		if (!this.noMonthView)
			allowedInitialModes.push('month');
		this.mode = allowedInitialModes[allowedInitialModes.indexOf(initMode)] || allowedInitialModes.pop();
		this.date.set(new Date(this.initialDate));
		this.focusDate.set(this.date);
	},
	mounted() {
		if (this.$refs.container) {
			new ResizeObserver(entries => {
				for (const entry of entries) {
					const w = entry.contentBoxSize ? entry.contentBoxSize[0].inlineSize : entry.contentRect.width;
					const h = entry.contentBoxSize ? entry.contentBoxSize[0].blockSize : entry.contentRect.height;

					// https://getbootstrap.com/docs/5.0/layout/breakpoints/
					// bootstrap breakpoints watch window size and this function monitors container size of calendar itself.
					// calendar is using bootstrap breakpoints which influence layout, which retriggers this function 
					// -> some width constellations will loop so we dont use values around bs5 breakpoints
					// ['xs', 'sm', 'md', 'lg'][this.size]
					if (w >= 600)
						this.size = 3;
					else if (w >= 350)
						this.size = 2;
					else if (w >= 250)
						this.size = 1;
					else
						this.size = 0;
					
					this.containerWidth = w
					this.containerHeight = h
				}
			}).observe(this.$refs.container);
		}
	},
	unmounted(){
		CalendarDates.cleanup();
	},
	template: /*html*/`
	<div ref="container" class="fhc-calendar card h-100" :class="sizeClass">
		<component :is="'calendar-' + mode" @updateMode="mode = $event" @change:range="handleRangeOffset"
		 @input="handleInput" >
			<template #calendarDownloads>
				<slot name="calendarDownloads" ></slot>
			</template>
			<template #monthPage="{event,day}">
				<slot name="monthPage" :event="event" :day="day" ></slot>
			</template>
			<template #weekPage="{event,day}">
				<slot name="weekPage" :event="event" :day="day" ></slot>
			</template>
			<template #dayPage="{event,day,mobile}">
				<slot name="dayPage" :event="event" :day="day" :mobile="mobile"></slot>
			</template>
			<template #pageMobilContent="{lvMenu, event}">
				<slot name="pageMobilContent" :lvMenu="lvMenu" :event="event"></slot>
			</template>
			<template #minimizedPage="{event,day}">
				<slot name="minimizedPage" :event="event" :day="day"></slot>
			</template>
		</component>
	</div>`
}
