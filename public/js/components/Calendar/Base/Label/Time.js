export default {
	name: "LabelTime",
	props: {
		part: {
			type: [luxon.Duration, Number, Object],
			required: true,
			validator(value) {
				if (value instanceof Object) {
					if (value instanceof luxon.Duration)
						return true;
					let start_ok = true;
					let end_ok = true;
					if (value.start) {
						start_ok = (
							value.start instanceof luxon.Duration
							|| Number.isInteger(value.start)
						);
					}
					if (value.end) {
						end_ok = (
							value.end instanceof luxon.Duration
							|| Number.isInteger(value.end)
						);
					}
					return start_ok && end_ok;
				}
				return true;
			}
		}
	},
	computed: {
		sanitizedTimestamps() {
			return this.part.start || this.part.end ? this.part : { start: this.part };
		},
		start() {
			if (!this.sanitizedTimestamps.start)
				return null;
			return this.formatTime(this.sanitizedTimestamps.start);
		},
		end() {
			if (!this.sanitizedTimestamps.end)
				return null;
			return this.formatTime(this.sanitizedTimestamps.end);
		}
	},
	methods: {
		formatTime(date) {
			return date.toISOTime({ suppressSeconds: true });
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
