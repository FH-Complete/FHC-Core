import FormInput from '../../../../../../Form/Input.js';

export default {
	name: "WidgetsGenteratorReportVarsVarCalcRight",
	components: {
		FormInput,
	},
	props: {
		modelValue: {
			type: String,
			default: ''
		},
	},
	emits: [
		"update:modelValue",
	],
	computed: {
		modelValueCmp: {
			get() {
				return this.modelValue;
			},
			set(v) {
				this.$emit('update:modelValue', v);
			},
		},
	},
	// TODO(chris): phrases: placeholder
	template: /*html*/ `
	<form-input
		v-model="modelValueCmp"
		class="widgets-report-config-vars-var-calc-right"
		type="text"
		input-group
		placeholder="Access Right (comma separated)"
	>
	</form-input>
	`,
};
