import FormInput from '../../../../../Form/Input.js';

export default {
	name: "WidgetsGenteratorReportAggregatorsAggregator",
	components: {
		FormInput,
	},
	inject: {
		defaultAggregatorIndex: 'defaultAggregatorIndex',
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
		index: {
			type: Number,
			required: true
		},
	},
	emits: [
		"update:modelValue",
	],
	template: /*html*/ `
	<div class="widgets-report-config-aggregators-aggregator">
		<form-input
			v-model="modelValue.label"
			type="text"
			:label="$p.t('global/label')"
			class="mb-3"
		/>
		<form-input
			v-model="modelValue.type"
			type="select"
			:label="$p.t('global/typ')"
			class="mb-3"
		>
			<option value="sum">{{ $p.t('dashboard/widget_report_kpi_aggregator_type_sum') }}</option>
			<option value="count">{{ $p.t('dashboard/widget_report_kpi_aggregator_type_count') }}</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'sum'"
			v-model="modelValue.fields"
			type="text"
			:label="$p.t('dashboard/widget_report_kpi_aggregator_fields')"
			:placeholder="$p.t('dashboard/widget_report_kpi_aggregator_fields_placeholder')"
			class="mb-3"
		/>
		<form-input
			v-model="defaultAggregatorIndex"
			type="radio"
			name="aggregatordefault"
			container-class="form-switch"
			:label="$p.t('ui/default')"
			:value="index"
		/>
	</div>
	`,
};
