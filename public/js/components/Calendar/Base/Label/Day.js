import CalendarDate from '../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDay",
	directives: {
		CalClick
	},
	inject: {
		locale: "locale",
		timezone: "timezone"
	},
	props: {
		timestamp: Number
	},
	computed: {
		day() {
			return luxon.DateTime.fromMillis(this.timestamp).setZone(this.timezone).setLocale(this.locale);
		},
		titleFull() {
			return this.day.toLocaleString({day: 'numeric', month: 'long', year: 'numeric'});
		},
		titleLong() {
			return this.day.toLocaleString({day: '2-digit', month: '2-digit', year: 'numeric'});
		},
		titleShort() {
			return this.day.toLocaleString({day: 'numeric', month: 'numeric'});
		},
		titleNarrow() {
			return this.day.toLocaleString({day: 'numeric'});
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
