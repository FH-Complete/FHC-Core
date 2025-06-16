import CalendarAbstract from './Abstract.js';
import CalendarPane from './Pane.js';
import CalendarMonthPage from './Month/Page.js';

export default {
	mixins: [
		CalendarAbstract
	],
	components: {
		CalendarMonthPage,
		CalendarPane
	},
	data() {
		return {
			syncOnNextChange: false
		}
	},
	computed: {
		title() {
			return this.focusDate.format({month: ['short','long','long','long'][this.size], year: 'numeric'}, this.$p.user_locale.value);
		}
	},
	methods: {
		paneChanged(dir) {
			if (this.syncOnNextChange) {
				this.syncOnNextChange = false;
				this.focusDate.set(this.date);
			} else {
				this.focusDate.moveMonthInDirection(dir)
			}
			this.$emit('change:range', {
				start: new Date(this.focusDate.y, this.focusDate.m, 1), 
				end: new Date(this.focusDate.y, this.focusDate.m+1, 0)
			});
		},
		prev() {
			this.$refs.pane.prev();
			this.$emit('change:offset', { y: 0, m: -1, d: 0 });
		},
		next() {
			this.$refs.pane.next();
			this.$emit('change:offset', { y: 0, m: 1, d: 0 });
		},
		selectDay(day) {
			let m = day.getMonth();
			if (this.focusDate.m != m) {
				this.syncOnNextChange = true;
				if (this.focusDate.m-1 == m || (m == 11 && !this.focusDate.m))
					this.$refs.pane.prev();
				else
					this.$refs.pane.next();
			} else {
				this.focusDate.set(this.date);
			}
			this.$emit('input', ['select:day',day])
		}
	},
	created() {
		this.$emit('change:range', {
			start: new Date(this.focusDate.y, this.focusDate.m, 1), 
			end: new Date(this.focusDate.y, this.focusDate.m+1, 0)
		});
	},
	template: `
	<div class="fhc-calendar-month">
		<calendar-header :title="title" @prev="prev" @next="next" @updateMode="$emit('updateMode', $event)" @click="$emit('updateMode', 'months')" >
			<template #calendarDownloads>
				<slot name="calendarDownloads"></slot>
			</template>
		</calendar-header>
		<calendar-pane ref="pane" v-slot="slot" @slid="paneChanged">
			<calendar-month-page :year="focusDate.y" :month="focusDate.m+slot.offset" @updateMode="$emit('updateMode', $event)" @page:back="prev" @page:forward="next" @input="selectDay" >
				<template #monthPage="{event,day}">
					<slot name="monthPage" :event="event" :day="day" ></slot>
				</template>
			</calendar-month-page>
		</calendar-pane>
	</div>`
}
