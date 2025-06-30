export default {
	name: "CalendarHeaderDatepicker",
	components: {
		VueDatePicker
	},
	inject: [
		"locale",
		"timezone"
	],
	props: {
		date: luxon.DateTime,
		view: String
	},
	emits: [
		"update:date"
	],
	computed: {
		convertedDate() {
			// convert to target TZ then strip TZ Information
			// so the datepicker can work with local times
			return this.date.setZone(this.timezone).setZone('local', { keepLocalTime: true });
		},
		current() {
			switch (this.view) {
			case "month":
				return {month: this.convertedDate.month-1, year: this.convertedDate.year};
			case "week":
				return [this.convertedDate.startOf('week', true).toMillis(), this.convertedDate.endOf('week', true).toMillis()];
			case "day":
				return this.convertedDate;
			default:
				return null;
			}
		},
		format() {
			switch (this.view) {
			case "month":
				return "MMMM yyyy"
			case "week":
				return "yyyy 'KW' ww";
			case "day":
				return "dd.MM.yyyy"
			default:
				return "'View not Supported'";
			}
		}
	},
	methods: {
		update(value) {
			let date;
			switch (this.view) {
			case "month":
				value.month++;
				date = luxon.DateTime.fromObject(value).setZone(this.timezone, { keepLocalTime: true });
				break;
			case "week":
				date = luxon.DateTime.fromJSDate(value[0]).setZone(this.timezone, { keepLocalTime: true });
				break;
			case "day":
				date = luxon.DateTime.fromJSDate(value).setZone(this.timezone, { keepLocalTime: true });
				break;
			default:
				return; // Don't update if the value is invalid!
			}
			this.$emit("update:date", date);
		}
	},
	template: /* html */`
	<vue-date-picker
		:model-value="current"
		@update:model-value="update"
		:format="format"
		:month-picker="view == 'month'"
		:week-picker="view == 'week'"
		:text-input="view == 'day'"
		:week-numbers="{ type: 'iso' }"
		:clearable="false"
		:enable-time-picker="false"
		:config="{ keepActionRow: view != 'month' }"
		:action-row="{ showSelect: false, showCancel: false, showNow: view != 'month', showPreview: false }"
		auto-apply
		six-weeks
		teleport
		:locale="locale"
	/>
	`
}
