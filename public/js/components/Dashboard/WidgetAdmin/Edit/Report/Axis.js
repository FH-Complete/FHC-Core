import FormInput from '../../../../Form/Input.js';

import { useReportFields } from '../../../../../composables/DashboardWidget/Report/ReportFields.js';

export default {
	name: "WidgetsGenteratorReportAxis",
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
	computed: {
		field: {
			get() {
				return this.modelValue?.field || '';
			},
			set(field) {
				this.$emit('update:modelValue', { ...this.modelValue, field });
			}
		},
		title: {
			get() {
				return this.modelValue?.title || '';
			},
			set(title) {
				this.$emit('update:modelValue', { ...this.modelValue, title });
			}
		},
	},
	methods: {
		addChart() {
			this.modelValue.push({});
		},
		removeChart(index) {
			this.$emit('update:modelValue', this.modelValue.toSpliced(index, 1));
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
	<div class="widgets-report-config-axis">
		<label class="form-label">{{ $p.t('dashboard/widget_report_graph_axis') }}</label>
		<form-input
			v-if="!fields.length"
			v-model="field"
			:label="$p.t('dashboard/widget_report_graph_field')"
		/>
		<form-input
			v-else
			v-model="field"
			type="select"
			:label="$p.t('dashboard/widget_report_graph_field')"
		>
			<option v-for="field in fields" :key="field.name" :value="field.name">
				{{ field.name }} ({{ field.type }})
			</option>
		</form-input>
		<form-input
			v-model="title"
			:label="$p.t('global/label')"
		/>
	</div>
	`,
};
