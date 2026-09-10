import FormInput from '../../../../../../Form/Input.js';

export default {
	name: "WidgetsGenteratorReportVarsVarCalcDate",
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
		class="widgets-report-config-vars-var-calc-date"
		type="text"
		input-group
		placeholder="Duration (add '-' at the beginning to substract) from current date"
	>
	</form-input>
	`,
};
