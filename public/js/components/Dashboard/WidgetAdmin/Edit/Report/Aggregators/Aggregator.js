import FormInput from '../../../../../Form/Input.js';

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
	template: /*html*/ `
	<div class="widgets-report-config-aggregators-aggregator">
		<form-input
			type="text"
			v-model="modelValue.label"
			:label="$p.t('global/label')"
		/>
		<form-input
			type="select"
			v-model="modelValue.type"
			:label="$p.t('global/typ')"
		>
			<option value="sum">{{ $p.t('dashboard/widget_report_kpi_aggregator_type_sum') }}</option>
			<option value="count">{{ $p.t('dashboard/widget_report_kpi_aggregator_type_count') }}</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'sum'"
			type="text"
			v-model="modelValue.fields"
			:label="$p.t('dashboard/widget_report_kpi_aggregator_fields')"
			:placeholder="$p.t('dashboard/widget_report_kpi_aggregator_fields_placeholder')"
		/>
	</div>
	`,
};
