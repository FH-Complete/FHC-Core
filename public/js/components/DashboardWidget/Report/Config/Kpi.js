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
		hasCustomVars() {
			return Object.values(this.config.vars).some(v => v.type == 'user');
		},
	},
	template: /*html*/ `
	<div class="widgets-report-kpi-config-kpi">
		<template v-if="hasCustomVars">
			// TODO(chris): label: vars
			<template
				v-for="(variable, key) in config.vars"
				:key="key"
			>
				<vars-var
					v-if="variable.type == 'user'"
					v-model="variable"
					:detail="variable.detail"
					no-type
				/>
				// TODO(chris): type == 'calc'?
			</template>
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
