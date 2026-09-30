import EditSetup from '../../../Dashboard/WidgetAdmin/Edit/Setup.js';
import EditPermission from '../../../Dashboard/WidgetAdmin/Edit/Permission.js';
import ReportPicker from '../../../Dashboard/WidgetAdmin/Edit/Report/Picker.js';
import ReportVars from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars.js';
import ReportAxis from '../../../Dashboard/WidgetAdmin/Edit/Report/Axis.js';
import ReportCharts from '../../../Dashboard/WidgetAdmin/Edit/Report/Charts.js';
import FormInput from '../../../Form/Input.js';
import BsConfirm from "../../../Bootstrap/Confirm.js";

import ApiReport from '../../../../api/factory/report.js';

export default {
	name: "WidgetsGeneratorReportGraph",
	components: {
		EditSetup,
		EditPermission,
		ReportPicker,
		ReportVars,
		ReportAxis,
		ReportCharts,
		FormInput,
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
		getTitleFromPicker() {
			const statistik_kurzbz = this.modelValue.arguments.statistik_kurzbz;
			
			let currentItem = this.$refs.picker.allItems
				.find(item => item.statistik_kurzbz == statistik_kurzbz);

			return currentItem?.bezeichnung || statistik_kurzbz;
		},
		attemptReportChange(value) {
			if (this.modelValue.arguments.statistik_kurzbz == value)
				return;
			
			const hasVars = Object.entries(this.modelValue.arguments.vars).length;
			const hasGraphoptions = this.modelValue.arguments.charts.length;
			const hasDefaultTitle = this.modelValue.arguments.title == this.getTitleFromPicker();

			if (hasVars || hasGraphoptions) {
				BsConfirm
					.popup(this.$p.t('dashboard/widget_report_statistik_change'))
					.then(() => {
						this.modelValue.arguments.statistik_kurzbz = value;
						this.modelValue.arguments.charts = [];
						if (hasDefaultTitle || this.modelValue.arguments.title == '')
							this.modelValue.arguments.title = this.getTitleFromPicker();
						this.loadDetails();
					})
					.catch(() => {
						this.$refs.picker.initSelectedValue();
					});
			} else {
				this.modelValue.arguments.statistik_kurzbz = value;
				if (hasDefaultTitle || this.modelValue.arguments.title == '')
					this.modelValue.arguments.title = this.getTitleFromPicker();
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
					icon: '/public/images/widgets/Graph_Widget.svg',
					name: '',
					hideFooter: false,
				},
				arguments: {
					title: '',
					statistik_kurzbz: '',
					vars: {},
					xAxis: {
						field: '',
						title: '',
					},
					charts: [],
				},
				berechtigung_kurzbz: null,
			});
		}
	},
	template: /*html*/ `
	<div class="widgets-generator-report-graph">
		<edit-setup
			v-model="modelValue.setup"
			class="border-bottom mb-3"
			edit-name
			edit-size
			edit-hide-footer
		/>
		<form-input
			v-model="modelValue.arguments.title"
			:label="$p.t('global/titel')"
			class="mb-3"
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
			<report-axis
				v-model="modelValue.arguments.xAxis"
				class="mb-3"
			/>
			<report-charts
				v-model="modelValue.arguments.charts"
				class="mb-3"
			/>
		</template>
		<div
			v-else-if="modelValue?.arguments?.statistik_kurzbz"
			class="placeholder-glow mb-3"
		>
			<span class="placeholder col-6"></span>
		</div>
		<edit-permission v-model="modelValue.berechtigung_kurzbz" disabled />
	</div>
	`,
};
