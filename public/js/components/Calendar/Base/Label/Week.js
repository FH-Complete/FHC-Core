import CalendarDate from '../../../../../helpers/Calendar/Date.js';

import CalClick from '../../../../../directives/Calendar/Click.js';

export default {
	name: "LabelWeek",
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
		weeks() {
			const firstDay = new Date(this.timestamp);
			const lastDay = CalendarDate.addDays(firstDay, 6);
			const weeks = [
				CalendarDate.getWeek(firstDay, this.locale),
				CalendarDate.getWeek(lastDay, this.locale)
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
