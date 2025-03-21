import CalendarDate from '../../../composables/CalendarDate.js';

export default {
	name: 'MonthPage',
	data(){
		return{
			highlightedWeek: null,
			highlightedDay: null,
		}
	},
	inject: [
		'today',
		'todayDate',
		'date',
		'focusDate',
		'size',
		'events',
		'showWeeks',
		'noWeekView',
		'selectedEvent',
		'setSelectedEvent'
	],
	props: {
		year: Number,
		month: Number
	},
	emits: [
		'updateMode',
		'page:back',
		'page:forward',
		'input'
	],
	computed: {
		dayText(){
			if (!this.size || !this.weeks[0]?.days) return {};
			let dayTextMap ={};
			this.weeks[0].days.forEach((day)=>{
				dayTextMap[day] = day.toLocaleString(this.$p.user_locale.value, { weekday: this.size < 1 ? 'narrow' : (this.size < 3 ? 'short' : 'long') });
			});
			return dayTextMap;
		},
		weeks() {
			let firstDayOfMonth = new CalendarDate(this.year, this.month, 1);
			let startDay = firstDayOfMonth.firstDayOfCalendarMonth;
			let endDay = firstDayOfMonth.lastDayOfCalendarMonth;
			
			let res = [];
			let week = {no:0,y:0,days:[]};
			while (startDay <= endDay) {
				week.days.push(new Date(startDay));
				
				if (week.days.length == 7) {
					let d = new CalendarDate(week.days[5]);
					week.no = d.w;
					week.y = d.y;
					res.push(week);
					week = {no:0,y:0,days:[]};
				}
				startDay.setDate(startDay.getDate() + 1);
			}
			return res;
		},
		
	},
	methods: {
		getDayClass(week, day) {
			let classstring = 'fhc-calendar-month-page-day text-decoration-none overflow-hidden'
			const isHighlightedWeek = this.isHighlightedWeek(week)
			const isHighlightedDay = this.isHighlightedDay(day)
			const isThisDate = this.focusDate.compare(day)

			const isNotThisMonth = day.getMonth() != this.month
			const isInThePast = day.getTime() < this.today // this.date is just the focusDate but not the initial Date
			
			if(isThisDate) classstring += ' fhc-calendar-month-page-day-focusday'
			if(isHighlightedWeek) classstring += ' fhc-highlight-week'
			if(isHighlightedDay) classstring += ' fhc-highlight-day'
			
			if(isNotThisMonth) classstring += ' opacity-25'
			if(isInThePast) classstring += ' fhc-calendar-past'
			return classstring
		},
		selectDay(day, event) {
			this.setSelectedEvent(event);
			this.date.set(day);
			this.$emit('input', day);
		},
		changeToWeek(week) {
			if (!this.noWeekView) {
				if (!this.focusDate.isInWeek(week.no, week.y))
					this.focusDate.set(week.days[0]);
				this.$emit('updateMode', 'week');
			}
		},
		highlight(week, day){
			this.highlightedWeek = week.no; 
			this.highlightedDay = day;
		},
		isHighlightedDay(day) {
			return day == this.highlightedDay
		},
		isHighlightedWeek(week) {
			return  week.no == this.highlightedWeek
		},
		clickEvent(day,week) {
			if(!this.noWeekView)
			{
				this.focusDate.set(day);
				this.$emit('updateMode', 'day');
			}
			this.selectDay(day);
		},
		getNumberStyle(day) {
			
			const styleObj = {}
			styleObj.display = 'inline-block';
			styleObj.height = '32px';
			styleObj['line-height'] = '32px';
			styleObj['text-align'] = 'center';
			styleObj['font-weight'] = 'bold';
			styleObj['font-size'] = '14px';

			if(day.getDate() === this.todayDate.getDate() 
				&& day.getMonth() === this.todayDate.getMonth() 
				&& day.getFullYear() === this.todayDate.getFullYear()) {
				styleObj['background-color'] = 'var(--fhc-primary)'; 
				styleObj.color = 'white';
			}
			
			return styleObj
		} 
	},
	mounted() {
		const container = document.getElementById("calendarContainer")
		if(container) container.style['overflow-y'] = 'auto'
		
	},
	template: /*html*/`
	<div class="fhc-calendar-month-page" :class="{'show-weeks': showWeeks}">
		<div v-if="showWeeks" class=" fw-bold border-top border-bottom text-center"></div>
		<div v-for="day in weeks[0].days" :key="day" class=" fw-bold border-top border-bottom text-center">
			{{dayText[day]}}
		</div>
		<template v-for="week in weeks"
		:key="week.no">
			<a href="#" v-if="showWeeks" class="fhc-calendar-month-page-weekday text-decoration-none text-end opacity-25"
			@click.prevent="changeToWeek(week)">{{week.no}}</a>
			<a href="#"
			@click.prevent="clickEvent(day,week)"
			@mouseover="highlight(week,day)"
			@mouseleave="highlightedWeek = null; highlightedDay = null"
			v-for="day in week.days"
			:key="day"
			:class="getDayClass(week, day)" 
			>
				<span @click="clickEvent(day,week)" class="no" :style="getNumberStyle(day)">{{day.getDate()}}</span>
				<span v-if="events[day.toDateString()] && events[day.toDateString()].length" class="events">
					<div v-for="event in events[day.toDateString()]" :key="event.id" 
					:style="{'background-color': event.color}" class="fhc-entry" :selected="event == selectedEvent"
					v-contrast @click.stop="selectDay(day,event)">
						<slot  name="monthPage" :event="event" :day="day" >
							<p>this is a placeholder which means that no template was passed to the Calendar Page slot</p>
						</slot>
					</div>
				</span>
			</a>
		</template>
	</div>
`
}
