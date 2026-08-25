import FormInput from '../../../../../Form/Input.js';

// TODO(chris): phrases

export default {
	name: "WidgetsGenteratorReportAggregatorsAggregator",
	components: {
		FormInput,
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
	methods: {
	},
	created() {
	},
	template: /*html*/ `
	<div class="widgets-report-config-aggregators-aggregator">
		<form-input
			type="text"
			v-model="modelValue.label"
			label="label"
		/>
		<form-input
			type="select"
			v-model="modelValue.type"
			label="type"
		>
			<option value="sum">Sum</option>
			<option value="count">Count</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'sum'"
			type="text"
			v-model="modelValue.fields"
			label="fields"
			placeholder="fields (comma separated)"
		/>
	</div>
	`,
};
