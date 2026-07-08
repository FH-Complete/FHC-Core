import AbstractWidget from './Abstract.js';
import FhcCalendar from '../Calendar/Widget.js';

import ApiLvPlan from '../../api/factory/lvPlan.js';

export default {
	name: "LvPlanWidget",
	components: {
		FhcCalendar
	},
	mixins: [
		AbstractWidget
	],
	methods: {
		getPromiseFunc(start, end) {
			return [
				this.$api.call(ApiLvPlan.LvPlanEvents(start.toISODate(), end.toISODate())),
				this.$api.call(ApiLvPlan.getLvPlanReservierungen(start.toISODate(), end.toISODate()))
			];
		}
	},
	created() {
		this.$emit('setConfig', false);
	},
	template: /*html*/`
	<div class="dashboard-widget-lvplan d-flex flex-column h-100">
		<fhc-calendar :get-promise-func="getPromiseFunc" />
	</div>`
}