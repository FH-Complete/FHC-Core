import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarWeekPage from './Week/Page.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarWeekPage,
		CalendarPane
	},
	computed: {
		title() {
			return this.focusDate.wYear + ' KW ' + this.focusDate.w;
		}
	},
	methods: {
		paneChanged(dir) {
			this.focusDate.d += dir * 7;
			this.emitRangeChanged();
		},
		emitRangeChanged() {
			let start = this.focusDate.firstDayOfWeek;
			let end = this.focusDate.lastDayOfWeek;
			this.$emit('change:range', { start, end });
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: 0, m: 0, d: -7 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 0, m: 0, d: 7 });
		},
		selectEvent(event) {
			this.$emit('input', ['select:event',event]);
		}
	},
	created() {
		this.emitRangeChanged();
	},
	template: /*html*/`
	<div class="fhc-calendar-week">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="$emit('updateMode', 'weeks')">
			<template #calendarDownloads>
				<slot name="calendarDownloads"></slot>
			</template>
		</calendar-header>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-week-page :year="focusDate.wYear" :week="focusDate.w+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectEvent" >
				<template #weekPage="{event,day}">
					<slot name="weekPage" :event="event" :day="day" ></slot>
				</template>
			</calendar-week-page>
		</calendar-pane>
	</div>`
}
