import AbstractWidget from '../Abstract.js';
import ReportError from './Error/Error.js';
import ConfigGraph from './Config/Graph.js';
import FhcChart from '../../Chart/FhcChart.js';

import { useCalculatedVars } from '../../../composables/DashboardWidget/Report/CalculatedVars.js';

import ApiReport from '../../../api/factory/report.js';

export default {
	name: "WidgetsReportGraph",
	components: {
		ReportError,
		ConfigGraph,
		FhcChart,
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
		filteredCharts() {
			if (!this.config.filter)
				return this.config.charts;
			return this.config.charts.filter((_data, index) => this.config.filter.includes(index));
		},
		filteredData() {
			if (!this.data)
				return null;
			
			let result = {
				title: {
					text: this.config.title,
				},
				credits: {
					text: "",
					href: "https:\/\/fhcomplete.org"
				},
				chart: {
					zoomType: "xy",
					type: "line",
					animation: true
				},
				xAxis: {
					categories: [],
					title: {
						text: this.config.xAxis.title || '',
					},
				},
				yAxis: {
					title: { text: "" },
				},
				series: this.filteredCharts.reduce((res, curr) => {
					res[curr.field] = {
						name: curr.label || curr.field,
						data: [],
					};
					switch (curr.type) {
					case "bar":
						res[curr.field].type = "column";
						break;
					case "dotted":
						res[curr.field].dashStyle = "dot";
						break;
					case "dashed":
						res[curr.field].dashStyle = "longdash";
						break;
					}
					if (curr.color)
						res[curr.field].color = '#' + curr.color.replace(/^#+/, '');
					if (curr.text)
						res[curr.field].dataLabels = { color: '#' + curr.text.replace(/^#+/, '') };
					return res;
				}, {}),
			};

			const chartNames = Object.keys(result.series);
			
			result = this.data.reduce((res, curr) => {
				const xValue = curr[this.config.xAxis.field];
				res.xAxis.categories.push(xValue);
				chartNames.forEach(
					chart => res.series[chart].data.push([xValue, Number(curr[chart])])
				);
				return res;
			}, result);

			result.series = Object.values(result.series);

			return result;
		},
		chartSetup() {
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
		},
		width(o, n) {
			if (o != n && this.$refs.chart)
				this.$refs.chart.reflow();
		},
		height(o, n) {
			if (o != n && this.$refs.chart)
				this.$refs.chart.reflow();
		},
	},
	methods: {
		async fetchData() {
			if (this.configMode)
				return;

			this.hasErrors = false;

			let vars = {};
			for (var key in this.config.vars) {
				vars[key] = await this.getValueForVar(this.config.vars[key]);

				if (this.config.vars[key].type == 'user') {
					if (!this.config.uservars || !this.config.uservars[key])
						delete vars[key];
					else
						vars[key] = this.config.uservars[key].value;
				} else if (this.config.vars[key].type.substr(0, 5) == 'calc:') {
					const requiresArray = this.config.vars[key].detail.multiple ? true : false;
					const isArray = Array.isArray(vars[key]);

					if (requiresArray && !isArray) {
						vars[key] = [ vars[key] ];
					} else if (!requiresArray && isArray) {
						const isSelect = this.config.vars[key].detail.type == 'select';

						let val = undefined;

						if (this.config.uservars)
							val = this.config.uservars[key]?.value;
						
						if (val && !vars[key].some(v1 => v1 == val)) {
							val = undefined;
							delete this.config.uservars[key].value;
						}
						if (isSelect && val && !this.config.vars[key].detail.options.some(option => option.value == val)) {
							val = undefined;
							delete this.config.uservars[key].value;
						}
						
						if (!val)
							val = vars[key].find(Boolean); // get first element
						
						let detail = { ...this.config.vars[key].detail, type: 'select' };

						if (this.config.vars[key].detail.type == 'select') {
							detail.options = this.config.vars[key].detail.options
								.filter(option => vars[key].some(v => v == option.value));
						} else {
							detail.options = vars[key].map(value => ({ label: value, value }));
						}
						
						const sharedData = this.sharedData || {};
						sharedData[key] = detail;
						this.$emit('update:sharedData', sharedData);
						this.$emit('setConfig', Vue.markRaw(ConfigGraph));

						vars[key] = val;
					}
				}
			}

			try {
				const result = await this.$api.call(
					ApiReport.get(this.config.statistik_kurzbz, vars),
					{ errorHandling: false }
				);
				this.data = result.data;
			} catch(error) {
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
		const { getValueForVar } = useCalculatedVars();
		return {
			getValueForVar
		};
	},
	mounted() {
		if (!this.adminMode) {
			if (Object.values(this.config.vars).some(v => v.type == 'user'))
				this.$emit('setConfig', Vue.markRaw(ConfigGraph));
			else if (this.config.charts.length > 1)
				this.$emit('setConfig', Vue.markRaw(ConfigGraph));
		}
	},
	template: /*html*/ `
	<div class="widgets-report-graph w-100 h-100 d-flex flex-column justify-content-center align-items-center">
		<report-error v-if="hasErrors" :errors="hasErrors" />
		<template v-else-if="chartSetup !== false">
			<fhc-chart ref="chart" :chart-options="chartSetup" />
		</template>
		<i v-else class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>
	`,
};
