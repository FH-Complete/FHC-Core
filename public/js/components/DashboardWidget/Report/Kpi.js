import AbstractWidget from '../Abstract.js';
import ReportError from './Error/Error.js';
import ConfigKpi from './Config/Kpi.js';

import { useCalculatedVars } from '../../../composables/DashboardWidget/Report/CalculatedVars.js';

import ApiReport from '../../../api/factory/report.js';

import { compileLabel } from '../../../helpers/DashboardWidget/Report.js';

export default {
	name: "WidgetsReportKpi",
	components: {
		ReportError,
		ConfigKpi,
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
			calledVars: {},
			data: undefined,
			hasErrors: false,
		};
	},
	computed: {
		activeLabel() {
			if (!this.activeAggregator.label)
				return '';

			return compileLabel(
				this.activeAggregator.label,
				this.calledVars,
				this.details
			);
		},
		activeAggregator() {
			if (this.config.aggregators.length == 1)
				return this.config.aggregators[0];
			
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
						(sum, key) => sum + (Number(curr[key]) || 0),
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
	watch: {
		config: {
			handler() { this.fetchData(); },
			immediate: true,
			deep: true,
		}
	},
	methods: {
		async fetchData() {
			if (this.configMode)
				return;

			this.hasErrors = false;

			const vars = await this.getValuesForVars(this.config.vars, this.config.uservars, (key, detail) => {
				const sharedData = this.sharedData || {};
				sharedData[key] = detail;
				this.$emit('update:sharedData', sharedData);
				this.$emit('setConfig', Vue.markRaw(ConfigKpi));
			});

			this.calledVars = vars;

			try {
				const result = await this.$api.call(
					ApiReport.get(this.config.statistik_kurzbz, vars),
					{ errorHandling: false }
				);
				this.data = result.data;
			} catch(error) {
				if (this.activeAggregator === undefined)
					return this.hasErrors = true;
				
				const hasEmptyUserValues = Object.entries(this.config.vars)
					.some(([key, v]) => {
						if (v.type != 'user')
							return false;
						if (!this.config.uservars)
							return true;
						if (!this.config.uservars[key])
							return true;
						return false;
					});
				if (hasEmptyUserValues)
					return this.hasErrors = true;

				if (error.response.data.errors)
					return this.hasErrors = error.response.data.errors;

				this.$fhcAlert.handleSystemError(error);
			}
		},
	},
	setup() {
		const { details, getValuesForVars } = useCalculatedVars();
		return {
			getValuesForVars,
			details,
		};
	},
	mounted() {
		if (!this.adminMode) {
			if (Object.values(this.config.vars).some(v => v.type == 'user'))
				this.$emit('setConfig', Vue.markRaw(ConfigKpi));
			else if (this.config.aggregators.length > 1)
				this.$emit('setConfig', Vue.markRaw(ConfigKpi));
		}
	},
	template: /*html*/ `
	<div class="widgets-report-kpi w-100 h-100 d-flex flex-column justify-content-center align-items-center">
		<report-error v-if="hasErrors" :errors="hasErrors" />
		<template v-else-if="kpi !== false">
			<div class="h1 text-center">{{ kpi }}</div>
			<small class="text-center">{{ activeLabel }}</small>
		</template>
		<i v-else class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>
	`,
};
