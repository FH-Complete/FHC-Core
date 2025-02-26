import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarDayPage from './Day/Page.js';
import CalendarDate from '../../composables/CalendarDate.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarDayPage,
		CalendarPane
	},
	computed: {
		title() {
			return this.focusDate.wYear + ' KW ' + this.focusDate.w;
		}
	},
	methods: {
		paneChanged(dir) {
			let previousDate = new CalendarDate(this.focusDate);
			this.focusDate.d += dir;
			this.emitRangeChanged(previousDate);
		},
		emitRangeChanged(previousDate, mounted) {
			this.$emit('change:range', { start: previousDate, end:this.focusDate });
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: 0, m: 0, d: -1 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 0, m: 0, d: 1 });
		},
		selectEvent(event) {
			this.$emit('input', ['select:event', event]);
		}
	},
	mounted() {
		this.emitRangeChanged(new CalendarDate(this.focusDate.y, this.focusDate.m, this.focusDate.d -1), true);
	},
	template: /*html*/`
	<div class="fhc-calendar-day">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="$emit('updateMode', 'week')">
			<template #calendarDownloads>
				<slot name="calendarDownloads"></slot>
			</template>
		</calendar-header>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-day-page :active="slot.active" :year="focusDate.y" :week="focusDate.w+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectEvent" >
				<template #dayPage="{event,day,mobile}">
					<slot name="dayPage" :event="event" :day="day" :mobile="mobile" ></slot>
				</template>
				<template #pageMobilContent="{lvMenu, event}">
					<slot name="pageMobilContent" :lvMenu="lvMenu" :event="event" ></slot>
				</template>
			</calendar-day-page>
		</calendar-pane>
	</div>`
}
