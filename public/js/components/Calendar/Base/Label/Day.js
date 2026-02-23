import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDay",
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
		titleFull() {
			return this.date.toLocaleString({day: 'numeric', month: 'long', year: 'numeric'});
		},
		titleLong() {
			return this.date.toLocaleString({day: '2-digit', month: '2-digit', year: 'numeric'});
		},
		titleShort() {
			return this.date.toLocaleString({day: 'numeric', month: 'numeric'});
		},
		titleNarrow() {
			return this.date.toLocaleString({day: 'numeric'});
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-label-day"
		v-cal-click:day="date"
	>
		<span class="full">{{ titleFull }}</span>
		<span class="long">{{ titleLong }}</span>
		<span class="short">{{ titleShort }}</span>
		<span class="narrow">{{ titleNarrow }}</span>
	</div>
	`
}
