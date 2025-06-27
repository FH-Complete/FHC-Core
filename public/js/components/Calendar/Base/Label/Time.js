import CalendarDate from '../../../../helpers/Calendar/Date.js';

export default {
	name: "LabelTime",
	inject: {
		locale: "locale"
	},
	props: {
		part: [Number, Object],
		axisMain: Array
	},
	computed: {
		sanitizedTimestamps() {
			return this.part.start || this.part.end ? this.part : { start: this.part };
		},
		start() {
			if (!this.sanitizedTimestamps.start)
				return null;
			return this.formatTime(new Date(this.axisMain[0] + this.sanitizedTimestamps.start));
		},
		end() {
			if (!this.sanitizedTimestamps.end)
				return null;
			return this.formatTime(new Date(this.axisMain[0] + this.sanitizedTimestamps.end));
		}
	},
	methods: {
		formatTime(date) {
			return CalendarDate.format(
				date,
				{ timeZone: 'Europe/Vienna', hour: '2-digit', minute: '2-digit' },
				this.locale
			);
		}
	},
	template: `
	<div class="fhc-calendar-base-label-time">
		<span v-if="start">{{ start }}</span>
		<span v-if="end">-</span>
		<span v-if="end">{{ end }}</span>
	</div>
	`
}
