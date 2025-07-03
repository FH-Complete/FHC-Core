import CalendarDate from '../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDow",
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
		titleLong() {
			return this.day.toLocaleString({weekday: 'long'});
		},
		titleShort() {
			return this.day.toLocaleString({weekday: 'short'});
		},
		titleNarrow() {
			return this.day.toLocaleString({weekday: 'narrow'});
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
