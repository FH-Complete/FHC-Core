import FormInput from '../../../../Form/Input.js';

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
	template: /*html*/ `
	<div class="widgets-report-config-axis">
		<label class="form-label">{{ $p.t('dashboard/widget_report_graph_axis') }}</label>
		<form-input
			v-model="field"
			:label="$p.t('dashboard/widget_report_graph_field')"
		/>
		<form-input
			v-model="title"
			:label="$p.t('global/label')"
		/>
	</div>
	`,
};
