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
			return this.focusDate.format({ year: 'numeric' }) + ' KW ' + this.focusDate.w;
		}
	},
	methods: {
		paneChanged(dir) {
			let previousDate = new CalendarDate(this.focusDate);
			this.focusDate.d += dir;
			this.emitRangeChanged(previousDate);
		},
		emitRangeChanged(previousDate) {
			this.$emit('change:range', { start: previousDate, end:this.focusDate });
		},
		prev() {
			this.$refs.pane.prev();
		},
		next() {
			this.$refs.pane.next();
		},
		selectEvent(event) {
			this.$emit('input', ['select:event', event]);
		}
	},
	created() {
		this.emitRangeChanged();
	},
	template: /*html*/`
	<div class="fhc-calendar-day">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="$emit('updateMode', 'week')"/>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-day-page :active="slot.active" :year="focusDate.y" :week="focusDate.w+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectEvent" >
				<template #dayPage="{event,day}">
					<slot name="dayPage" :event="event" :day="day" ></slot>
				</template>
			</calendar-day-page>
		</calendar-pane>
	</div>`
}
