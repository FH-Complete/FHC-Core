import AbstractWidget from '../../Abstract.js';
import VarsVar from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars/Var.js';
import FormInput from '../../../Form/Input.js';

import { compileLabel } from '../../../../helpers/DashboardWidget/Report.js';

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
		aggregatorLabels() {
			let calledVars = this.shared?.calledVars
				? { ...this.shared.calledVars }
				: {};
			for (const key in calledVars) // Overwrite with current value
				if (this.customVars[key] && this.customVars[key].modelValue.hasOwnProperty('value'))
					calledVars[key] = this.customVars[key].modelValue.value;
			const details = this.shared?.details || {};
			return this.config.aggregators.map(agg => compileLabel(agg.label, calledVars, details));
		},
		customVars() {
			const uservars = this.config.uservars || {};

			return Object.entries(this.config.vars).reduce((res, [key, variable]) => {
				if (variable.type == 'user') {
					res[key] = {
						modelValue: uservars[key] || {},
						detail: variable.detail,
					};
				} else if (this.sharedData?.customizableCalcVars && this.sharedData.customizableCalcVars[key]) {
					res[key] = {
						modelValue: uservars[key] || {},
						detail: this.sharedData.customizableCalcVars[key],
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
	<div class="widgets-report-config-kpi">
		<div v-if="hasCustomVars" :class="config.aggregators.length > 1 ? 'mb-3' : ''">
			<label class="form-label">{{ $p.t('dashboard/widget_report_vars') }}</label>
			<vars-var
				v-for="(variable, key) in customVars"
				:key="key"
				v-bind="variable"
				no-type
				@update:model-value="updateUserVar(key, $event)"
			/>
		</div>
		<form-input
			v-if="config.aggregators.length > 1"
			type="select"
			:label="$p.t('dashboard/widget_report_kpi_aggregator')"
			v-model="aggregator"
		>
			<option v-for="(aggregator, i) in aggregatorLabels" :key="i" :value="i">
				{{ aggregator }}
			</option>
		</form-input>
	</div>
	`,
};
