import AbstractWidget from '../../Abstract.js';
import VarsVar from '../../../Dashboard/WidgetAdmin/Edit/Report/Vars/Var.js';
import FormInput from '../../../Form/Input.js';

export default {
	name: "WidgetsReportGraphSetup",
	components:{
		VarsVar,
		FormInput,
	},
	mixins: [ AbstractWidget ],
	computed: {
		filter: {
			get() {
				if (this.config.filter !== undefined)
					return this.config.filter;
				
				if (this.config.charts.length > 1)
					return Array.from(this.config.charts.keys());
				
				return [];
			},
			set(v) {
				if (v.length == this.config.charts.length)
					v = undefined;
				this.config.filter = v;
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
				} else if (this.sharedData && this.sharedData[key]) {
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
	<div class="widgets-report-config-graph">
		<div v-if="hasCustomVars" :class="config.charts.length > 1 ? 'mb-3' : ''">
			<label class="form-label">{{ $p.t('dashboard/widget_report_vars') }}</label>
			<vars-var
				v-for="(variable, key) in customVars"
				:key="key"
				v-bind="variable"
				no-type
				@update:model-value="updateUserVar(key, $event)"
			/>
		</div>
		<template v-if="config.charts.length > 1">
			<label class="form-label">{{ $p.t('dashboard/widget_report_graph_charts') }}</label>
			<div v-for="(chart, i) in config.charts">
				<form-input
					v-model="filter"
					type="checkbox"
					:label="chart.label || chart.field"
					name="filter"
					:value="i"
					:disabled="filter.length == 1 && filter.indexOf(i) != -1"
				/>
			</div>
		</template>
	</div>
	`,
};
