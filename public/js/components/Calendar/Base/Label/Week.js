import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelWeek",
	directives: {
		CalClick
	},
	props: {
		date: {
			type: luxon.DateTime,
			required: true
		}
	},
	computed: {
		weeks() {
			const firstDay = this.date.startOf('week', { useLocaleWeeks: true });
			const lastDay = this.date.endOf('week', { useLocaleWeeks: true });

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
