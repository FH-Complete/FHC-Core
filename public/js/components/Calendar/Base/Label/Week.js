import CalendarDate from '../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelWeek",
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
		weeks() {
			const someDay = luxon.DateTime.fromMillis(this.timestamp).setZone(this.timezone).setLocale(this.locale);
			const firstDay = someDay.startOf('week');
			const lastDay = someDay.endOf('week');
			const weeks = [
				{ number: firstDay.localWeekNumber, year: firstDay.localWeekYear },
				{ number: lastDay.localWeekNumber, year: lastDay.localWeekYear }
			];
			if (weeks[0].number == weeks[1].number)
				weeks.pop();
			return weeks;
		}
	},
	template: `
	<div class="fhc-calendar-base-label-week">
		<span
			v-for="week in weeks"
			v-cal-click:week="week"
		>
			{{ week.number }}
		</span>
	</div>
	`
}
