import EditSetup from '../../../Dashboard/WidgetAdmin/Edit/Setup.js';
import ReportPicker from '../../../Dashboard/WidgetAdmin/Edit/Report/Picker.js';
import ReportVars from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars.js';
import ReportAggregators from '../../../Dashboard/WidgetAdmin/Edit/Report/Aggregators.js';
import BsConfirm from "../../../Bootstrap/Confirm.js";

import ApiReport from '../../../../api/factory/report.js';

export default {
	name: "WidgetsGeneratorReportKpi",
	components: {
		EditSetup,
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
		attemptReportChange(value) {
			if (this.modelValue.arguments.statistik_kurzbz == value)
				return;
			
			const hasVars = Object.entries(this.modelValue.arguments.vars).length;
			const hasAggregators = this.modelValue.arguments.aggregators.length;

			if (hasVars || hasAggregators) {
				BsConfirm
					.popup('change') // TODO(chris): phrase
					.then(() => {
						this.modelValue.arguments.statistik_kurzbz = value;
						this.modelValue.arguments.aggregators = [];
						this.loadDetails();
					})
					.catch(() => {
						this.$refs.picker.initSelectedValue();
					});
			} else {
				this.modelValue.arguments.statistik_kurzbz = value;
				this.loadDetails();
			}
		},
		loadDetails() {
			this.details = null;
			this.$api
				.call(ApiReport.vars(this.modelValue.arguments.statistik_kurzbz))
				.then(result => {
					const allowedKeys = result.data.map(detail => detail.kurzbz);
					if (!this.modelValue.arguments.vars)
						this.modelValue.arguments.vars = {};
					else
						for (var key in this.modelValue.arguments.vars) {
							if (!allowedKeys.includes(key))
								delete this.modelValue.arguments.vars[key];
						};
					result.data.forEach(detail => {
						if (!this.modelValue.arguments.vars[detail.kurzbz])
							this.modelValue.arguments.vars[detail.kurzbz] = { type: 'fix' };
					});

					// TODO(chris): emit update:modelValue
					this.details = result.data;
				})
				.catch(this.$fhcAlert.handleSystemErrors)
		},
	},
	created() {
		if (this.modelValue.arguments.statistik_kurzbz)
			this.loadDetails(this.modelValue.arguments.statistik_kurzbz);
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
		<edit-setup
			v-model="modelValue.setup"
			edit-name
			edit-size
			edit-hide-footer
		/>
		<report-picker
			ref="picker"
			:model-value="modelValue.arguments.statistik_kurzbz"
			class="mb-3"
			@update:model-value="attemptReportChange"
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
