import CalendarDate from '../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDow",
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
		titleLong() {
			return CalendarDate.format(this.day, {weekday: 'long'}, this.locale);
		},
		titleShort() {
			return CalendarDate.format(this.day, {weekday: 'short'}, this.locale);
		},
		titleNarrow() {
			return CalendarDate.format(this.day, {weekday: 'narrow'}, this.locale);
		}
	},
	template: `
	<div
		class="fhc-calendar-base-label-dow"
		v-cal-click:dow="timestamp"
	>
		<div class="text-center">
			<b class="long">{{ titleLong }}</b>
			<b class="short">{{ titleShort }}</b>
			<b class="narrow">{{ titleNarrow }}</b>
		</div>
	</div>
	`
}
