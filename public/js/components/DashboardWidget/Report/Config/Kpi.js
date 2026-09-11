import AbstractWidget from '../../Abstract.js';
import VarsVar from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars/Var.js';
import FormInput from '../../../Form/Input.js';

export default {
	name: "WidgetsReportKpiSetup",
	components:{
		VarsVar,
		FormInput,
	},
	mixins: [ AbstractWidget ],
	computed: {
		aggregator: {
			get() {
				if (this.config.aggregator !== undefined)
					return this.config.aggregator;
				
				if (this.config.aggregators.length == 1)
					return this.config.aggregators[0];

				if (this.config.aggregators.length > 1) {
					const def = this.config.aggregators.findIndex(agg => agg.default);
					if (def > 0)
						return def;
				}
				
				return 0;
			},
			set(v) {
				this.config.aggregator = v;
			}
		},
		customVars() {
			const uservars = this.config.uservars || {};

			return Object.entries(this.config.vars).reduce((res, [key, variable]) => {
				if (variable.type == 'user') {
					res[key] = {
						modelValue: uservars[key] || {},
						detail: variable.detail,
					};
				} else if (this.sharedData[key]) {
					res[key] = {
						modelValue: uservars[key] || {},
						detail: this.sharedData[key],
					};
				}
				return res;
			}, {});
		},
		hasCustomVars() {
			return Object.keys(this.customVars).length;
		},
	},
	methods: {
		updateUserVar(key, { value }) {
			if (!this.config.uservars)
				this.config.uservars = {};
			this.config.uservars[key] = { value };
		},
	},
	template: /*html*/ `
	<div class="widgets-report-kpi-config-kpi">
		<template v-if="hasCustomVars">
			// TODO(chris): label: vars
			<vars-var
				v-for="(variable, key) in customVars"
				:key="key"
				v-bind="variable"
				no-type
				@update:model-value="updateUserVar(key, $event)"
			/>
		</template>
		<form-input
			v-if="config.aggregators.length > 1"
			type="select"
			:label="$p.t('dashboard/widget_report_kpi_aggregator')"
			v-model="aggregator"
		>
			<option v-for="(aggregator, i) in config.aggregators" :key="i" :value="i">
				{{ aggregator.label }}
			</option>
		</form-input>
	</div>
	`,
};
