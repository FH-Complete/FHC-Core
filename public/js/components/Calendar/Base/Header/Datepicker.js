// TODO(chris): translate aria-labels

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
		date: {
			type: luxon.DateTime,
			required: true
		},
		mode: {
			type: String,
			required: true
		},
		listLength: {
			type: Number,
			default: 7
		}
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
			switch (this.mode) {
			case "month":
				return {month: this.convertedDate.month-1, year: this.convertedDate.year};
			case "list":
				return [this.convertedDate.startOf('day').ts, this.convertedDate.startOf('day').plus({ days: this.listLength }).ts - 1];
			case "week":
				return [this.convertedDate.startOf('week', { useLocaleWeeks: true }).ts, this.convertedDate.endOf('week', { useLocaleWeeks: true }).ts];
			case "day":
				return this.convertedDate;
			default:
				return null;
			}
		},
		title() {
			switch (this.mode) {
			case "month":
				return this.date.toLocaleString({ month: 'long', year: 'numeric' });
			case "week":
				const year = this.date.localWeekYear;
				const week = this.date.toFormat('nn');
				return this.$p.t('calendar/year_kw', { year, week });
			case "list":
			case "day":
				return this.date.toLocaleString(luxon.DateTime.DATE_FULL);
			default:
				return 'View not Supported';
			}
		},
		format() {
			const title = this.title;
			return `'${title}'`;
		},
		weekStart() {
			return luxon.Info.getStartOfWeek(this.date)%7;
		}
	},
	methods: {
		update(value) {
			let date;
			switch (this.mode) {
			case "month":
				value.month++;
				date = luxon.DateTime.fromObject(value).setZone(this.timezone, { keepLocalTime: true }).setLocale(this.locale);
				break;
			case "list":
			case "week":
				date = luxon.DateTime.fromJSDate(value[0]).setZone(this.timezone, { keepLocalTime: true }).setLocale(this.locale);
				break;
			case "day":
				date = luxon.DateTime.fromJSDate(value).setZone(this.timezone, { keepLocalTime: true }).setLocale(this.locale);
				break;
			default:
				return; // Don't update if the value is invalid!
			}
			this.$emit("update:date", date);
		},
		weekNumbers(date) {
			return luxon.DateTime.fromJSDate(date, { locale: this.locale }).localWeekNumber;
		}
	},
	template: /* html */`
	<vue-date-picker
		:model-value="current"
		@update:model-value="update"
		:format="format"
		:month-picker="mode == 'month'"
		:week-picker="mode == 'week'"
		:range="mode == 'list' ? { autoRange: listLength } : false"
		:text-input="mode == 'day'"
		:week-start="weekStart"
		:week-numbers="{ type: weekNumbers }"
		:clearable="false"
		:enable-time-picker="false"
		:config="{ keepActionRow: mode != 'month' }"
		:action-row="{ showSelect: false, showCancel: false, showNow: mode != 'month', showPreview: false }"
		auto-apply
		six-weeks
		teleport
		:locale="locale"
		:now-button-label="$p.t('calendar/today')"
		:week-num-name="$p.t('calendar/kw')"
	/>
	`
}
