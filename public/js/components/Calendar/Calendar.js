import CalendarMonth from './Month.js';
import CalendarMonths from './Months.js';
import CalendarYears from './Years.js';
import CalendarWeek from './Week.js';
import CalendarWeeks from './Weeks.js';
import CalendarDay from './Day.js';
import CalendarMinimized from './Minimized.js';
import CalendarDate from '../../composables/CalendarDate.js';


// TODO(chris): week/month toggle

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
			date: this.date,
			focusDate: this.focusDate,
			size: Vue.computed({ get: () => this.size, set: v => this.size = v }),
			events: Vue.computed(() => this.eventsPerDay),
			filteredEvents: Vue.computed(() => this.filteredEvents),
			minimized: Vue.computed({ get: () => this.minimized, set: v => this.$emit('update:minimized', v) }),
			showWeeks: this.showWeeks,
			noMonthView: this.noMonthView,
			noWeekView: this.noWeekView,
			eventsAreNull: Vue.computed(() => this.events === null),
			classHeader: this.classHeader,
			mode: Vue.computed(()=>this.mode),
			selectedEvent: Vue.computed(() => this.selectedEvent),
			setSelectedEvent: (event)=>{this.selectedEvent = event;},
			widget: this.widget
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
		classHeader: {
			type: [String, Object, Array],
			default: ''
		},
		minimized: Boolean,
		noWeekView: Boolean,
		noMonthView: Boolean,
		widget: {
			type: Boolean,
			default: false
		}
	},
	watch:{
		// scroll to the first event if the html element was found
		scrollTime({focusDate,scrollTime}){
			// return early if the scrollTime is not set
			if(!scrollTime) return;
			// scroll the Stundenplan to the closest event
			Vue.nextTick(()=>{
				let previousScrollAnchor = document.getElementById('scroll' + (scrollTime - 1) + this.focusDate.d + this.focusDate.w)
				let scrollAnchor = document.getElementById('scroll' + scrollTime + this.focusDate.d + this.focusDate.w);
				if (previousScrollAnchor) {
					previousScrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
				else {
					if (scrollAnchor) {
						scrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				}
			});
		}
	},
	emits: [
		'select:day',
		'select:event',
		'change:range',
		'update:minimized'
	],
	data() {
		return {
			header: '',
			prevMode: null,
			currMode: null,
			date: new CalendarDate(),
			focusDate: new CalendarDate(),
			size: 0,
			selectedEvent:null,
		}
	},
	computed: {
		sizeClass() {
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
				// return the first beginning time of the filtered events
				if(this.filteredEvents && Array.isArray(this.filteredEvents) && this.filteredEvents.length > 0)
				{
					let scrollTime = parseInt(this.filteredEvents.sort((a, b) => parseInt(a.beginn) - parseInt(b.beginn))[0].beginn);
					// to ensure that the scrollTime watcher triggers even if the scrollTime doesn't change, it returns both the scrollTime and the focusDate
					return { focusDate: this.focusDate, scrollTime };
				}
				// there is no event that matches the current view mode constraints
				else 
				{
					return { focusDate: this.focusDate, scrollTime: null };
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
					if (this.mode == 'week') 
					{
						// week view filters the elements only for the same week
						return this.focusDate.w == eventDate.w;
					}
					else if (this.mode == 'day') 
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
		handleInput(day) {
			this.$emit(day[0], day[1]);
		}
	},
	created() {
		const allowedInitialModes = ['years'];
		if (!this.noWeekView)
			allowedInitialModes.push('week');
		if (!this.noMonthView)
			allowedInitialModes.push('month');
		this.mode = allowedInitialModes[allowedInitialModes.indexOf(this.initialMode)] || allowedInitialModes.pop();
		this.date.set(new Date(this.initialDate));
		this.focusDate.set(this.date);
	},
	mounted() {
		if (this.$refs.container) {
			new ResizeObserver(entries => {
				for (const entry of entries) {
					let w = entry.contentBoxSize ? entry.contentBoxSize[0].inlineSize : entry.contentRect.width;
					// TODO(chris): rework sizing
					if (w > 600)
						this.size = 3;
					else if (w > 350)
						this.size = 2;
					else if (w > 250)
						this.size = 1;
					else
						this.size = 0;
				}
			}).observe(this.$refs.container);
		}

	},
	template: /*html*/`
	<div ref="container" class="fhc-calendar card h-100" :class="sizeClass">
		<component :is="'calendar-' + mode" @updateMode="mode = $event" @change:range="$emit('change:range',$event)" @input="handleInput" >
			<template #monthPage="{event,day,isSelected}">
				<slot name="monthPage" :event="event" :day="day" :isSelected="isSelected"></slot>
			</template>
			<template #weekPage="{event,day,isSelected}">
				<slot name="weekPage" :event="event" :day="day" :isSelected="isSelected"></slot>
			</template>
			<template #dayPage="{event,day}">
				<slot name="dayPage" :event="event" :day="day"></slot>
			</template>
			<template #minimizedPage="{event,day}">
				<slot name="minimizedPage" :event="event" :day="day"></slot>
			</template>
		</component>
	</div>`
}
