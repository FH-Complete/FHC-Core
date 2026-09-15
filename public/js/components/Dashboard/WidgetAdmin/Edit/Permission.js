import FormInput from "../../../Form/Input.js";

export default {
	name: 'WidgetsAdminEditPermission',
	components: {
		FormInput,
	},
	props: {
		modelValue: String,
		disabled: Boolean,
	},
	emits: [
		"update:modelValue"
	],
	computed: {
		value: {
			get() {
				return this.modelValue;
			},
			set(modelValue) {
				this.$emit('update:modelValue', modelValue);
			},
		},
	},
	template: /* html */`
	<div class="widgets-admin-edit-permission">
		<form-input
			v-model="value"
			type="text"
			:label="$p.t('global/permission')"
			:disabled="disabled"
		/>
	</div>
	`
}
