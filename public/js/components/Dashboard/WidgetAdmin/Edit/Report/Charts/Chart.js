import FormInput from '../../../../../Form/Input.js';

export default {
	name: "WidgetsGenteratorReportChartsChart",
	components: {
		FormInput,
		PvDropdown: primevue.dropdown,
		PvColorpicker: primevue.colorpicker, // NOTE(chris): colorpicker must be loaded in view
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
	},
	emits: [
		"update:modelValue",
		"remove",
	],
	data() {
		return {
			typeOptions: [
				{
					value: 'bar',
					title: this.$p.t('dashboard/widget_report_graph_chart_type_bar'),
					optionHtml: /* html */`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 10"><rect x="0" y="5" width="8" height="5" fill="black"/><rect x="11" y="2" width="8" height="8" fill="black"/><rect x="22" y="6" width="8" height="4" fill="black"/></svg>`,
				},
				{
					value: 'line',
					title: this.$p.t('dashboard/widget_report_graph_chart_type_line'),
					optionHtml: /* html */`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 10"><path d="M0,5h30" stroke="black"/></svg>`,
				},
				{
					value: 'dashed',
					title: this.$p.t('dashboard/widget_report_graph_chart_type_dashed'),
					optionHtml: /* html */`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 10"><path d="M0,5h30" stroke="black" stroke-dasharray="4 4"/></svg>`,
				},
				{
					value: 'dotted',
					title: this.$p.t('dashboard/widget_report_graph_chart_type_dotted'),
					optionHtml: /* html */`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 10"><path d="M0,5h30" stroke="black" stroke-dasharray="0 4" stroke-linecap="round"/></svg>`,
				},
			],
		};
	},
	computed: {
		field: {
			get() {
				return this.modelValue.field || '';
			},
			set(field) {
				this.$emit('update:modelValue', { ...this.modelValue, field });
			}
		},
		label: {
			get() {
				return this.modelValue.label || '';
			},
			set(label) {
				this.$emit('update:modelValue', { ...this.modelValue, label });
			}
		},
		color: {
			get() {
				return this.modelValue.color || '';
			},
			set(color) {
				this.$emit('update:modelValue', { ...this.modelValue, color });
			}
		},
		text: {
			get() {
				return this.modelValue.text || '';
			},
			set(text) {
				this.$emit('update:modelValue', { ...this.modelValue, text });
			}
		},
		type: {
			get() {
				if (!this.modelValue.type)
					return undefined;
				return this.typeOptions.find(opt => opt.value == this.modelValue.type);
			},
			set({ value: type }) {
				this.$emit('update:modelValue', { ...this.modelValue, type });
			}
		},
	},
	methods: {
		remove() {
			this.$fhcAlert.confirmDelete()
				.then(result => {
					if (result)
						this.$emit('remove');
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	template: /*html*/ `
	<div class="widgets-report-config-charts-chart">
		<form-input
			v-model="field"
			:label="$p.t('dashboard/widget_report_graph_field')"
			class="mb-3"
		/>
		<form-input
			v-model="label"
			:label="$p.t('global/label')"
			class="mb-3"
		/>
		<label class="form-label">{{ $p.t('dashboard/widget_report_graph_chart_layout') }}</label>
		<div class="input-group mb-3">
			<span class="input-group-text">{{ $p.t('global/typ') }}</span>
			<pv-dropdown
				v-model="type"
				:options="typeOptions"
			>
				<template #value="{ value, placeholder }">
					<div
						v-html="value ? value.optionHtml : placeholder"
						style="width:3rem"
					></div>
				</template>
				<template #option="{ option }">
					<div v-html="option.optionHtml"></div>
				</template>
			</pv-dropdown>
			<span
				class="input-group-text"
				:title="$p.t('dashboard/widget_report_graph_chart_bg_color')"
			>
				<i class="fa-solid fa-fill"></i>
			</span>
			<pv-colorpicker v-model="color" class="input-group-text"/>
			<form-input v-model="color" input-group></form-input>
			<span
				class="input-group-text"
				:title="$p.t('dashboard/widget_report_graph_chart_text_color')"
			>
				<i class="fa-solid fa-font"></i>
			</span>
			<pv-colorpicker v-model="text" class="input-group-text"/>
			<form-input v-model="text" input-group></form-input>
		</div>
		<div class="d-flex justify-content-end align-items-baseline">
			<button type="button" class="btn btn-danger" @click="remove">
				<i class="fa-solid fa-trash"></i>
				{{ $p.t('ui/loeschen') }}
			</button>
		</div>
	</div>
	`,
};
