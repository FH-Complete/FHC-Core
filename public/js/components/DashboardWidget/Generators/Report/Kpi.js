import EditSetup from '../../../Dashboard/WidgetAdmin/Edit/Setup.js';
import EditPermission from '../../../Dashboard/WidgetAdmin/Edit/Permission.js';
import ReportPicker from '../../../Dashboard/WidgetAdmin/Edit/Report/Picker.js';
import ReportVars from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars.js';
import ReportAggregators from '../../../Dashboard/WidgetAdmin/Edit/Report/Aggregators.js';
import BsConfirm from "../../../Bootstrap/Confirm.js";

import ApiReport from '../../../../api/factory/report.js';

export default {
	name: "WidgetsGeneratorReportKpi",
	components: {
		EditSetup,
		EditPermission,
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
	methods: {
		attemptReportChange(value) {
			if (this.modelValue.arguments.statistik_kurzbz == value)
				return;
			
			const hasVars = Object.entries(this.modelValue.arguments.vars).length;
			const hasAggregators = this.modelValue.arguments.aggregators.length;

			if (hasVars || hasAggregators) {
				BsConfirm
					.popup($p.t('dashboard/widget_report_statistik_change'))
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

					if (result.meta?.berechtigung_kurzbz)
						this.modelValue.berechtigung_kurzbz = result.meta.berechtigung_kurzbz;

					this.details = result.data;
				})
				.catch(this.$fhcAlert.handleSystemErrors)
		},
	},
	created() {
		if (this.modelValue.arguments.statistik_kurzbz)
			this.loadDetails(this.modelValue.arguments.statistik_kurzbz);
		
		if (!this.modelValue.arguments.vars) {
			this.$emit('update:modelValue', {
				...this.modelValue,
				setup: {
					...this.modelValue.setup,
					icon: '',
					name: '',
					width: 1,
					height: 1,
					hideFooter: true,
				},
				arguments: {
					statistik_kurzbz: '',
					vars: {},
					aggregators: [],
				},
				berechtigung_kurzbz: null,
			});
		}
	},
	template: /*html*/ `
	<div class="widgets-generator-report-kpi">
		<edit-setup
			v-model="modelValue.setup"
			class="border-bottom mb-3"
			edit-name
			edit-size
			edit-hide-footer
		/>
		<report-picker
			ref="picker"
			:model-value="modelValue.arguments.statistik_kurzbz || ''"
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
		<edit-permission v-model="modelValue.berechtigung_kurzbz" disabled />
	</div>
	`,
};
