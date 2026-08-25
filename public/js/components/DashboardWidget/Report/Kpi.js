import AbstractWidget from '../Abstract.js';
import ConfigKpi from './Config/Kpi.js';

import ApiReport from '../../../api/factory/report.js';

export default {
	name: "WidgetsReportKpi",
	components:{
		ConfigKpi
	},
	mixins: [ AbstractWidget ],
	inject: {
		adminMode: {
			from: 'adminMode',
			default: false
		}
	},
	data() {
		return {
			data: undefined,
		};
	},
	computed: {
		activeAggregator() {
			if (this.config.aggregators.length == 1)
				return this.config.aggregators[0];
			// TODO(chris): by name not index??
			if (this.config.aggregator !== undefined)
				return this.config.aggregators[this.config.aggregator];
			return this.config.aggregators.find(agg => agg.default);
		},
		filteredData() {
			if (!this.data)
				return null;
			if (!this.activeAggregator)
				return null;
			if (this.activeAggregator.type == 'count')
				return this.data.length;
			if (this.activeAggregator.type == 'sum') {
				const fields = this.activeAggregator.fields.split(',');
				return this.data.reduce(
					(res, curr) => res + fields.reduce(
						(sum, key) => sum + (curr[key] || 0),
						0
					),
					0
				);
			}
		},
		kpi() {
			if (this.adminMode)
				return '?';
			if (this.filteredData !== null)
				return this.filteredData;
			return false;
		}
	},
	methods: {
	},
	mounted() {
		if (!this.adminMode) {
			if (Object.values(this.config.vars).some(v => v.type == 'user'))
				this.$emit('setConfig', true);
			else if (this.config.aggregators.length > 1)
				this.$emit('setConfig', true);

			const vars = Object.fromEntries(Object.entries(this.config.vars).map(([key, { value }]) => [key, value]));
			// TODO(chris): calculated stuff??
			this.$api
				.call(ApiReport.get(this.config.statistik_kurzbz, vars))
				.then(result => {
					this.data = result.data;
				})
				// TODO(chris): handle some errors inside
				.catch(this.$fhcAlert.handleSystemErrors);
		}
	},
	template: /*html*/ `
	<div
		class="widgets-report-kpi w-100 h-100"
		:class="{
			'd-flex': !configMode,
			'flex-column': !configMode,
			'justify-content-center': !configMode,
			'align-items-center': !configMode
		}"
	>
		<template v-if="configMode">
			<config-kpi :config="config" />
		</template>
		<template v-else-if="kpi">
			<div class="h1 text-center">{{ kpi }}</div>
			<small>{{ activeAggregator.label }}</small>
		</template>
		<i v-else class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>
	`,
};
