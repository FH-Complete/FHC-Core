import CalendarDate from '../../../composables/CalendarDate.js';

export default {
	inject: [
		'date',
		'focusDate',
		'size',
		'events',
		'showWeeks',
		'noWeekView'
	],
	props: {
		year: Number,
		month: Number
	},
	emits: [
		'update:mode',
		'page:back',
		'page:forward',
		'input'
	],
	computed: {
		weeks() {
			let firstDayOfMonth = new CalendarDate(this.year, this.month, 1);
			let startDay = firstDayOfMonth.firstDayOfCalendarMonth;
			let endDay = firstDayOfMonth.lastDayOfCalendarMonth;

			let res = [];
			let week = {no:0,y:0,days:[]};
			while (startDay <= endDay) {
				week.days.push(new Date(startDay));
				if (week.days.length == 7) {
					let d = new CalendarDate(week.days[res.length ? 0 : 6]);
					week.no = d.w;
					week.y = d.y;
					res.push(week);
					week = {no:0,y:0,days:[]};
				}
				startDay.setDate(startDay.getDate() + 1);
			}
			return res;
		}
	},
	methods: {
		selectDay(day) {
			this.date.set(day);
			this.$emit('input', day);
		},
		changeToWeek(week) {
			if (!this.noWeekView) {
				if (!this.focusDate.isInWeek(week.no, week.y))
					this.focusDate.set(week.days[0]);
				this.$emit('update:mode', 'week');
			}
		}
	},
	template: `
	<div class="fhc-calendar-month-page" :class="{'show-weeks': showWeeks}">
		<div v-if="showWeeks" class="bg-light fw-bold border-top border-bottom text-center"></div>
		<div v-for="day in weeks[0].days" :key="day" class="bg-light fw-bold border-top border-bottom text-center">
			{{day.toLocaleString(undefined, {weekday: size < 1 ? 'narrow' : (size < 3 ? 'short' : 'long')})}}
		</div>
		<template v-for="week in weeks" :key="week.no">
			<a href="#" v-if="showWeeks" class="fhc-calendar-month-page-weekday text-decoration-none text-end opacity-25" @click.prevent="changeToWeek(week)">{{week.no}}</a>
			<a href="#" v-for="day in week.days" :key="day" class="fhc-calendar-month-page-day text-decoration-none overflow-hidden" :class="{active:date.compare(day),'opacity-50':day.getMonth() != month}" @click.prevent="selectDay(day)">
				<span class="no">{{day.getDate()}}</span>
				<span v-if="events[day.toDateString()] && events[day.toDateString()].length" class="events">
					<span v-for="event in events[day.toDateString()]" :key="event.id" style="color:white" :style="{'background-color': event.color}">
						{{event.title}}
					</span>
				</span>
			</a>
		</template>
	</div>`
}
