import FormInput from '../../../../../Form/Input.js';

import { capitalize } from '../../../../../../helpers/StringHelpers.js';

import ApiReport from '../../../../../../api/factory/report.js';

export default {
	name: "WidgetsGenteratorReportVarsVar",
	components: {
		FormInput,
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
		detail: {
			type: Object,
			required: true
		},
	},
	emits: [
		"update:modelValue",
	],
	data() {
		return {
		};
	},
	computed: {
		fixedInputAttrs() {
			let attrs = {
				type: this.detail.type,
			};

			if (attrs.type == 'text')
				attrs.type = 'input';

			if (this.detail.placeholder)
				attrs.placeholder = this.detail.placeholder;

			if (this.detail.multiple)
				attrs.multiple = this.detail.multiple;

			return attrs;
		},
		options() {
			if (this.modelValue.type == 'calc') {
				// TODO(chris): IMPLEMENT
				return [{ value: '', label: 'NOT YET IMPLEMENTED!' }];
			}
			if (this.detail.type == 'select')
				return this.detail.options;
			return [];
		},
	},
	methods: {
		getComponent(string) {
			return 'Report' + capitalize(string);
		},
		setType(type) {
			this.$emit('update:modelValue', { type });
		},
		setValue(value) {
			this.$emit('update:modelValue', { ...this.modelValue, value });
		},
	},
	created() {
		if (!this.modelValue.type) {
			this.setType('fix');
		}
	},
	template: /*html*/ `
	<div class="widgets-report-config-vars-var input-group">
		<span class="input-group-text">
			{{ detail.title }}
		</span>
		<form-input
			type="select"
			:modelValue="modelValue.type"
			container-class="d-flex"
			input-group
			@update:modelValue="setType"
		>
			<option value="fix">Fixed</option>
			<option value="calc">Calculated</option>
			<option value="user">User Defined</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'fix'"
			:modelValue="modelValue.value"
			v-bind="fixedInputAttrs"
			input-group
			@update:modelValue="setValue"
		>
			<option v-for="option in options" :key="option.value" :value="option.value">
				{{ option.label }}
			</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'calc'"
			:modelValue="modelValue.value"
			input-group
			@update:modelValue="setValue"
		>
			<option v-for="option in options" :key="option.value" :value="option.value">
				{{ option.label }}
			</option>
		</form-input>
	</div>
	`,
};
