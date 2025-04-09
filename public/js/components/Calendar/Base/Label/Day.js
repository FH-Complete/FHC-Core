import CalendarDate from '../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDay",
	directives: {
		CalClick
	},
	inject: {
		locale: "locale"
	},
	props: {
		timestamp: Number
	},
	computed: {
		day() {
			return new Date(this.timestamp);
		},
		titleFull() {
			return CalendarDate.format(this.day, {day: 'numeric', month: 'long', year: 'numeric'}, this.locale);
		},
		titleLong() {
			return CalendarDate.format(this.day, {day: '2-digit', month: '2-digit', year: 'numeric'}, this.locale);
		},
		titleShort() {
			return CalendarDate.format(this.day, {day: 'numeric', month: 'numeric'}, this.locale);
		},
		titleNarrow() {
			return CalendarDate.format(this.day, {day: 'numeric'}, this.locale);
		}
	},
	template: `
	<div
		class="fhc-calendar-base-label-day"
		v-cal-click:day="timestamp"
	>
		<div class="text-center">
			<span class="full">{{ titleFull }}</span>
			<span class="long">{{ titleLong }}</span>
			<span class="short">{{ titleShort }}</span>
			<span class="narrow">{{ titleNarrow }}</span>
		</div>
	</div>
	`
}
