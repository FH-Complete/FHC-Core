import ReportChart from './Charts/Chart.js';

let counter = 0;

export default {
	name: "WidgetsGenteratorReportCharts",
	components: {
		ReportChart,
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
	},
	emits: [
		"update:modelValue",
	],
	data() {
		return {
			myId: 0,
		};
	},
	methods: {
		addChart() {
			this.modelValue.push({});
		},
		removeChart(index) {
			this.$emit('update:modelValue', this.modelValue.toSpliced(index, 1));
		},
	},
	created() {
		this.myId = counter++;
	},
	template: /*html*/ `
	<div class="widgets-report-config-charts">
		<label class="form-label">{{ $p.t('dashboard/widget_report_graph_charts') }}</label>
		<div class="accordion mb-2">
			<div
				v-for="(opt, i) in modelValue"
				:key="i"
				class="accordion-item"
			>
				<h3 class="accordion-header">
					<button
						type="button"
						class="accordion-button collapsed"
						data-bs-toggle="collapse"
						:data-bs-target="'#chart_' + myId + '_' + i"
						aria-expanded="false"
						:aria-controls="'chart_' + myId + '_' + i"
					>
						<span v-if="opt.label">{{ opt.label }}</span>
						<span v-else-if="opt.field">{{ opt.field }}</span>
						<i v-else>{{ $p.t('ui/neu') }}</i>
					</button>
				</h3>
				<div :id="'chart_' + myId + '_' + i" class="accordion-collapse collapse">
					<report-chart v-model="modelValue[i]" class="accordion-body" @remove="removeChart(i)" />
				</div>
			</div>
		</div>
		<div>
			<button type="button" class="btn btn-primary" @click="addChart">
				<i class="fa fa-plus" />
				{{ $p.t('dashboard/widget_report_graph_chart') }}
			</button>
		</div>
	</div>
	`,
};
