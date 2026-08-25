import ReportAggregator from './Aggregators/Aggregator.js';

export default {
	name: "WidgetsGenteratorReportAggregators",
	components: {
		ReportAggregator,
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
			newItem: false,
		};
	},
	methods: {
		addAggregator() {
			this.modelValue.push({});
		}
	},
	created() {
	},
	template: /*html*/ `
	<div class="widgets-report-config-aggregators">
		<report-aggregator
			v-for="(agg, i) in modelValue"
			:key="i"
			v-model="agg"
		/>
		<div>
			<button type="button" class="btn btn-primary" @click="addAggregator">
				<i class="fa fa-plus" />
				Aggregator
			</button>
		</div>
	</div>
	`,
};
