import CalClick from '../../../../directives/Calendar/Click.js';

export default {
	name: "LabelDow",
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
		titleLong() {
			return this.date.toLocaleString({weekday: 'long'});
		},
		titleShort() {
			return this.date.toLocaleString({weekday: 'short'});
		},
		titleNarrow() {
			return this.date.toLocaleString({weekday: 'narrow'});
		}
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-label-dow"
		v-cal-click:dow="date"
	>
		<b class="long">{{ titleLong }}</b>
		<b class="short">{{ titleShort }}</b>
		<b class="narrow">{{ titleNarrow }}</b>
	</div>
	`
}
