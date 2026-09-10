import AbstractWidget from '../Abstract.js';
import ConfigKpi from './Config/Kpi.js';

import { useCalculatedVars } from '../../../composables/DashboardWidget/Report/CalculatedVars.js';

import ApiReport from '../../../api/factory/report.js';
import ApiStudienjahr from '../../../api/factory/studienjahr.js';

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
			hasErrors: false,
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

			let vars = {};
			for (var key in this.config.vars) {
				vars[key] = await this.getValueForVar(this.config.vars[key]);
			}

			try {
				const result = await this.$api.call(
					ApiReport.get(this.config.statistik_kurzbz, vars),
					{ errorHandling: false }
				);
				this.data = result.data;
			} catch(error) {
				
				if (this.activeAggregator === undefined)
					return this.hasErrors = true;
				if (Object.values(this.config.vars).some(v => v.type == 'user' && v.value === undefined))
					return this.hasErrors = true;

				if (error.response.data.errors)
					return this.hasErrors = error.response.data.errors;

				this.$fhcAlert.handleSystemError(error);
			}
		},
	},
	setup() {
		const { getValueForVar } = useCalculatedVars();
		return {
			getValueForVar
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
	<div
		class="widgets-report-kpi w-100 h-100"
		:class="{
			'd-flex': !configMode,
			'flex-column': !configMode,
			'justify-content-center': !configMode,
			'align-items-center': !configMode
		}"
	>
		<div
			v-if="hasErrors === true"
			class="alert alert-danger m-0 h-100 w-100 border-0 rounded-0 d-flex justify-content-center align-items-center"
		>
			{{ $p.t('ui/errorConfigFehlt') }}
		</div>
		<template v-else-if="hasErrors">
			<template v-for="error in hasErrors" :key="error">
				<div v-if="error.message" class="alert alert-danger mx-1">
					{{ error.message }}
				</div>
				<template v-else-if="error.messages">
					<div v-for="msg in error.messages" :key="msg" class="alert alert-danger mx-1">
						{{ msg }}
					</div>
				</template>
			</template>
		</template>
		<template v-else-if="kpi !== false">
			<div class="h1 text-center">{{ kpi }}</div>
			<small>{{ activeAggregator.label }}</small>
		</template>
		<i v-else class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>
	`,
};
