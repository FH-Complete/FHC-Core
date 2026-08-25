import ReportPicker from '../../../Dashboard/WidgetAdmin/Edit/Report/Picker.js';
import ReportVars from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars.js';
import ReportAggregators from '../../../Dashboard/WidgetAdmin/Edit/Report/Aggregators.js';

export default {
	name: "WidgetsGeneratorReportKpi",
	components:{
		ReportPicker,
		ReportVars,
		ReportAggregators,
	},
	props: {
		modelValue: Object,
	},
	emits: [
		"update:modelValue",
	],
	data() {
		return {
			details: null,
		};
	},
	computed: {
	},
	methods: {
	},
	created() {
		if (!this.modelValue.arguments.vars) {
			this.modelValue.arguments = {
				statistik_kurzbz: '',
				vars: {},
				aggregators: [],
			};
		}
	},
	template: /*html*/ `
	<div class="widgets-generator-report-kpi">
		<report-picker
			v-model="modelValue.arguments.statistik_kurzbz"
			v-model:details="details"
			class="mb-3"
		/>
		<template v-if="details">
			<report-vars
				v-model="modelValue.arguments.vars"
				:details="details"
				class="mb-3"
			/>
			<report-aggregators
				v-model="modelValue.arguments.aggregators"
				:details="details"
				class="mb-3"
			/>
		</template>
		<div v-else class="placeholder-glow mb-3">
			<span class="placeholder col-6"></span>
		</div>
	</div>
	`,
};
