import FormInput from '../../../../../Form/Input.js';
import TitleInput from '../TitleInput.js';

import { useReportFields } from '../../../../../../composables/DashboardWidget/Report/ReportFields.js';

export default {
	name: "WidgetsGenteratorReportAggregatorsAggregator",
	components: {
		FormInput,
		TitleInput,
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
		details: {
			type: Array
		},
	},
	emits: [
		"update:modelValue",
		"remove",
	],
	methods: {
		remove() {
			this.$fhcAlert.confirmDelete()
				.then(result => {
					if (result)
						this.$emit('remove', this.index);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	setup() {
		const statistikKurzbz = Vue.inject('statistikKurzbz');

		const { fields } = useReportFields(statistikKurzbz);

		return {
			fields,
		};
	},
	template: /*html*/ `
	<div class="widgets-report-config-aggregators-aggregator">
		<title-input
			v-model="modelValue.label"
			id="aggregator-label"
			:label="$p.t('global/label')"
			:details="details"
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
		<template v-if="modelValue.type == 'sum'">
			<form-input
				v-if="!fields.length"
				v-model="modelValue.fields"
				type="text"
				:label="$p.t('dashboard/widget_report_kpi_aggregator_fields')"
				:placeholder="$p.t('dashboard/widget_report_kpi_aggregator_fields_placeholder')"
				class="mb-3"
			/>
			<form-input
				v-else
				v-model="modelValue.fields"
				type="select"
				:label="$p.t('dashboard/widget_report_kpi_aggregator_fields')"
				class="mb-3"
				multiple
			>
				<option v-for="field in fields" :key="field.name" :value="field.name">
					{{ field.name }} ({{ field.type }})
				</option>
			</form-input>
		</template>
		<div class="d-flex justify-content-between align-items-baseline">
			<form-input
				v-model="defaultAggregatorIndex"
				type="radio"
				name="aggregatordefault"
				container-class="form-switch"
				:label="$p.t('ui/default')"
				:value="index"
			/>
			<button type="button" class="btn btn-danger" @click="remove">
				<i class="fa-solid fa-trash"></i>
				{{ $p.t('ui/loeschen') }}
			</button>
		</div>
	</div>
	`,
};
