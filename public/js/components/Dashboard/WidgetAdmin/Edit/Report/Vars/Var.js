import FormInput from '../../../../../Form/Input.js';

import { useCalculatedVars } from '../../../../../../composables/DashboardWidget/Report/CalculatedVars.js';

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
		noType: {
			type: Boolean,
			default: false
		},
	},
	emits: [
		"update:modelValue",
	],
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
		saveValue() {
			if (this.fixedInputAttrs.multiple)
				if (this.modelValue.value === undefined)
					return [];
			
			return this.modelValue.value;
		},
		extendedType() {
			if (!this.modelValue?.type)
				return [];
			return this.modelValue.type.split(':');
		},
		calcTypeExtra: {
			get() {
				if (this.extendedType.length > 1)
					return this.extendedType[2];
				return undefined;
			},
			set(v) {
				this.setType([this.extendedType[0], this.extendedType[1], v].join(':'));
			},
		},
		calcComponent() {
			if (this.noType)
				return null;
			if (this.extendedType.length < 2)
				return null;
			if (this.extendedType[0] != 'calc')
				return null;
			if (!this.calcComponents[this.extendedType[1]])
				return null;

			return this.calcComponents[this.extendedType[1]];
		},
		options() {
			if (this.extendedType.length < 1)
				return [];
			if (this.extendedType[0] == 'calc')
				return this.calcOptions;
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
			if (type == 'user') {
				this.$emit('update:modelValue', { type, detail: this.detail });
			} else {
				this.$emit('update:modelValue', { type });
			}
		},
		setSubType(subtype) {
			let type = 'calc:' + subtype;
			this.$emit('update:modelValue', { type });
		},
		setValue(value) {
			this.modelValue.value = value;
			this.$emit('update:modelValue', this.modelValue);
		},
	},
	setup() {
		const { components, options } = useCalculatedVars();
		return {
			calcComponents: components,
			calcOptions: options,
		};
	},
	created() {
		if (!this.modelValue?.type) {
			this.setType('fix');
		}
	},
	template: /*html*/ `
	<div class="widgets-report-config-vars-var input-group">
		<span class="input-group-text">
			{{ detail.title }}
		</span>
		<form-input
			v-if="!noType"
			type="select"
			:modelValue="extendedType[0]"
			container-class="d-flex"
			input-group
			@update:modelValue="setType"
		>
			<option value="fix">Fixed</option>
			<option value="calc">Calculated</option>
			<option value="user">User Defined</option>
		</form-input>
		<form-input
			v-if="modelValue.type == 'fix' || noType"
			v-model="saveValue"
			v-bind="fixedInputAttrs"
			input-group
			@update:modelValue="setValue"
		>
			<option v-for="option in options" :key="option.value" :value="option.value">
				{{ option.label }}
			</option>
		</form-input>
		<form-input
			v-if="extendedType[0] == 'calc' && !noType"
			:modelValue="extendedType[1]"
			type="select"
			input-group
			@update:modelValue="setSubType"
		>
			<option v-for="option in options" :key="option.value" :value="option.value">
				{{ option.label }}
			</option>
		</form-input>
		<component v-if="calcComponent" :is="calcComponent" v-model="calcTypeExtra" />
	</div>
	`,
};
